import 'dart:async';
import 'package:flutter/material.dart';
import 'package:socket_io_client/socket_io_client.dart' as IO;

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();
  
  IO.Socket? socket;
  final StreamController<Map<String, dynamic>> _notificationController = 
      StreamController<Map<String, dynamic>>.broadcast();
  
  Stream<Map<String, dynamic>> get notifications => _notificationController.stream;
  
  void initialize(String userId) {
    // Initialize socket connection
    socket = IO.io('http://your-server-ip:3000', <String, dynamic>{
      'transports': ['websocket'],
      'autoConnect': true,
    });
    
    // Connect to socket server
    socket!.connect();
    
    // Socket event listeners
    socket!.on('connect', (_) {
      print('Socket connected');
      // Authenticate with the server using user ID
      socket!.emit('authenticate', userId);
    });
    
    socket!.on('authenticated', (data) {
      print('Authenticated with socket: $data');
    });
    
    socket!.on('new-notification', (data) {
      print('New notification: $data');
      _notificationController.add(Map<String, dynamic>.from(data));
    });
    
    socket!.on('unread-notifications', (data) {
      if (data is List) {
        for (var notification in data) {
          _notificationController.add(Map<String, dynamic>.from(notification));
        }
      }
    });
    
    socket!.on('disconnect', (_) {
      print('Socket disconnected');
    });
    
    socket!.on('connect_error', (error) {
      print('Connection error: $error');
    });
  }
  
  void disconnect() {
    socket?.disconnect();
  }
  
  void dispose() {
    socket?.disconnect();
    socket?.dispose();
    _notificationController.close();
  }
}