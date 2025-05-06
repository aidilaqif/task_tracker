import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/pages/login_page.dart';
import 'package:flutter_mobile_app/services/api_services.dart';

class ProfilePage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const ProfilePage({super.key, required this.userData});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  Map<String, dynamic> _profileData = {};
  Map<String, dynamic> _userStats = {};
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _loadProfileData();
  }

  Future<void> _loadProfileData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final userId = int.parse(widget.userData['user']['id'].toString());
      final profileResponse = await _apiService.getUserProfile(userId);

      if (profileResponse['status']) {
        setState(() {
          _profileData = profileResponse['data'] ?? {};
          _isLoading = false;
        });

        _loadUserStats(userId);
      } else {
        setState(() {
          _errorMessage = profileResponse['msg'] ?? 'Failed to load profile';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error loading profile: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  Future<void> _loadUserStats(int userId) async {
    try {
      final statsResponse = await _apiService.getUserTasks(userId);

      if (statsResponse['status']) {
        final tasks = statsResponse['data'] ?? [];
        int totalTasks = tasks.length;
        int completedTasks =
            tasks.where((task) => task['status'] == 'completed').length;
        int pendingTasks =
            tasks.where((task) => task['status'] == 'pending').length;
        int inProgressTasks =
            tasks.where((task) => task['status'] == 'in-progress').length;

        double completionRate =
            totalTasks > 0 ? (completedTasks / totalTasks * 100) : 0.0;

        setState(() {
          _userStats = {
            'total_tasks': totalTasks,
            'completed_tasks': completedTasks,
            'pending_tasks': pendingTasks,
            'in_progress_tasks': inProgressTasks,
            'completion_rate': completionRate,
          };
        });
      }
    } catch (e) {
      // Silently handle stats loading errors
      print('Error loading user stats: ${e.toString()}');
    }
  }

  Future<void> _logout() async {
    // Show confirmation dialog
    bool confirm = await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Logout', style: AppTheme.titleStyle),
        content: Text(
          'Are you sure you want to logout?',
          style: AppTheme.bodyStyle,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text(
              'Cancel',
              style: TextStyle(color: AppTheme.textSecondaryColor),
            ),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.errorColor,
            ),
            child: Text(
              'Logout',
              style: TextStyle(color: Colors.white)
            ),
          )
        ],
      ),
    ) ?? false;

    if (confirm) {
      try {
        // Call logout API
        await _apiService.logoutUser();

        // Regardless of response navigate to login page
        if (!mounted) return;

        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder:(context) => const LoginPage()),
          (route) => false,
        );
      } catch (e) {
        // Even if there's an error, still logout locally
        if (!mounted) return;

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error duing logout: ${e.toString()}'),
            backgroundColor: AppTheme.errorColor,
          ),
        );

        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (context) => const LoginPage()),
          (route) => false,
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body:
          _isLoading
              ? Center(child: CircularProgressIndicator())
              : _errorMessage.isNotEmpty
              ? _buildErrorView()
              : _buildProfileView(),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 70, color: AppTheme.errorColor),
            SizedBox(height: AppTheme.spacingMd),
            Text('Error loading profile', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              _errorMessage,
              style: AppTheme.bodyStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingLg),
            ElevatedButton(
              onPressed: _loadProfileData,
              child: Text('Try Again'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileView() {
    // Extract user information from the profile data
    final name =
        _profileData['name'] ?? widget.userData['user']['name'] ?? 'User';
    final email =
        _profileData['email'] ?? widget.userData['user']['email'] ?? '';
    final role =
        _profileData['role'] ?? widget.userData['role'] ?? 'Team Member';

    // Format team information
    String teamInfo = 'Not assigned to any team';
    if (_profileData['team_id'] != null) {
      teamInfo = 'Team ID: ${_profileData['team_id']}';
    }

    return RefreshIndicator(
      onRefresh: _loadProfileData,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Padding(
          padding: const EdgeInsets.all(AppTheme.spacingLg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Placeholder for profile view
              _ProfileHeader(name: name, email: email, role: role),
              SizedBox(height: AppTheme.spacingLg),
              // Placeholder for team information
              _InfoSection(
                title: 'Team Information',
                items: [
                  _InfoItem(icon: Icons.people, label: 'Team', value: teamInfo),
                ],
              ),
              SizedBox(height: AppTheme.spacingLg),
              // Placeholder for performance metrics
              if (_userStats.isNotEmpty) ...[
                _InfoSection(
                  title: 'Performance Metrics',
                  items: [
                    _MetricItem(
                      label: 'Total Tasks',
                      value: _userStats['total_tasks'].toString(),
                      icon: Icons.assignment_outlined,
                    ),
                    Divider(),
                    _MetricItem(
                      label: 'Completed Tasks',
                      value: _userStats['completed_tasks'].toString(),
                      icon: Icons.assignment_turned_in_outlined,
                    ),
                    Divider(),
                    _MetricItem(
                      label: 'Pending Tasks',
                      value: _userStats['pending_tasks'].toString(),
                      icon: Icons.assignment_late_outlined,
                    ),
                    Divider(),
                    _MetricItem(
                      label: 'In Progress Tasks',
                      value: _userStats['in_progress_tasks'].toString(),
                      icon: Icons.assignment_ind_outlined,
                    ),
                    Divider(),
                    _CompletionProgress(
                      completionRate: _userStats['completion_rate'],
                    ),
                  ],
                ),
              ],
              SizedBox(height: AppTheme.spacingLg),
              // Placeholder for account actions
              _InfoSection(
                title: 'Account',
                items: [
                  InkWell(
                    onTap: _logout,
                    child: Padding(
                      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingMd),
                      child: Row(
                        children: [
                          Icon(
                            Icons.logout,
                            color: AppTheme.errorColor,
                          ),
                          SizedBox(width: AppTheme.spacingMd),
                          Text(
                            'Logout',
                            style: TextStyle(
                              color: AppTheme.errorColor,
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          Spacer(),
                          Icon(
                            Icons.chevron_right,
                            color: AppTheme.textSecondaryColor,
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              SizedBox(height: AppTheme.spacingLg),
              const _AppInfo(),
            ],
          ),
        ),
      ),
    );
  }
}


class _ProfileHeader extends StatelessWidget {
  final String name;
  final String email;
  final String role;

  const _ProfileHeader({
    required this.name,
    required this.email,
    required this.role,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Avatar Cirlce
        CircleAvatar(
          radius: 50,
          backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
          child: Text(
            name.isNotEmpty ? name[0].toUpperCase() : '?',
            style: TextStyle(
              fontSize: 40,
              fontWeight: FontWeight.bold,
              color: AppTheme.primaryColor,
            ),
          ),
        ),
        SizedBox(height: AppTheme.spacingMd),
        // User Name
        Text(name, style: AppTheme.headlineStyle, textAlign: TextAlign.center),

        // Role Badge
        Container(
          padding: EdgeInsets.symmetric(
            horizontal: AppTheme.spacingMd,
            vertical: AppTheme.spacingSm,
          ),
          decoration: BoxDecoration(
            color:
                role.toLowerCase() == 'admin'
                    ? AppTheme.highPriorityBgColor
                    : AppTheme.lowPriorityBgColor,
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
          ),
          child: Text(
            role,
            style: TextStyle(
              color: AppTheme.textSecondaryColor,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        SizedBox(height: AppTheme.spacingMd),

        // Email
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.email_outlined,
              size: 16,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(width: AppTheme.spacingMd),
            Text(
              email,
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _InfoSection extends StatelessWidget {
  final String title;
  final List<Widget> items;

  const _InfoSection({required this.title, required this.items});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: AppTheme.titleStyle),
        SizedBox(height: AppTheme.spacingMd),
        Card(
          elevation: AppTheme.elevationSm,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
          ),
          child: Padding(
            padding: EdgeInsets.all(AppTheme.spacingMd),
            child: Column(children: items),
          ),
        ),
      ],
    );
  }
}

class _InfoItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoItem({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingSm),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppTheme.primaryColor),
          SizedBox(width: AppTheme.spacingMd),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: AppTheme.captionStyle),
                Text(value, style: AppTheme.bodyStyle),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _MetricItem extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _MetricItem({
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingSm),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppTheme.primaryColor),
          SizedBox(width: AppTheme.spacingMd),
          Text(label, style: AppTheme.subtitleStyle),
          Spacer(),
          Text(
            value,
            style: AppTheme.subtitleStyle.copyWith(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
}

class _CompletionProgress extends StatelessWidget {
  final double completionRate;
  const _CompletionProgress({required this.completionRate});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingSm),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.trending_up, size: 20, color: AppTheme.primaryColor),
              SizedBox(width: AppTheme.spacingMd),
              Text('Completion Rate', style: AppTheme.subtitleStyle),
              Spacer(),
              Text(
                '${completionRate.toStringAsFixed(1)}%',
                style: AppTheme.subtitleStyle.copyWith(
                  color: AppTheme.primaryColor,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          SizedBox(height: AppTheme.spacingSm),
          ClipRRect(
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
            child: LinearProgressIndicator(
              value: completionRate / 100,
              backgroundColor: AppTheme.dividerColor,
              valueColor: AlwaysStoppedAnimation<Color>(
                AppTheme.getProgressColor(completionRate.toInt()),
              ),
              minHeight: 8,
            ),
          ),
        ],
      ),
    );
  }
}

class _AppInfo extends StatelessWidget {
  const _AppInfo();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Divider(height: AppTheme.spacingLg),
        SizedBox(height: AppTheme.spacingMd),
        Text(
          'Task Tracker Mobile',
          style: AppTheme.captionStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
        SizedBox(height: AppTheme.spacingSm),
        Text(
          'Version 1.0.0',
          style: AppTheme.captionStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
        SizedBox(height: AppTheme.spacingSm),
        Text(
          '© 2025 Task Tracker',
          style: AppTheme.captionStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
      ],
    );
  }
}