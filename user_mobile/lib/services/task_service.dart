import '../models/task.dart';
import 'api_service.dart';

class TaskService {
  final ApiService _apiService = ApiService();

  // Get available tasks (pending tasks)
  Future<List<Task>> getAvailableTasks() async {
    try {
      final response = await _apiService.get(
        '/tasks/available',
        requiresAuth: true,
      );
      final List<dynamic> tasksJson = response['tasks'] ?? [];
      return tasksJson.map((json) => Task.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Gagal memuat tugas tersedia: $e');
    }
  }

  // Get my tasks (accepted/completed)
  Future<List<Task>> getMyTasks() async {
    try {
      final response = await _apiService.get(
        '/tasks/my-tasks',
        requiresAuth: true,
      );
      final List<dynamic> tasksJson = response['tasks'] ?? [];
      return tasksJson.map((json) => Task.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Gagal memuat tugas saya: $e');
    }
  }

  // Accept task
  Future<Map<String, dynamic>> acceptTask(int taskId) async {
    try {
      return await _apiService.post(
        '/tasks/$taskId/accept',
        requiresAuth: true,
      );
    } catch (e) {
      throw Exception('Gagal menerima tugas: $e');
    }
  }

  // Reject task
  Future<Map<String, dynamic>> rejectTask(int taskId) async {
    try {
      return await _apiService.post(
        '/tasks/$taskId/reject',
        requiresAuth: true,
      );
    } catch (e) {
      throw Exception('Gagal menolak tugas: $e');
    }
  }

  // Complete task
  Future<Map<String, dynamic>> completeTask(int taskId) async {
    try {
      return await _apiService.post(
        '/tasks/$taskId/complete',
        requiresAuth: true,
      );
    } catch (e) {
      throw Exception('Gagal menyelesaikan tugas: $e');
    }
  }
}
