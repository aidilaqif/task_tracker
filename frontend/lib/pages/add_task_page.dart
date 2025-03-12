import 'package:flutter/material.dart';
import 'package:frontend/services/api_services.dart';
import 'package:frontend/widgets/custom_form_field.dart';
import 'package:intl/intl.dart';

class AddTaskPage extends StatefulWidget {
  final int userId;

  const AddTaskPage({super.key, required this.userId});

  @override
  State<AddTaskPage> createState() => _AddTaskPageState();
}

class _AddTaskPageState extends State<AddTaskPage> {
  final ApiService apiService = ApiService();
  final TextEditingController _titleController = TextEditingController();
  final TextEditingController _descriptionController = TextEditingController();
  final TextEditingController _dueDateController = TextEditingController();

  String _selectedPriority = 'medium';
  String _selectedStatus = 'pending';
  String _statusMessage = '';
  bool _isLoading = false;
  DateTime? _selectedDate;

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime(2100),
    );

    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
        _dueDateController.text = DateFormat('yyyy-MM-dd').format(picked);
      });
    }
  }

  void _submitTask() async {
    if (_titleController.text.isEmpty) {
      setState(() {
        _statusMessage = 'Title is required';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _statusMessage = '';
    });

    try {
      final response = await apiService.addTask({
        'user_id': widget.userId,
        'title': _titleController.text,
        'description': _descriptionController.text,
        'due_date':
            _dueDateController.text.isEmpty ? null : _dueDateController.text,
        'status': _selectedStatus,
        'priority': _selectedPriority,
      });

      setState(() {
        _statusMessage = response['msg'] ?? 'Unknown response';
        _isLoading = false;
      });

      if (response['status'] == true) {
        // Show success message and return to previous screen
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Task created successfully!',
              style: TextStyle(color: Colors.white),
            ),
          ),
        );

        // Return to the previous screen with result
        Navigator.pop(context, true);
      }
    } catch (e) {
      setState(() {
        _statusMessage = 'Error: $e';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Add New Task'),
        backgroundColor: Theme.of(context).colorScheme.inversePrimary,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CustomFormField(
              isHidden: false,
              labelText: "Title",
              controller: _titleController,
              prefixIcon: Icons.title,
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _descriptionController,
              decoration: InputDecoration(
                labelText: 'Description',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                prefixIcon: const Icon(Icons.description),
                contentPadding: const EdgeInsets.symmetric(
                  vertical: 16.0,
                  horizontal: 16.0,
                ),
              ),
              maxLines: 3,
            ),
            const SizedBox(height: 16),
            GestureDetector(
              onTap: () => _selectDate(context),
              child: AbsorbPointer(
                child: CustomFormField(
                  isHidden: false,
                  labelText: "Due Data (Optional)",
                  controller: _dueDateController,
                  prefixIcon: Icons.calendar_today,
                ),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Priority',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: RadioListTile<String>(
                    title: const Text('Low'),
                    value: 'low',
                    groupValue: _selectedPriority,
                    onChanged: (value) {
                      setState(() {
                        _selectedPriority = value!;
                      });
                    },
                    activeColor: Colors.green,
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                Expanded(
                  child: RadioListTile<String>(
                    title: const Text('Medium'),
                    value: 'medium',
                    groupValue: _selectedPriority,
                    onChanged: (value) {
                      setState(() {
                        _selectedPriority = value!;
                      });
                    },
                    activeColor: Colors.orange,
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                Expanded(
                  child: RadioListTile<String>(
                    title: const Text('High'),
                    value: 'high',
                    groupValue: _selectedPriority,
                    onChanged: (value) {
                      setState(() {
                        _selectedPriority = value!;
                      });
                    },
                    activeColor: Colors.red,
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Text(
              'Status',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<String>(
              value: _selectedStatus,
              decoration: InputDecoration(
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 8,
                ),
              ),
              items: const [
                DropdownMenuItem(value: 'pending', child: Text('Pending')),
                DropdownMenuItem(
                  value: 'in-progress',
                  child: Text('In Progress'),
                ),
                DropdownMenuItem(value: 'completed', child: Text('Completed')),
              ],
              onChanged: (String? newValue) {
                setState(() {
                  _selectedStatus = newValue!;
                });
              },
            ),
            const SizedBox(height: 24),
            if (_statusMessage.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(bottom: 16.0),
                child: Text(
                  _statusMessage,
                  style: TextStyle(
                    color:
                        _statusMessage.contains('successfully')
                            ? Colors.green
                            : Colors.red,
                  ),
                ),
              ),
            _isLoading
                ? const Center(child: CircularProgressIndicator())
                : SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _submitTask,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).colorScheme.primary,
                      foregroundColor: Colors.white
                    ),
                    child: const Text(
                      'Create Task',
                      style: TextStyle(fontSize:16),
                      ),
                  ),
                ),
          ],
        ),
      ),
    );
  }
}
