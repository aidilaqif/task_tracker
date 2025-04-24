<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Task Tracker' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <!-- Base CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">

    <!-- Ensure sidebar CSS is always loaded -->
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar.css') ?>">

    <!-- Component-specific CSS -->
    <?php if(isset($css_files) && is_array($css_files)): ?>
        <?php foreach($css_files as $css): ?>
            <link rel="stylesheet" href="<?= base_url('assets/css/components/' . $css . '.css') ?>">
        <?php endforeach; ?>
    <?php endif; ?>
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