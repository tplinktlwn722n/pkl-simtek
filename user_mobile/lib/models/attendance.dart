import 'user.dart';

class Attendance {
  final int id;
  final int userId;
  final DateTime checkInTime;
  final String status;
  final String? notes;
  final String? location;
  final double? latitude;
  final double? longitude;
  final User? user;

  Attendance({
    required this.id,
    required this.userId,
    required this.checkInTime,
    required this.status,
    this.notes,
    this.location,
    this.latitude,
    this.longitude,
    this.user,
  });

  factory Attendance.fromJson(Map<String, dynamic> json) {
    return Attendance(
      id: json['id'],
      userId: json['user_id'],
      checkInTime: DateTime.parse(json['check_in_time']),
      status: json['status'],
      notes: json['notes'],
      location: json['location'],
      latitude: json['latitude'] != null
          ? double.tryParse(json['latitude'].toString())
          : null,
      longitude: json['longitude'] != null
          ? double.tryParse(json['longitude'].toString())
          : null,
      user: json['user'] != null ? User.fromJson(json['user']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'check_in_time': checkInTime.toIso8601String(),
      'status': status,
      'notes': notes,
      'location': location,
      'latitude': latitude,
      'longitude': longitude,
      'user': user?.toJson(),
    };
  }
}
