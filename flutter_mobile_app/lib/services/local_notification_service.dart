import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class LocalNotificationService {
  final FlutterLocalNotificationsPlugin _flutterLocalNotificationsPlugin = 
      FlutterLocalNotificationsPlugin();
  
  Future<void> initialize() async {
    // App icon must be added as a drawable resource in Android
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');
        
    final InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
    );
    
    await _flutterLocalNotificationsPlugin.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: _onNotificationTap,
    );
    
    // Request permission for Android 13+
    final AndroidFlutterLocalNotificationsPlugin? androidImplementation =
        _flutterLocalNotificationsPlugin.resolvePlatformSpecificImplementation
            <AndroidFlutterLocalNotificationsPlugin>();
    
    if (androidImplementation != null) {
      await androidImplementation.requestNotificationsPermission();
    }
  }

  void _onNotificationTap(NotificationResponse response) {
    // Handle notification tap here - navigate to specific screen
    // or process payload data
    print('Notification tapped: ${response.payload}');
  }
  
  Future<void> showNotification({
    required int id,
    required String title,
    required String message,
    String? type,
    int? taskId,
  }) async {
    // Configure Android notification details
    final AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      'default_channel',
      'Default Notifications',
      channelDescription: 'Channel for app notifications',
      importance: Importance.max,
      priority: Priority.high,
      showWhen: true,
    );

    // Create the notification details
    final NotificationDetails platformDetails = NotificationDetails(
      android: androidDetails,
    );
    
    // Create a JSON payload with the notification data
    String payload = '{"id":$id,"title":"$title","message":"$message"';
    if (taskId != null) payload += ',"task_id":$taskId';
    if (type != null) payload += ',"type":"$type"';
    payload += '}';
    
    // Show the notification
    await _flutterLocalNotificationsPlugin.show(
      id,
      title,
      message,
      platformDetails,
      payload: payload,
    );
  }
}