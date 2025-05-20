import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/pages/login/login_page.dart';
import 'package:flutter_mobile_app/pages/tasks/tasks_page.dart';
import 'package:flutter_mobile_app/pages/activity/activity_page.dart';
import 'package:flutter_mobile_app/pages/team/team_page.dart';
import 'package:flutter_mobile_app/pages/profile/profile_page.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/services/socket_notification_service.dart';

class CustomNavigationBar extends StatefulWidget {
  final Map<String, dynamic> userData;
  
  const CustomNavigationBar({super.key, required this.userData});

  @override
  State<CustomNavigationBar> createState() => _CustomNavigationBarState();
}

class _CustomNavigationBarState extends State<CustomNavigationBar> {
  final ApiService _apiService = ApiService();
  int _selectedIndex = 0;
  late List<Widget> _pages;
   final GlobalKey<TasksPageState> _tasksPageKey = GlobalKey<TasksPageState>();
  
  @override
  void initState() {
    super.initState();

    // Initialize pages with user data
    _pages = [
      TasksPage(key: _tasksPageKey, userData: widget.userData),
      ActivityPage(userData: widget.userData),
      TeamPage(userData: widget.userData),
      ProfilePage(userData: widget.userData),
    ];
  }
  
  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  // Get page-specific app bar titles
  String _getAppBarTitle() {
    switch (_selectedIndex) {
      case 0: return 'My Tasks';
      case 1: return 'Activity';
      case 2: return 'My Team';
      case 3: return 'Profile';
      default: return 'Task Tracker';
    }
  }

  // Get page-specific app bar actions
  List<Widget> _getAppBarActions() {
    switch (_selectedIndex) {
      case 0: // Tasks page
        return [
          // Task filtering/sorting
          IconButton(
            icon: Icon(Icons.filter_list, color: AppTheme.textOnPrimaryColor),
            onPressed: () {
              _tasksPageKey.currentState?.showFilterOptions();
            },
          ),
          // Task refresh
          IconButton(
            icon: Icon(Icons.refresh, color: AppTheme.textOnPrimaryColor),
            onPressed: () {
              _tasksPageKey.currentState?.loadTasks();
            },
          ),
        ];
      
      case 1: // Activity page
        return [
          // Mark all as read
          IconButton(
            icon: Icon(Icons.done_all, color: AppTheme.textOnPrimaryColor),
            onPressed: () {
              // Refresh notifications
              final activityPage = _pages[1] as ActivityPage;
              activityPage.refreshNotifications();
            },
            tooltip: 'Refresh',
          ),
        ];
      
      case 2: // Team page
        return []; // No special actions needed
      
      case 3: // Profile page
        return [
          // Logout
          IconButton(
            icon: Icon(Icons.logout, color: AppTheme.textOnPrimaryColor),
            onPressed: _logout,
          ),
        ];
      
      default:
        return [];
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
        // Close socket connection
        SocketNotificationService().closeSocket();

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

        // Close socket connection
        SocketNotificationService().closeSocket();

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
      appBar: AppBar(
        title: Text(
          _getAppBarTitle(),
          style: TextStyle(
            color: AppTheme.textOnPrimaryColor,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: AppTheme.primaryColor,
        elevation: AppTheme.elevationSm,
        actions: _getAppBarActions(),
      ),
      body: _pages[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        backgroundColor: AppTheme.primaryColor,
        selectedItemColor: AppTheme.textOnPrimaryColor,
        unselectedItemColor: AppTheme.textSecondaryColor,
        selectedFontSize: 12,
        unselectedFontSize: 12,
        currentIndex: _selectedIndex,
        onTap: _onItemTapped,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.task_alt),
            label: 'Tasks',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.notifications_outlined),
            label: 'Activity',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people_outline),
            label: 'Team',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}