import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/scheduler.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:convert';
import 'dart:io';
import 'package:provider/provider.dart';
import '../providers/attendance_provider.dart';
import '../services/location_service.dart';

/// Camera Attendance Screen
///
/// Screen untuk mengambil foto attendance dengan keamanan:
/// - GPS validation: Cek lokasi dalam radius 100m dari kantor
/// - HANYA menggunakan kamera langsung (ImageSource.camera)
/// - TIDAK menggunakan galeri untuk menghindari manipulasi foto lama
/// - Mendukung platform Web dan Mobile
/// - Foto langsung diambil pada waktu absensi
class CameraAttendanceScreen extends StatefulWidget {
  final bool isCheckOut;

  const CameraAttendanceScreen({super.key, this.isCheckOut = false});

  @override
  State<CameraAttendanceScreen> createState() => _CameraAttendanceScreenState();
}

class _CameraAttendanceScreenState extends State<CameraAttendanceScreen> {
  final ImagePicker _picker = ImagePicker();
  final LocationService _locationService = LocationService();
  XFile? _imageFile;
  bool _isProcessing = false;
  bool _hasNavigatedBack = false;
  String? _imageUrl;
  double? _latitude;
  double? _longitude;

  @override
  void initState() {
    super.initState();
    _validateGPS();
  }

  Future<void> _validateGPS() async {
    // Cek GPS enabled
    bool isEnabled = await _locationService.isLocationServiceEnabled();
    if (!isEnabled) {
      if (!mounted) return;
      _showGPSDialog(
        'GPS Tidak Aktif',
        'Silakan aktifkan GPS untuk melanjutkan absensi.',
      );
      return;
    }

    // Cek permission
    bool hasPermission = await _locationService.checkLocationPermission();
    if (!hasPermission) {
      hasPermission = await _locationService.requestLocationPermission();
      if (!hasPermission) {
        if (!mounted) return;
        Navigator.pop(context);
        return;
      }
    }

    // Validasi lokasi
    final result = await _locationService.validateLocation();
    if (!mounted) return;

    if (result['success']) {
      setState(() {
        _latitude = result['latitude'];
        _longitude = result['longitude'];
      });
    } else {
      _showLocationErrorDialog(result['message'], result['distance']);
    }
  }

  void _showGPSDialog(String title, String message) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.location_off, color: Colors.red),
            const SizedBox(width: 8),
            Text(title),
          ],
        ),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context); // Only close dialog
            },
            child: const Text('Tutup'),
          ),
          ElevatedButton.icon(
            onPressed: () async {
              Navigator.pop(context);
              await _locationService.openLocationSettings();
              // Cek ulang setelah 2 detik
              await Future.delayed(const Duration(seconds: 2));
              _validateGPS();
            },
            icon: const Icon(Icons.settings),
            label: const Text('Aktifkan GPS'),
          ),
        ],
      ),
    );
  }

  void _showLocationErrorDialog(String message, double? distance) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.error_outline, color: Colors.red),
            SizedBox(width: 8),
            Text('Lokasi Tidak Valid'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(message),
            if (distance != null) ...[
              const SizedBox(height: 8),
              Text(
                distance >= 1000
                    ? 'Jarak Anda: ${(distance / 1000).toStringAsFixed(2)} km dari kantor'
                    : 'Jarak Anda: ${distance.toStringAsFixed(0)} meter dari kantor',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.red,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Radius maksimal: 1.5 km',
                style: TextStyle(fontSize: 12),
              ),
            ],
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context); // Close dialog only
              _validateGPS(); // Retry validation
            },
            child: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }

  Future<void> _takePicture() async {
    try {
      // Hanya menggunakan kamera, TIDAK BISA dari galeri untuk menghindari manipulasi
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.camera, // WAJIB dari kamera
        preferredCameraDevice: CameraDevice.front,
        imageQuality: 85,
        maxWidth: 1024,
        maxHeight: 1024,
      );

      if (photo != null) {
        String? imageUrl;
        if (kIsWeb) {
          // Untuk web, buat URL dari bytes
          imageUrl = await photo.readAsBytes().then((bytes) {
            return 'data:image/jpeg;base64,${base64Encode(bytes)}';
          });
        }

        setState(() {
          _imageFile = photo;
          _imageUrl = imageUrl;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error mengambil foto: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<String> _convertImageToBase64(XFile imageFile) async {
    final bytes = await imageFile.readAsBytes();
    return 'data:image/jpeg;base64,${base64Encode(bytes)}';
  }

  Future<void> _submitAttendance() async {
    if (_imageFile == null) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan ambil foto terlebih dahulu'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (_latitude == null || _longitude == null) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Data GPS tidak tersedia, silakan coba lagi'),
          backgroundColor: Colors.red,
        ),
      );
      _validateGPS();
      return;
    }

    setState(() {
      _isProcessing = true;
    });

    try {
      final base64Image = await _convertImageToBase64(_imageFile!);
      if (!mounted) return;

      final attendanceProvider = Provider.of<AttendanceProvider>(
        context,
        listen: false,
      );

      bool success = await attendanceProvider.checkIn(
        location: 'Mobile App',
        photoBase64: base64Image,
        latitude: _latitude!,
        longitude: _longitude!,
      );

      print('DEBUG: Check-in success = $success'); // Debug

      if (!mounted) return;

      if (success) {
        print('DEBUG: Calling Navigator.pop(true)'); // Debug
        // Set flag to prevent double navigation
        _hasNavigatedBack = true;

        // Auto-close immediately and return to attendance screen
        // Use SchedulerBinding to ensure navigation happens after build
        SchedulerBinding.instance.addPostFrameCallback((_) {
          if (mounted && !Navigator.of(context).userGestureInProgress) {
            Navigator.of(context).pop(true);
          }
        });
        return; // Exit immediately after scheduling pop
      } else {
        print(
          'DEBUG: Check-in failed: ${attendanceProvider.errorMessage}',
        ); // Debug
        setState(() {
          _isProcessing = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(attendanceProvider.errorMessage ?? 'Gagal check in'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Terjadi kesalahan: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !_isProcessing && !_hasNavigatedBack,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop && !_hasNavigatedBack) {
          // User manually closed
          print('DEBUG: User manually closed camera');
        }
      },
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Absen Masuk - Ambil Foto'),
          automaticallyImplyLeading: !_isProcessing,
          elevation: 0,
          flexibleSpace: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Colors.blue.shade600, Colors.blue.shade400],
              ),
            ),
          ),
        ),
        body: Column(
          children: [
            Expanded(
              child: Container(
                width: double.infinity,
                color: Colors.grey.shade900,
                child: _imageFile == null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.camera_alt,
                              size: 100,
                              color: Colors.grey.shade700,
                            ),
                            const SizedBox(height: 20),
                            Text(
                              'Ambil foto untuk ${widget.isCheckOut ? 'check out' : 'check in'}',
                              style: TextStyle(
                                color: Colors.grey.shade500,
                                fontSize: 16,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Pastikan wajah terlihat jelas',
                              style: TextStyle(
                                color: Colors.grey.shade600,
                                fontSize: 14,
                              ),
                            ),
                            const SizedBox(height: 12),
                            // GPS Status
                            if (_latitude != null && _longitude != null)
                              Container(
                                margin: const EdgeInsets.only(bottom: 8),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.green.shade900.withValues(
                                    alpha: 0.3,
                                  ),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: Colors.green.shade700.withValues(
                                      alpha: 0.5,
                                    ),
                                    width: 1,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.check_circle_outline,
                                      color: Colors.green.shade300,
                                      size: 16,
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Lokasi valid - Dalam radius 1.5 km',
                                      style: TextStyle(
                                        color: Colors.green.shade200,
                                        fontSize: 11,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 20,
                                vertical: 8,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.orange.shade900.withValues(
                                  alpha: 0.3,
                                ),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: Colors.orange.shade700.withValues(
                                    alpha: 0.5,
                                  ),
                                  width: 1,
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.info_outline,
                                    color: Colors.orange.shade300,
                                    size: 18,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Foto harus diambil langsung dari kamera',
                                    style: TextStyle(
                                      color: Colors.orange.shade200,
                                      fontSize: 12,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      )
                    : Stack(
                        fit: StackFit.expand,
                        children: [
                          kIsWeb
                              ? Image.network(_imageUrl!, fit: BoxFit.contain)
                              : Image.file(
                                  File(_imageFile!.path),
                                  fit: BoxFit.contain,
                                ),
                        ],
                      ),
              ),
            ),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 10,
                    offset: const Offset(0, -5),
                  ),
                ],
              ),
              child: SafeArea(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (_imageFile == null)
                      ElevatedButton.icon(
                        onPressed: _isProcessing ? null : _takePicture,
                        icon: const Icon(Icons.camera_alt, size: 28),
                        label: const Text(
                          'Ambil Foto',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 40,
                            vertical: 16,
                          ),
                          minimumSize: const Size(double.infinity, 60),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      )
                    else
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: _isProcessing ? null : _takePicture,
                              icon: const Icon(Icons.refresh),
                              label: const Text('Ambil Ulang'),
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  vertical: 16,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: _isProcessing
                                  ? null
                                  : _submitAttendance,
                              icon: _isProcessing
                                  ? const SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        color: Colors.white,
                                      ),
                                    )
                                  : const Icon(Icons.check),
                              label: Text(
                                _isProcessing ? 'Memproses...' : 'Kirim',
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.green,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(
                                  vertical: 16,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
