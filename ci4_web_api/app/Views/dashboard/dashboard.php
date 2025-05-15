<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <h2>Dashboard</h2>

    <div class="dashboard-actions">
        <div class="quick-actions">
            <button id="addTaskBtn" class="action-button add">
                <i class="fas fa-plus"></i> Add New Task
            </button>
            <button id="refreshDashboardBtn" class="refresh-button">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Metrics Container -->
    <div class="metrics-container">
        <!-- Task Status Metrics Card (existing) -->
        <div class="metric-card" id="task-status-card">
            <h3>Task Status Overview</h3>
            <div class="total" id="total-tasks">-</div>
            <p>Total Tasks</p>

            <div class="horizontal-chart" id="tasks-chart">
                <!-- Chart segments will be added dynamically -->
            </div>

            <div class="status-breakdown">
                <div class="status-item status-pending">
                    <div class="status-count" id="pending-count">-</div>
                    <div class="status-label">Pending</div>
                </div>
                <div class="status-item status-in-progress">
                    <div class="status-count" id="in-progress-count">-</div>
                    <div class="status-label">In Progress</div>
                </div>
                <div class="status-item status-completed">
                    <div class="status-count" id="completed-count">-</div>
                    <div class="status-label">Completed</div>
                </div>
                <div class="status-item status-request-extension">
                    <div class="status-count" id="extension-count">-</div>
                    <div class="status-label">Extension</div>
                </div>
            </div>
        </div>
        
        <!-- Task Priority Distribution Card -->
        <div class="metric-card" id="task-priority-card">
            <h3>Task Priority Distribution</h3>
            
            <!-- Donut chart for priority distribution -->
            <div class="priority-donut-chart" id="priority-donut-chart">
                <!-- Donut segments will be added dynamically -->
                <div class="donut-hole">
                    <div class="donut-text-total" id="priority-chart-total">-</div>
                    <div class="donut-text-label">Tasks</div>
                </div>
            </div>
            
            <!-- Priority Legend -->
            <div class="priority-chart-legend">
                <div class="legend-item">
                    <div class="legend-color priority-high"></div>
                    <span>High</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color priority-medium"></div>
                    <span>Medium</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color priority-low"></div>
                    <span>Low</span>
                </div>
            </div>
            
            <!-- Priority Counts -->
            <div class="status-breakdown">
                <div class="status-item">
                    <div class="status-count" id="high-priority-count" style="color: #DC3545;">-</div>
                    <div class="status-label">High</div>
                </div>
                <div class="status-item">
                    <div class="status-count" id="medium-priority-count" style="color: #FFC107;">-</div>
                    <div class="status-label">Medium</div>
                </div>
                <div class="status-item">
                    <div class="status-count" id="low-priority-count" style="color: #28A745;">-</div>
                    <div class="status-label">Low</div>
                </div>
            </div>
        </div>
        <!-- Overdue Tasks -->
        <div class="metric-card" id="overdue-tasks-card">
            <h3>Overdue Tasks</h3>
            <div class="total" id="overdue-count">-</div>
            <p>Tasks Past Due Date</p>

            <div class="overdue-tasks-list" id="overdue-tasks-list">
                <div class="loading-spinner">Loading overdue tasks...</div>
            </div>

            <!-- Link to view all overdue tasks -->
            <div class="view-all-link" id="view-all-overdue" style="display: none;">
                <a href="/task?status=overdue">View All Overdue Tasks</a>
            </div>
        </div>
        
        <!-- Upcoming Tasks (Due in Next 7 Days) -->
        <div class="metric-card" id="upcoming-tasks-card">
            <h3>Tasks Due in Next 7 Days</h3>
            <div class="total" id="upcoming-count">-</div>
            <p>Tasks Due Soon</p>

            <div class="upcoming-tasks-list" id="upcoming-tasks-list">
                <div class="loading-spinner">Loading upcoming tasks...</div>
            </div>

            <div class="view-all-link" id="view-all-upcoming" style="display: none;">
                <a href="/task?due=upcoming">View All Upcoming Tasks</a>
            </div>
        </div>

        <!-- Team Completion Rates Comparison Card -->
        <div class="metric-card" id="team-completion-card">
            <h3>Team Completion Rates</h3>
            <div class="total-wrapper">
                <div class="total" id="teams-count">-</div>
                <p>Teams</p>
            </div>

            <div class="team-metrics-container" id="team-metrics-container">
                <div class="loading-spinner">Loading team metrics...</div>
            </div>

            <div class="view-all-link">
                <a href="/team">View All Teams</a>
            </div>
        </div>

    </div>
</div>

<!-- Include Task Modal for quick task creation -->
<?= $this->include('task/modals/create') ?>

<script>
    <?= $this->include('dashboard/js/dashboard.js') ?>
    <?= $this->include('dashboard/js/dashboard-metrics.js') ?>
</script>
<?= $this->endSection() ?>