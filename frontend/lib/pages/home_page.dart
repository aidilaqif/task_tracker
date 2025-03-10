import 'package:flutter/material.dart';
import 'package:frontend/services/api_services.dart';

class MyHomePage extends StatefulWidget {
  const MyHomePage({super.key, required this.title, this.userData});
  final String title;
  final dynamic userData;

  @override
  State<MyHomePage> createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  final ApiService apiService = ApiService();
  bool isLoading = true;
  String userName = "User";

  @override
  void initState() {
    super.initState();

    if (widget.userData != null) {
      setState(() {
        userName = widget.userData['name'] ?? "User";
        isLoading = false;
      });
    } else {
      setState(() {
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Theme.of(context).colorScheme.inversePrimary,
        title: Text(widget.title),
      ),
      body: isLoading ? const Center(child: CircularProgressIndicator(),) : Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Text(
              'Welcome, $userName!',
              ),
            Text(
              'Task List'
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: null,
        tooltip: 'Fetch Data',
        child: const Icon(Icons.refresh),
      ),
    );
  }
}
