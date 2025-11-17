import '../models/user.dart';
import 'api_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await _apiService.post(
      '/register',
      body: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );

    if (response['success'] == true && response['data'] != null) {
      final token = response['data']['token'];
      await _apiService.saveToken(token);
    }

    return response;
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await _apiService.post(
      '/login',
      body: {'email': email, 'password': password},
    );

    if (response['success'] == true && response['data'] != null) {
      final token = response['data']['token'];
      await _apiService.saveToken(token);
    }

    return response;
  }

  Future<Map<String, dynamic>> logout() async {
    final response = await _apiService.post('/logout', requiresAuth: true);
    await _apiService.removeToken();
    return response;
  }

  Future<User?> getCurrentUser() async {
    final response = await _apiService.get('/user', requiresAuth: true);

    if (response['success'] == true && response['data'] != null) {
      return User.fromJson(response['data']);
    }

    return null;
  }

  Future<bool> isLoggedIn() async {
    final token = await _apiService.getToken();
    return token != null;
  }
}
