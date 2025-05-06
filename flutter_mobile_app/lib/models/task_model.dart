class Task {
  final int id;
  final int userId;
  final String title;
  final String description;
  final String? dueDate;
  final String status;
  final String priority;
  final String progress;
  final String createdAt;
  final String updatedAt;

  Task({
    required this.id,
    required this.userId,
    required this.title,
    required this.description,
    this.dueDate,
    required this.status,
    required this.priority,
    required this.progress,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      id: json['id'] is String ? int.parse(json['id']) : json['id'],
      userId:
          json['user_id'] is String
              ? int.parse(json['user_id'])
              : json['user_id'],
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      dueDate: json['due_date'],
      status: json['status'] ?? 'pending',
      priority: json['priority'] ?? 'medium',
      progress: json['progress'] ?? 0,
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
    );
  }

  // Helper method to get a color for the priority
  String get priorityLabel {
    switch (priority.toLowerCase()) {
      case 'high':
        return 'High';
      case 'medium':
        return 'Medium';
      case 'low':
        return 'Low';
      default:
        return 'Unknown';
    }
  }

  // Helper method to get a formatted status
  String get statusLabel {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'in-progress':
        return 'In Progress';
      case 'completed':
        return 'Completed';
      default:
        return 'Unknown';
    }
  }
}
