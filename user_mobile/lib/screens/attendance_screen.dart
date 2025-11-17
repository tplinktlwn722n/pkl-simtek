import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import '../providers/attendance_provider.dart';
import 'camera_attendance_screen.dart';
import '../services/location_service.dart';

class AttendanceScreen extends StatefulWidget {
  final bool showHistory;

  const AttendanceScreen({super.key, this.showHistory = false});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    initializeDateFormatting('id_ID', null);
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: widget.showHistory ? 1 : 0,
    );
    _tabController.addListener(_onTabChanged);

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = Provider.of<AttendanceProvider>(context, listen: false);
      provider.loadTodayStatus();
      if (widget.showHistory || _tabController.index == 1) {
        provider.loadHistory();
      }
    });
  }

  void _onTabChanged() {
    if (_tabController.index == 1) {
      final provider = Provider.of<AttendanceProvider>(context, listen: false);
      provider.loadHistory();
    }
  }

  @override
  void dispose() {
    _tabController.removeListener(_onTabChanged);
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _handleCheckIn() async {
    final locationService = LocationService();

    // 1. Check if GPS is enabled
    bool isGPSEnabled = await locationService.isLocationServiceEnabled();
    if (!isGPSEnabled) {
      if (!mounted) return;
      _showGPSDisabledDialog();
      return;
    }

    // 2. Check location permission
    bool hasPermission = await locationService.checkLocationPermission();
    if (!hasPermission) {
      hasPermission = await locationService.requestLocationPermission();
      if (!hasPermission) {
        if (!mounted) return;
        _showPermissionDeniedDialog();
        return;
      }
    }

    // 3. Validate location (distance check)
    final result = await locationService.validateLocation();
    if (!mounted) return;

    if (!result['success']) {
      _showLocationErrorDialog(result['message'], result['distance']);
      return;
    }

    // 4. All checks passed, open camera
    final cameraResult = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const CameraAttendanceScreen(isCheckOut: false),
      ),
    );

    print('DEBUG: Camera result = $cameraResult'); // Debug

    if (cameraResult == true && mounted) {
      print('DEBUG: Camera result is true, reloading status'); // Debug
      // Reload today's status first
      final provider = Provider.of<AttendanceProvider>(context, listen: false);
      await provider.loadTodayStatus();

      // Force rebuild UI
      if (mounted) {
        setState(() {});
      }

      // Show success message
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white),
                const SizedBox(width: 12),
                const Expanded(
                  child: Text(
                    'Absen masuk berhasil! Anda sudah absen hari ini.',
                    style: TextStyle(fontSize: 15),
                  ),
                ),
              ],
            ),
            backgroundColor: Colors.green,
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 4),
            action: SnackBarAction(
              label: 'OK',
              textColor: Colors.white,
              onPressed: () {},
            ),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Absensi'),
        elevation: 0,
        backgroundColor: Colors.transparent,
        flexibleSpace: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Colors.blue.shade600, Colors.blue.shade400],
            ),
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Hari Ini', icon: Icon(Icons.today)),
            Tab(text: 'Riwayat', icon: Icon(Icons.history)),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [_buildCheckInOutTab(), _buildHistoryTab()],
      ),
    );
  }

  Widget _buildCheckInOutTab() {
    return Consumer<AttendanceProvider>(
      builder: (context, provider, child) {
        return RefreshIndicator(
          onRefresh: () => provider.loadTodayStatus(),
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
                // Current Date Card
                Card(
                  elevation: 4,
                  child: Padding(
                    padding: const EdgeInsets.all(20.0),
                    child: Column(
                      children: [
                        Icon(
                          Icons.calendar_today,
                          size: 48,
                          color: Theme.of(context).primaryColor,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          DateFormat('EEEE, MMMM d, y').format(DateTime.now()),
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          DateFormat('HH:mm:ss').format(DateTime.now()),
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Tombol Masuk - Always show for testing
                if (provider.isLoading)
                  const Center(child: CircularProgressIndicator())
                else
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _handleCheckIn,
                      icon: const Icon(Icons.login, size: 32),
                      label: const Text(
                        'Absen Masuk',
                        style: TextStyle(fontSize: 20),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.all(24),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildHistoryTab() {
    return Consumer<AttendanceProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.history.isEmpty) {
          return const Center(child: CircularProgressIndicator());
        }

        if (provider.history.isEmpty) {
          return const Center(child: Text('No attendance history found'));
        }

        return RefreshIndicator(
          onRefresh: () => provider.loadHistory(),
          child: ListView.builder(
            padding: const EdgeInsets.all(16.0),
            itemCount: provider.history.length,
            itemBuilder: (context, index) {
              final attendance = provider.history[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: _getStatusColor(attendance.status),
                    child: const Icon(Icons.login, color: Colors.white),
                  ),
                  title: Text(
                    DateFormat(
                      'EEEE, d MMMM y',
                      'id_ID',
                    ).format(attendance.checkInTime),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(
                        'Masuk: ${DateFormat('HH:mm:ss').format(attendance.checkInTime)}',
                      ),
                    ],
                  ),
                  trailing: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor(
                        attendance.status,
                      ).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      attendance.status.toUpperCase(),
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: _getStatusColor(attendance.status),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'present':
        return Colors.green;
      case 'late':
        return Colors.orange;
      case 'absent':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  void _showGPSDisabledDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.location_off, color: Colors.red, size: 32),
            SizedBox(width: 12),
            Text('GPS Tidak Aktif'),
          ],
        ),
        content: const Text(
          'Silakan aktifkan GPS untuk melanjutkan absensi.\n\nTekan tombol di bawah untuk membuka pengaturan GPS.',
          style: TextStyle(fontSize: 15),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Tutup'),
          ),
          ElevatedButton.icon(
            onPressed: () async {
              Navigator.pop(context);
              final locationService = LocationService();
              await locationService.openLocationSettings();
              // Wait 2 seconds then retry
              await Future.delayed(const Duration(seconds: 2));
              _handleCheckIn();
            },
            icon: const Icon(Icons.settings),
            label: const Text('Aktifkan GPS'),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  void _showPermissionDeniedDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning, color: Colors.orange, size: 32),
            SizedBox(width: 12),
            Text('Izin Lokasi Ditolak'),
          ],
        ),
        content: const Text(
          'Aplikasi membutuhkan izin lokasi untuk fitur absensi.\n\nSilakan berikan izin di pengaturan aplikasi.',
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showLocationErrorDialog(String message, double? distance) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.error_outline, color: Colors.red, size: 32),
            SizedBox(width: 12),
            Text('Lokasi Tidak Valid'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(message),
            if (distance != null) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.location_on,
                          color: Colors.red.shade700,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            distance >= 1000
                                ? 'Jarak: ${(distance / 1000).toStringAsFixed(2)} km'
                                : 'Jarak: ${distance.toStringAsFixed(0)} meter',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.red.shade900,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Radius maksimal: 1.5 km',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _handleCheckIn(); // Retry
            },
            child: const Text('Coba Lagi'),
          ),
        ],
      ),
    );
  }
}
