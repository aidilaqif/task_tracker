import 'package:flutter/material.dart';

class ProfilePage extends StatelessWidget {
  final Map<String, dynamic> userData;
  const ProfilePage({super.key, required this.userData});

  @override
  Widget build(BuildContext context) {
    // Extract information from userData
    final userName = userData['user']['name'] ?? 'User';
    return Center(
      child: Container(
        color: Colors.white,
        child: Text('This is profile page for $userName'),
      ),
    );
  }
}