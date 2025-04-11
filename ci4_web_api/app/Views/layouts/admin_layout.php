<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Task Tracker Admin' ?></title>
    
    <!-- Add basic CSS reset and styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            color: #212529;
            background-color: #f8f9fa;
        }
    </style>
    
    <!-- Font Awesome CDN for icons (you can replace with local files in production) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?= $this->include('components/sidebar') ?>
    
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Page Header -->
        <header class="content-header">
            <div class="container">
                <h1><?= $header ?? $title ?? 'Task Tracker' ?></h1>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="content-container">
            <div class="container">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>
    
    <style>
        .container {
            padding: 0 15px;
        }
        
        .content-header {
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</body>
</html>