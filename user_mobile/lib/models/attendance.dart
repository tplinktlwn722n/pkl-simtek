import 'user.dart';

class Attendance {
  final int id;
  final int userId;
  final DateTime checkInTime;
  final DateTime? checkOutTime;
  final String status;
  final String? notes;
  final String? location;
  final User? user;

  Attendance({
    required this.id,
    required this.userId,
    required this.checkInTime,
    this.checkOutTime,
    required this.status,
    this.notes,
    this.location,
    this.user,
  });

  factory Attendance.fromJson(Map<String, dynamic> json) {
    return Attendance(
      id: json['id'],
      userId: json['user_id'],
      checkInTime: DateTime.parse(json['check_in_time']),
      checkOutTime: json['check_out_time'] != null
          ? DateTime.parse(json['check_out_time'])
          : null,
      status: json['status'],
      notes: json['notes'],
      location: json['location'],
      user: json['user'] != null ? User.fromJson(json['user']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'check_in_time': checkInTime.toIso8601String(),
      'check_out_time': checkOutTime?.toIso8601String(),
      'status': status,
      'notes': notes,
      'location': location,
      'user': user?.toJson(),
    };
  }
}
