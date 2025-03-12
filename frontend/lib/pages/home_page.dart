import 'package:flutter/material.dart';
import 'package:frontend/services/api_services.dart';
import 'package:frontend/models/task_model.dart';
import 'package:frontend/pages/add_task_page.dart';
import 'package:frontend/widgets/task_card.dart';

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
  int userId = 0;
  List<Task> tasks = [];
  String errorMessage = '';

  @override
  void initState() {
    super.initState();

    if (widget.userData != null) {
      setState(() {
        userName = widget.userData['name'] ?? "User";
        userId =
            widget.userData['id'] is String
                ? int.tryParse(widget.userData['id']) ?? 0
                : widget.userData['id'] ?? 0;
        isLoading = false;
      });
      fetchTasks();
    } else {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> fetchTasks() async {
    if (userId == 0) return;

    setState(() {
      isLoading = true;
      errorMessage = '';
    });

    try {
      final response = await apiService.getAllTasks(userId);

      if (response['status'] == true) {
        setState(() {
          tasks =
              (response['data'] as List)
                  .map((taskJson) => Task.fromJson(taskJson))
                  .toList();
          isLoading = false;
        });
      } else {
        setState(() {
          errorMessage = response['msg'] ?? 'Failed to load tasks';
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        errorMessage = 'Error: $e';
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
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: fetchTasks,
            tooltip: 'Refersh Taks',
          ),
        ],
      ),
      body:
          isLoading
              ? const Center(child: CircularProgressIndicator())
              : errorMessage.isNotEmpty
              ? Center(
                child: Text(errorMessage, style: TextStyle(color: Colors.red)),
              )
              : Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Text(
                      'Welcome, $userName!',
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Text(
                      'Your Tasks',
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                  ),
                  Expanded(
                    child:
                        tasks.isEmpty
                            ? Center(
                              child: Text(
                                'No tasks found. Create your first task!',
                              ),
                            )
                            : ListView.builder(
                              padding: const EdgeInsets.all(8.0),
                              itemCount: tasks.length,
                              itemBuilder: (context, index) {
                                return TaskCard(
                                  task: tasks[index],
                                  onDelete: () async {
                                    // Delete functionality
                                    await apiService.deleteTask(
                                      tasks[index].id,
                                    );
                                    fetchTasks();
                                  },
                                  onStatusChange: (newStatus) async {
                                    // Implement status update functionality
                                    await apiService.editTask(tasks[index].id, {
                                      'status': newStatus,
                                    });
                                    fetchTasks();
                                  },
                                );
                              },
                            ),
                  ),
                ],
              ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => AddTaskPage(userId: userId),
            ),
          );
          if (result == true) {
            fetchTasks();
          }
        },
        tooltip: 'Add Task',
        child: const Icon(Icons.add),
      ),
    );
  }
}
