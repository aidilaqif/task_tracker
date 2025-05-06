import 'package:flutter/material.dart';

class ActivityPage extends StatelessWidget {
  final Map<String, dynamic> userData;
  const ActivityPage({super.key, required this.userData});

  @override
  Widget build(BuildContext context) {
    // Extract user information from userData
    final userName = userData['user']['name'] ?? 'User';
    return Center(
      child: Container(
        color: Colors.white,
        child: Text('This is activity page for $userName'),
      ),
    );
  }
}