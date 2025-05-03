import 'package:flutter/material.dart';

class TeamPage extends StatelessWidget {
  final Map<String, dynamic> userData;
  const TeamPage({super.key, required this.userData});

  @override
  Widget build(BuildContext context) {
    // Extract user information from userData
    final userName = userData['user']['name'] ?? 'User';
    return Center(
      child: Container(
        color: Colors.white,
        child: Text('This is team page for $userName'),
      ),
    );
  }
}