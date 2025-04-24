# TaskTracker

TaskTracker is a comprehensive task management system designed to streamline workflow management between managers and team members. The system consists of a web application for managers and a mobile application for team members (currently in development).

## Overview

TaskTracker aims to bridge the gap between management and team execution by providing specialized interfaces for different user roles. The system centralizes task assignment, monitoring, and reporting in a single cohesive platform.

## Components

### Web Application (Manager Interface) - 🚧 In Development

The web application provides managers with tools to:

- **Task Assignment**: Create and assign tasks to team members with details, priorities, and deadlines
- **Team Management**: Monitor workload distribution and team performance
- **Task Administration**: Update, track, and manage task statuses
- **Priority Setting**: Define importance levels for tasks to help team members prioritize their work
- **Performance Monitoring**: View completion rates and productivity metrics

### Mobile Application (Team Member Interface) - 🚧 In Development

The mobile application will provide team members with:

- Push notifications for new task assignments
- Task viewing capabilities
- Progress tracking with visual indicators
- Status update functionality (in-progress, completed, request extension)

### Notification Server - 🚧 In Development

A real-time notification system to alert team members of new tasks and updates.

## Technical Implementation

The system is built using:

- **Backend**: CodeIgniter 4 PHP framework
- **Web Frontend**: HTML, CSS, JavaScript
- **Mobile App**: Flutter
- **Database**: MySQL
- **Real-time Communication**: Socket.IO

## Project Status

- **Web Application**: Under development
- **Mobile Application**: Under development
- **Notification Server**: Under development

## Setup Instructions

1. Clone the repository
2. Configure your database settings in `ci4_web_api/app/Config/Database.php`
3. Run database migrations
4. Launch the web application

## Future Development

- Complete web application implementation
- Complete mobile application implementation
- Implement notification server for real-time updates
- Add reporting and analytics features
- Integrate calendar views for deadline management

---

© 2024 TaskTracker. All rights reserved.
