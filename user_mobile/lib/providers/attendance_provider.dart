import 'package:flutter/material.dart';
import '../models/attendance.dart';
import '../services/attendance_service.dart';

class AttendanceProvider with ChangeNotifier {
  final AttendanceService _attendanceService = AttendanceService();

  Attendance? _todayAttendance;
  List<Attendance> _history = [];
  List<Attendance> _historyList = [];
  bool _isLoading = false;
  String? _errorMessage;

  Attendance? get todayAttendance => _todayAttendance;
  List<Attendance> get history => _history;
  List<Attendance> get historyList => _historyList;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get hasCheckedInToday => _todayAttendance != null;
  bool get hasCheckedOutToday => _todayAttendance?.checkOutTime != null;

  Future<void> loadTodayStatus() async {
    _isLoading = true;
    notifyListeners();

    try {
      _todayAttendance = await _attendanceService.getTodayStatus();
      _errorMessage = null;
    } catch (e) {
      _errorMessage = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<bool> checkIn({String? location, String? notes}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _attendanceService.checkIn(
        location: location,
        notes: notes,
      );

      if (response['success'] == true) {
        _todayAttendance = Attendance.fromJson(response['data']);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response['message'];
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> checkOut() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _attendanceService.checkOut();

      if (response['success'] == true) {
        _todayAttendance = Attendance.fromJson(response['data']);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response['message'];
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> loadHistory({int page = 1, int? month, int? year}) async {
    _isLoading = true;
    notifyListeners();

    try {
      if (month != null && year != null) {
        _historyList = await _attendanceService.getHistoryFiltered(
          month: month,
          year: year,
        );
      } else {
        _history = await _attendanceService.getHistory(page: page);
      }
      _errorMessage = null;
    } catch (e) {
      _errorMessage = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<bool> exportAttendance() async {
    try {
      await _attendanceService.exportToCSV();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }
}
