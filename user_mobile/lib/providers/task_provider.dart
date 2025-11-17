import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:web_socket_channel/web_socket_channel.dart';
import '../models/task.dart';
import '../services/task_service.dart';

class TaskProvider with ChangeNotifier {
  final TaskService _taskService = TaskService();

  List<Task> _availableTasks = [];
  List<Task> _myTasks = [];
  bool _isLoading = false;
  String? _error;

  WebSocketChannel? _channel;
  StreamSubscription? _subscription;

  // Callback untuk notifikasi
  Function(String title, String message)? onNotification;

  List<Task> get availableTasks => _availableTasks;
  List<Task> get myTasks => _myTasks;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Task? get currentTask {
    try {
      return _myTasks.firstWhere((task) => task.status == 'accepted');
    } catch (e) {
      return null;
    }
  }

  bool get hasActiveTask => currentTask != null;

  // Connect to WebSocket for real-time notifications
  void connectWebSocket() {
    try {
      _channel = WebSocketChannel.connect(
        Uri.parse(
          'ws://127.0.0.1:8080/app/local-key?protocol=7&client=js&version=8.4.0-rc2&flash=false',
        ),
      );

      _channel?.sink.add(
        jsonEncode({
          'event': 'pusher:subscribe',
          'data': {'channel': 'tasks'},
        }),
      );

      _subscription = _channel?.stream.listen(
        (message) {
          _handleWebSocketMessage(message);
        },
        onError: (error) {
          debugPrint('WebSocket error: $error');
        },
        onDone: () {
          debugPrint('WebSocket connection closed');
          // Auto reconnect after 5 seconds
          Future.delayed(const Duration(seconds: 5), () {
            if (_channel == null || _channel!.closeCode != null) {
              connectWebSocket();
            }
          });
        },
      );

      debugPrint('WebSocket connected to tasks channel');
    } catch (e) {
      debugPrint('Failed to connect WebSocket: $e');
    }
  }

  void _handleWebSocketMessage(dynamic message) {
    try {
      final data = jsonDecode(message);

      if (data['event'] == 'App\\Events\\TaskCreated') {
        final taskData = jsonDecode(data['data']);
        debugPrint('New task received: ${taskData['title']}');

        // Trigger notification popup
        if (onNotification != null && !hasActiveTask) {
          onNotification!(
            'Ada Pekerjaan Baru!',
            'Harap segera dikerjakan: ${taskData['title']}',
          );
        }

        // Reload available tasks
        loadAvailableTasks();
      }
    } catch (e) {
      debugPrint('Error handling WebSocket message: $e');
    }
  }

  void disconnectWebSocket() {
    _subscription?.cancel();
    _channel?.sink.close();
    _channel = null;
    _subscription = null;
  }

  Future<void> loadAvailableTasks() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _availableTasks = await _taskService.getAvailableTasks();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMyTasks() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _myTasks = await _taskService.getMyTasks();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> acceptTask(int taskId) async {
    try {
      await _taskService.acceptTask(taskId);
      await loadMyTasks();
      await loadAvailableTasks();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> rejectTask(int taskId) async {
    try {
      await _taskService.rejectTask(taskId);
      await loadAvailableTasks();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> completeTask(int taskId) async {
    try {
      await _taskService.completeTask(taskId);
      await loadMyTasks();
      await loadAvailableTasks();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  @override
  void dispose() {
    disconnectWebSocket();
    super.dispose();
  }
}
