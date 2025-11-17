import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/attendance_provider.dart';
import '../providers/task_provider.dart';
import 'attendance_screen.dart';
import 'tasks_screen.dart';
import 'profile_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen>
    with SingleTickerProviderStateMixin {
  int _selectedIndex = 0;
  late AnimationController _animationController;

  @override
  void initState() {
    super.initState();

    _animationController = AnimationController(
      duration: const Duration(milliseconds: 250),
      vsync: this,
    );

    _animationController.forward();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<AttendanceProvider>(context, listen: false).loadTodayStatus();

      // Setup task notification handler
      final taskProvider = Provider.of<TaskProvider>(context, listen: false);
      taskProvider.connectWebSocket();
      taskProvider.onNotification = (title, message) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.notification_important, color: Colors.white),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(message, style: const TextStyle(fontSize: 14)),
                      ],
                    ),
                  ),
                ],
              ),
              backgroundColor: Colors.orange[800],
              duration: const Duration(seconds: 30),
              behavior: SnackBarBehavior.floating,
              action: SnackBarAction(
                label: 'LIHAT',
                textColor: Colors.white,
                onPressed: () {
                  setState(() {
                    _selectedIndex = 2; // Navigate to Tasks
                  });
                },
              ),
            ),
          );
        }
      };
    });
  }

  @override
  void dispose() {
    _animationController.dispose();
    final taskProvider = Provider.of<TaskProvider>(context, listen: false);
    taskProvider.disconnectWebSocket();
    super.dispose();
  }

  void _onNavItemTapped(int index) {
    if (_selectedIndex != index) {
      _animationController.reset();
      setState(() {
        _selectedIndex = index;
      });
      _animationController.forward();
    }
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      _buildHomeScreen(),
      const AttendanceScreen(),
      const TasksScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      body: AnimatedSwitcher(
        duration: const Duration(milliseconds: 250),
        transitionBuilder: (Widget child, Animation<double> animation) {
          return FadeTransition(
            opacity: animation,
            child: SlideTransition(
              position:
                  Tween<Offset>(
                    begin: const Offset(0.1, 0),
                    end: Offset.zero,
                  ).animate(
                    CurvedAnimation(
                      parent: animation,
                      curve: Curves.easeInOutCubic,
                    ),
                  ),
              child: child,
            ),
          );
        },
        child: IndexedStack(
          key: ValueKey<int>(_selectedIndex),
          index: _selectedIndex,
          children: screens,
        ),
      ),
      bottomNavigationBar: _buildCustomNavBar(),
    );
  }

  Widget _buildHomeScreen() {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Halaman Utama'),
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
      ),
      body: Consumer2<AuthProvider, AttendanceProvider>(
        builder: (context, authProvider, attendanceProvider, child) {
          final user = authProvider.user;

          return RefreshIndicator(
            onRefresh: () async {
              await attendanceProvider.loadTodayStatus();
            },
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // User Info Card
                  Card(
                    elevation: 4,
                    child: Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Column(
                        children: [
                          CircleAvatar(
                            radius: 40,
                            backgroundColor: Theme.of(context).primaryColor,
                            child: Text(
                              user?.name.substring(0, 1).toUpperCase() ?? 'U',
                              style: const TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),
                          Text(
                            user?.name ?? 'User',
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            user?.email ?? '',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Today's Attendance Status
                  Card(
                    elevation: 4,
                    child: Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(
                                Icons.calendar_today,
                                color: Theme.of(context).primaryColor,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'Absensi Hari Ini',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          if (attendanceProvider.isLoading)
                            const Center(child: CircularProgressIndicator())
                          else if (attendanceProvider.hasCheckedInToday)
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                _buildStatusRow(
                                  'Status',
                                  _translateStatus(
                                    attendanceProvider.todayAttendance!.status,
                                  ),
                                  _getStatusColor(
                                    attendanceProvider.todayAttendance!.status,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                _buildStatusRow(
                                  'Masuk',
                                  _formatTime(
                                    attendanceProvider
                                        .todayAttendance!
                                        .checkInTime,
                                  ),
                                  Colors.green,
                                ),
                              ],
                            )
                          else
                            const Center(
                              child: Padding(
                                padding: EdgeInsets.all(16.0),
                                child: Text(
                                  'Anda belum absen hari ini',
                                  style: TextStyle(
                                    fontSize: 16,
                                    color: Colors.grey,
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildCustomNavBar() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      height: 70,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(35),
        boxShadow: [
          BoxShadow(
            color: Colors.blue.withValues(alpha: 0.15),
            blurRadius: 20,
            offset: const Offset(0, 5),
            spreadRadius: 0,
          ),
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(35),
        child: Stack(
          children: [
            // Animated sliding indicator
            AnimatedPositioned(
              duration: const Duration(milliseconds: 800),
              curve: Curves.easeInOutQuart,
              left:
                  _selectedIndex * (MediaQuery.of(context).size.width - 40) / 4,
              right:
                  (3 - _selectedIndex) *
                  (MediaQuery.of(context).size.width - 40) /
                  4,
              top: 0,
              bottom: 0,
              child: Center(
                child: Container(
                  width: 50,
                  height: 50,
                  decoration: BoxDecoration(
                    color: Colors.blue.shade600.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                  ),
                ),
              ),
            ),
            // Navigation items
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildNavItem(icon: Icons.home_rounded, index: 0),
                _buildNavItem(icon: Icons.access_time_rounded, index: 1),
                _buildNavItem(icon: Icons.assignment_rounded, index: 2),
                _buildNavItem(icon: Icons.person_rounded, index: 3),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNavItem({required IconData icon, required int index}) {
    final isSelected = _selectedIndex == index;

    return Expanded(
      child: GestureDetector(
        onTap: () {
          _onNavItemTapped(index);
        },
        child: Container(
          color: Colors.transparent,
          child: Center(
            child: AnimatedScale(
              duration: const Duration(milliseconds: 700),
              curve: Curves.easeInOutQuart,
              scale: isSelected ? 1.15 : 1.0,
              child: Icon(
                icon,
                color: isSelected ? Colors.blue.shade700 : Colors.grey.shade400,
                size: 26,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusRow(String label, String value, Color color) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            value,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ),
      ],
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

  String _translateStatus(String status) {
    switch (status.toLowerCase()) {
      case 'present':
        return 'HADIR';
      case 'late':
        return 'TERLAMBAT';
      case 'absent':
        return 'ALPHA';
      default:
        return status.toUpperCase();
    }
  }

  String _formatTime(DateTime time) {
    return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
  }
}
