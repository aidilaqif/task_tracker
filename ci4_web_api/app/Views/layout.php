<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Task Tracker' ?></title>
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

        .container {
            padding: 0 15px;
        }

        .content-header {
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            background-color: #fff;
            border: 1px solid #dee2e6;
        }

        table th,
        table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        table tr:nth-child(even) {
            background-color: #f8f9a;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        /* Button style */
        button {
            /* background-color: #007bff; */
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }

        button:hover {
            /* background-color: #0069d9; */
        }

        button.view {
            background-color: #107ed9;
        }

        button.view:hover {
            background-color: #155387;
        }

        button.edit {
            background-color: #bfa900;
        }

        button.edit:hover {
            background-color: #826d01;
        }

        button.remove {
            background-color: #f03c3c;
        }

        button.remove:hover {
            background-color: #942e2e;
        }

        /* Status and priority styles */
        .status-pending {
            color: #856404;
            background-color: #fff3cd;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .status-in-progress {
            color: #0c5460;
            background-color: #d1ecf1;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }

        .status-completed {
            color: #155724;
            background-color: #d4edda;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }

        .status-request-extension {
            color: #721c24;
            background-color: #f8d7da;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }

        .priority-high {
            color: #721c24;
            font-weight: 600;
        }

        .priority-medium {
            color: #856404;
        }

        .priority-low {
            color: #155724;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/modals.css') ?>">
</head>
<body>
    <!-- Include Sidebar -->
    <?= $this->include('sidebar') ?>

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
</body>
</html>