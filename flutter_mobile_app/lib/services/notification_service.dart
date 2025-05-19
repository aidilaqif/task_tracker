import 'dart:async';
import 'package:flutter/material.dart';
import 'package:socket_io_client/socket_io_client.dart' as IO;
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'local_notification_services.dart';

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  final LocalNotificationService _localNotificationService = LocalNotificationService();

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

  // Initialize both socket and local notifications
  Future<void> init(int userId) async {
    // Initialize local notifications
    await _localNotificationService.initialize();
    
    // Initialize socket connection
    _initSocket(userId);
  }

  // Public method to initialize Socket.IO connection
  void initSocket(int userId) => _initSocket(userId);

  // Initialize Socket.IO connection (private)
  void _initSocket(int userId) {
    // Dont initialize if userId is invalid
    if (userId <= 0) {
      debugPrint('Socket.IO: Invalid user ID, not connecting');
      return;
    }
    // Close existing connection
    closeSocket();

    // Get notification server URL
    final serverUrl = dotenv.env['NOTIFICATION_SERVER_URL'];

    debugPrint('Socket.IO: Attempting to connect to $serverUrl with user ID $userId');

    try {
      // Configure Socket
      _socket = IO.io(
        serverUrl,
        IO.OptionBuilder()
          .setTransports(['websocket', 'polling'])
          .disableAutoConnect()
          .enableForceNew()
          .enableReconnection()
          .setReconnectionAttempts(10)
          .setReconnectionDelay(1000)
          .setReconnectionDelayMax(5000)
          .setQuery({'userId': userId.toString()})
          .build()
      );

      // Setup event listeners
      _setupSocketListeners(userId);

      // Connect to socket
      _socket?.connect();

      debugPrint('Socket.IO: Connection initiated to $serverUrl');
    } catch (e) {
      debugPrint('Socket.IO: Connection error: ${e.toString()}');
    }
  }
  // Setup event listners
  void _setupSocketListeners(int userId) {
    _socket?.onConnect((_) {
      debugPrint('Socket.IO: Connected successfully');
      //Authenticate with user ID
      _authenticateUser(userId);
    });

    _socket?.onConnectError((error) {
      debugPrint('Socket.IO: Connection error: $error');
    });

    _socket?.onDisconnect((_) {
      debugPrint('Socket.IO: Disconnected');
      // Try to reconnect
      Future.delayed(Duration(seconds: 3), () {
        if (_socket != null && !_socket!.connected) {
          debugPrint('Socket.IO: Attempting to reconnect...');
          _socket?.connect();
        }
      });
    });

    _socket?.onError((error) {
      debugPrint('Socket.IO: Error: $error');
    });

    _socket?.onReconnect((_) {
      debugPrint('Socket.IO: Reconnected');
      // Re-authenticate after reconnection
      _authenticateUser(userId);
    });

    _socket?.onReconnectAttempt((attemptNumber) {
      debugPrint('Socket.IO: Reconnection attempt $attemptNumber');
    });

    _socket?.onReconnectError((error) {
      debugPrint('Socket.IO: Reconnection error: $error');
    });

    _socket?.onReconnectFailed((_) {
      debugPrint('Socket.IO: Reconnection failed');
    });

    // Listen for notification events
    _socket?.on('new-notification', (data) {
      debugPrint('Socket.IO: New notification received: $data');
      if (data != null) {
        _notificationStreamController.add(data);

        // Show local notification
        _localNotificationService.showNotification(
          id: data['id'] ?? DateTime.now().millisecondsSinceEpoch,
          title: data['title'] ?? 'New Notification',
          message: data['message'] ?? '',
          type: data['type'],
          taskId: data['task_id'],
        );

        // Send acknowledgement to server
        _socket?.emit('notification-received', {'notificationId': data['id']});
      }
    });

    // Listen for unread notifications
    _socket?.on('unread-notifications', (data) {
      debugPrint('Socket.IO: Unread notifications received: $data');

      // Process each notification
      if (data is List) {
        for (var notification in data) {
          if (notification != null) {
            _notificationStreamController.add(notification);
          }
        }
      }
    });
    
    // Listen for authentication response
    _socket?.once('authenticated', (data) {
      debugPrint('Socket.IO: Authentication successful: $data');
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