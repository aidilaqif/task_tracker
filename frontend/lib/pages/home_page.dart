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
  // int _counter = 0;
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

  // void _incrementCounter() {
  //   setState(() {
  // This call to setState tells the Flutter framework that something has
  // changed in this State, which causes it to rerun the build method below
  // so that the display can reflect the updated values. If we changed
  // _counter without calling setState(), then the build method would not be
  // called again, and so nothing would appear to happen.
  // _counter++;
  //   });
  // }

  
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
