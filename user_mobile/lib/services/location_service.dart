import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';
import 'dart:math' show cos, sqrt, asin;

class LocationService {
  // Koordinat kantor SIMTEK
  static const double officeLatitude = -7.1183948;
  static const double officeLongitude = 108.2657503;
  static const double allowedRadiusMeters = 1500.0; // 1.5 km

  /// Check if location permission is granted
  Future<bool> checkLocationPermission() async {
    final permission = await Permission.location.status;
    return permission.isGranted;
  }

  /// Request location permission
  Future<bool> requestLocationPermission() async {
    final status = await Permission.location.request();
    return status.isGranted;
  }

  /// Check if GPS is enabled
  Future<bool> isLocationServiceEnabled() async {
    return await Geolocator.isLocationServiceEnabled();
  }

  /// Open location settings
  Future<bool> openLocationSettings() async {
    return await Geolocator.openLocationSettings();
  }

  /// Get current position
  Future<Position?> getCurrentPosition() async {
    try {
      // Check if location service is enabled
      bool serviceEnabled = await isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Location service is disabled. Please enable GPS.');
      }

      // Check permission
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          throw Exception('Location permission denied');
        }
      }

      if (permission == LocationPermission.deniedForever) {
        throw Exception(
          'Location permissions are permanently denied. Please enable in settings.',
        );
      }

      // Get current position with high accuracy
      Position position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 10,
        ),
      );

      return position;
    } catch (e) {
      // Debug: Error getting position
      // print('Error getting position: $e');
      return null;
    }
  }

  /// Calculate distance between two points in meters using Haversine formula
  double calculateDistance(double lat1, double lon1, double lat2, double lon2) {
    const p = 0.017453292519943295; // Math.PI / 180
    final a =
        0.5 -
        cos((lat2 - lat1) * p) / 2 +
        cos(lat1 * p) * cos(lat2 * p) * (1 - cos((lon2 - lon1) * p)) / 2;
    return 12742000 * asin(sqrt(a)); // 2 * R * asin, R = 6371 km = 6371000 m
  }

  /// Check if current position is within office radius
  Future<Map<String, dynamic>> validateLocation() async {
    try {
      Position? position = await getCurrentPosition();

      if (position == null) {
        return {
          'success': false,
          'message': 'Tidak dapat mendapatkan lokasi Anda',
          'latitude': null,
          'longitude': null,
          'distance': null,
        };
      }

      double distance = calculateDistance(
        position.latitude,
        position.longitude,
        officeLatitude,
        officeLongitude,
      );

      bool isWithinRange = distance <= allowedRadiusMeters;

      return {
        'success': isWithinRange,
        'message': isWithinRange
            ? (distance >= 1000
                  ? 'Lokasi Anda valid (${(distance / 1000).toStringAsFixed(2)} km dari kantor)'
                  : 'Lokasi Anda valid (${distance.toStringAsFixed(0)} meter dari kantor)')
            : (distance >= 1000
                  ? 'Anda berada ${(distance / 1000).toStringAsFixed(2)} km dari kantor. Harap berada dalam radius ${(allowedRadiusMeters / 1000).toStringAsFixed(1)} km!'
                  : 'Anda berada ${distance.toStringAsFixed(0)} meter dari kantor. Harap berada dalam radius ${allowedRadiusMeters.toStringAsFixed(0)} meter!'),
        'latitude': position.latitude,
        'longitude': position.longitude,
        'distance': distance,
        'isWithinRange': isWithinRange,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: ${e.toString()}',
        'latitude': null,
        'longitude': null,
        'distance': null,
      };
    }
  }

  /// Get office coordinates
  Map<String, double> getOfficeCoordinates() {
    return {'latitude': officeLatitude, 'longitude': officeLongitude};
  }
}
