import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import '../providers/attendance_provider.dart';

class AttendanceScreen extends StatefulWidget {
  final bool showHistory;

  const AttendanceScreen({super.key, this.showHistory = false});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  @override
  void initState() {
    super.initState();
    initializeDateFormatting('id_ID', null);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = Provider.of<AttendanceProvider>(context, listen: false);
      provider.loadTodayStatus();
      if (widget.showHistory) {
        provider.loadHistory();
      }
    });
  }

  Future<void> _handleCheckIn() async {
    final provider = Provider.of<AttendanceProvider>(context, listen: false);

    final success = await provider.checkIn(location: 'Mobile App');

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Absen masuk berhasil!'),
            backgroundColor: Colors.green,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(provider.errorMessage ?? 'Absen masuk gagal'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _handleCheckOut() async {
    final provider = Provider.of<AttendanceProvider>(context, listen: false);

    final success = await provider.checkOut();

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Absen keluar berhasil!'),
            backgroundColor: Colors.green,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(provider.errorMessage ?? 'Absen keluar gagal'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      initialIndex: widget.showHistory ? 1 : 0,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Absensi'),
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
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Hari Ini', icon: Icon(Icons.today)),
              Tab(text: 'Riwayat', icon: Icon(Icons.history)),
            ],
          ),
        ),
        body: TabBarView(children: [_buildCheckInOutTab(), _buildHistoryTab()]),
      ),
    );
  }

  Widget _buildCheckInOutTab() {
    return Consumer<AttendanceProvider>(
      builder: (context, provider, child) {
        return RefreshIndicator(
          onRefresh: () => provider.loadTodayStatus(),
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
                // Current Date Card
                Card(
                  elevation: 4,
                  child: Padding(
                    padding: const EdgeInsets.all(20.0),
                    child: Column(
                      children: [
                        Icon(
                          Icons.calendar_today,
                          size: 48,
                          color: Theme.of(context).primaryColor,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          DateFormat('EEEE, MMMM d, y').format(DateTime.now()),
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          DateFormat('HH:mm:ss').format(DateTime.now()),
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Tombol Masuk/Keluar
                if (provider.isLoading)
                  const Center(child: CircularProgressIndicator())
                else if (!provider.hasCheckedInToday)
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _handleCheckIn,
                      icon: const Icon(Icons.login, size: 32),
                      label: const Text(
                        'Absen Masuk',
                        style: TextStyle(fontSize: 20),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.all(24),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  )
                else if (!provider.hasCheckedOutToday)
                  Column(
                    children: [
                      // Status Card
                      Card(
                        elevation: 2,
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Status:'),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 12,
                                      vertical: 6,
                                    ),
                                    decoration: BoxDecoration(
                                      color: _getStatusColor(
                                        provider.todayAttendance!.status,
                                      ).withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      _translateStatus(
                                        provider.todayAttendance!.status,
                                      ),
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: _getStatusColor(
                                          provider.todayAttendance!.status,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const Divider(height: 24),
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Waktu Masuk:'),
                                  Text(
                                    DateFormat('HH:mm:ss').format(
                                      provider.todayAttendance!.checkInTime,
                                    ),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _handleCheckOut,
                          icon: const Icon(Icons.logout, size: 32),
                          label: const Text(
                            'Absen Keluar',
                            style: TextStyle(fontSize: 20),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.red,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.all(24),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                    ],
                  )
                else
                  Card(
                    elevation: 2,
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        children: [
                          const Icon(
                            Icons.check_circle,
                            size: 64,
                            color: Colors.green,
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'Anda telah menyelesaikan absensi hari ini!',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('Masuk:'),
                              Text(
                                DateFormat(
                                  'HH:mm:ss',
                                ).format(provider.todayAttendance!.checkInTime),
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('Keluar:'),
                              Text(
                                DateFormat('HH:mm:ss').format(
                                  provider.todayAttendance!.checkOutTime!,
                                ),
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
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
    );
  }

  Widget _buildHistoryTab() {
    return Consumer<AttendanceProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.history.isEmpty) {
          return const Center(child: CircularProgressIndicator());
        }

        if (provider.history.isEmpty) {
          return const Center(child: Text('No attendance history found'));
        }

        return RefreshIndicator(
          onRefresh: () => provider.loadHistory(),
          child: ListView.builder(
            padding: const EdgeInsets.all(16.0),
            itemCount: provider.history.length,
            itemBuilder: (context, index) {
              final attendance = provider.history[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: _getStatusColor(attendance.status),
                    child: Icon(
                      attendance.checkOutTime != null
                          ? Icons.check_circle
                          : Icons.login,
                      color: Colors.white,
                    ),
                  ),
                  title: Text(
                    DateFormat(
                      'EEEE, d MMMM y',
                      'id_ID',
                    ).format(attendance.checkInTime),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(
                        'Masuk: ${DateFormat('HH:mm:ss').format(attendance.checkInTime)}',
                      ),
                      if (attendance.checkOutTime != null)
                        Text(
                          'Keluar: ${DateFormat('HH:mm:ss').format(attendance.checkOutTime!)}',
                        ),
                    ],
                  ),
                  trailing: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor(
                        attendance.status,
                      ).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      attendance.status.toUpperCase(),
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: _getStatusColor(attendance.status),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        );
      },
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
}
