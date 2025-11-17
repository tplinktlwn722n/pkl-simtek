class Task {
  final int id;
  final String title;
  final String description;
  final int adminId;
  final int? assignedTo;
  final String status; // pending, accepted, completed, rejected
  final DateTime createdAt;
  final DateTime? acceptedAt;
  final DateTime? completedAt;
  final String? adminName;
  final String? assignedUserName;

  Task({
    required this.id,
    required this.title,
    required this.description,
    required this.adminId,
    this.assignedTo,
    required this.status,
    required this.createdAt,
    this.acceptedAt,
    this.completedAt,
    this.adminName,
    this.assignedUserName,
  });

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      adminId: json['admin_id'],
      assignedTo: json['assigned_to'],
      status: json['status'],
      createdAt: DateTime.parse(json['created_at']),
      acceptedAt: json['accepted_at'] != null
          ? DateTime.parse(json['accepted_at'])
          : null,
      completedAt: json['completed_at'] != null
          ? DateTime.parse(json['completed_at'])
          : null,
      adminName: json['admin']?['name'],
      assignedUserName: json['assigned_user']?['name'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'admin_id': adminId,
      'assigned_to': assignedTo,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'accepted_at': acceptedAt?.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
    };
  }
}
