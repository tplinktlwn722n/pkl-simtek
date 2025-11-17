import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/attendance.dart';
import 'api_service.dart';

class AttendanceService {
  final ApiService _apiService = ApiService();
  static const String baseUrl = 'http://127.0.0.1:8000/api';

  Future<Map<String, dynamic>> checkIn({
    String? location,
    String? notes,
  }) async {
    return await _apiService.post(
      '/attendances/check-in',
      requiresAuth: true,
      body: {
        if (location != null) 'location': location,
        if (notes != null) 'notes': notes,
      },
    );
  }

  Future<Map<String, dynamic>> checkOut() async {
    return await _apiService.post('/attendances/check-out', requiresAuth: true);
  }

  Future<Attendance?> getTodayStatus() async {
    final response = await _apiService.get(
      '/attendances/today',
      requiresAuth: true,
    );

    if (response['success'] == true &&
        response['data'] != null &&
        response['data']['attendance'] != null) {
      return Attendance.fromJson(response['data']['attendance']);
    }

    return null;
  }

  Future<List<Attendance>> getHistory({int page = 1}) async {
    final response = await _apiService.get(
      '/attendances?page=$page',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final List<dynamic> data = response['data']['data'] ?? [];
      return data.map((json) => Attendance.fromJson(json)).toList();
    }

    return [];
  }

  Future<List<Attendance>> getHistoryFiltered({
    required int month,
    required int year,
  }) async {
    final response = await _apiService.get(
      '/attendances/history?month=$month&year=$year',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final List<dynamic> data = response['data'];
      return data.map((json) => Attendance.fromJson(json)).toList();
    }

    return [];
  }

  Future<void> exportToCSV() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final response = await http.get(
        Uri.parse('$baseUrl/attendances/export'),
        headers: {'Accept': 'text/csv', 'Authorization': 'Bearer $token'},
      );

      if (response.statusCode == 200) {
        // For web, download successful
        // CSV file will be downloaded automatically by browser
        return;
      } else {
        throw Exception('Failed to export data');
      }
    } catch (e) {
      throw Exception('Export failed: $e');
    }
  }
}
