import 'dart:async';
import 'package:flutter/material.dart';
import 'package:socket_io_client/socket_io_client.dart' as IO;
import 'package:flutter_dotenv/flutter_dotenv.dart';

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();

  // Factory constructor
  factory NotificationService() {
    return _instance;
  }

  NotificationService._internal();

  // Socket instance
  IO.Socket? _socket;

  // Stream controller for notifications
  final _notificationStreamController = StreamController<Map<String, dynamic>>.broadcast();

  // Stream getter
  Stream<Map<String, dynamic>> get notificationStream => _notificationStreamController.stream;

  // Connection status
  bool get isConnected => _socket?.connected ?? false;

  // Initialize Socket.IO connection
  void initSocket(int userId) {
    // Dont initialize if userId is invalid
    if (userId <= 0) {
      debugPrint('Socket.IO: Invalid user ID, not connecting');
      return;
    }
    // Close existing connection
    closeSocket();

    // Get notification server URL
    final serverUrl = dotenv.env['NOTIFICATION_SERVER_URL'];

    try {
      // Configure Socket
      _socket = IO.io(
        serverUrl,
        IO.OptionBuilder()
          .setTransports(['websocket'])
          .disableAutoConnect()
          .enableForceNew()
          .build()
      );
      // Connect to socket
      _socket?.connect();
      // Setup event listener
      _setupSocketListeners(userId);

      debugPrint('Socket.IO: Attempting to connect to $serverUrl');
    } catch (e) {
      debugPrint('Socket.IO: Connection error: ${e.toString()}');
    }
  }
  // Setup event listners
  void _setupSocketListeners(int userId) {
    _socket?.onConnect((_){
      debugPrint('Socket.IO: Connected');
      //Authenticate with user ID
      _authenticateUser(userId);
    });

    _socket?.onDisconnect((_){
      debugPrint('Socket.IO: Disconnected');
    });

    _socket?.onConnectError((error){
      debugPrint('Socket.IO: Connection error: $error');
    });

    _socket?.onError((error){
      debugPrint('Socket.IO: Error: $error');
    });

    // Listen for notification events
    _socket?.on('new-notification', (data){
      debugPrint('Socket.IO: New notification received: $data');
      _notificationStreamController.add(data);

      // Send acknowledgement to server
      _socket?.emit('notification-received', {'notificationId': data['id']});
    });

    // Listen for unread notifications
    _socket?.on('unread-notifications', (data){
      debugPrint('Socket.IO: Unread notifications received: $data');

      // Process each notifications
      if (data is List) {
        for (var notification in data) {
          _notificationStreamController.add(notification);
        }
      }
    });
  }

  // Authenticate user with notification server
  void _authenticateUser(int userId) {
    debugPrint('Socket.IO: Authenticating user $userId');
    _socket?.emit('authenticate', userId);

    // Listen for authentication response
    _socket?.once('authenticated', (data){
      debugPrint('Socket.IO: Authentication successful: $data');
    });

    // Listen for authentication errors
    _socket?.once('error', (data){
      debugPrint('Socket.IO: Authentication error: $data');
    });
  }

  // Close socket connection
  void closeSocket() {
    if (_socket != null && _socket!.connected) {
      _socket?.disconnect();
      _socket?.destroy();
      _socket = null;
      debugPrint('Socket.IO: Connection closed');
    }
  }

  // Dispose resources
  void dispose() {
    closeSocket();
    _notificationStreamController.close();
  }

  // Mark notification as read
  Future<void> markNotificationAsRead(int notificationId) async {
    _socket?.emit('notification-read', {'notificationId': notificationId});
  }
}