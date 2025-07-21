<?php
session_start();

// Ensure user is a logged-in admin or manager
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || (!isset($_SESSION["is_admin"]) && !isset($_SESSION["is_manager"]))) {
    header("location: login.php");
    exit;
}

require_once "config.php";
$link = get_db_connection();

// Fetch chart data
$chart_data = [];
$sql_chart = "
    SELECT
        DATE_FORMAT(issue_date, '%Y-%m') AS issue_month,
        COUNT(*) as count
    FROM (
        SELECT issue_date FROM zoning_certificates
        UNION ALL
        SELECT issue_date FROM locational_clearances
    ) AS all_issuances
    WHERE issue_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY issue_month
    ORDER BY issue_month ASC;
";

if($result_chart = mysqli_query($link, $sql_chart)) {
    while($row_chart = mysqli_fetch_assoc($result_chart)) {
        $chart_data[] = $row_chart;
    }
}

// Fetch all non-admin and non-manager users
$users = [];
$sql_users = "SELECT id, username FROM users WHERE is_admin = FALSE AND is_manager = FALSE ORDER BY username ASC";
if($result_users = mysqli_query($link, $sql_users)){
    if(mysqli_num_rows($result_users) > 0){
        while($row = mysqli_fetch_assoc($result_users)){
            $users[] = $row;
        }
        mysqli_free_result($result_users);
    }
}

mysqli_close($link);

// Prepare data for JavaScript
$chart_labels = json_encode(array_column($chart_data, 'issue_month'));
$chart_values = json_encode(array_column($chart_data, 'count'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .dashboard-card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-card h3 {
            margin-top: 0;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        #chart-card {
            position: relative;
            height: 40vh; /* vh is viewport height, adjust as needed */
            max-height: 350px; /* Set a max height */
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'navigation.php'; ?>

        <div class="page-header" style="margin-top: 20px;">
            <h1>Manager Dashboard</h1>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card" id="search-card">
                <h3>Search Records</h3>
                <form action="search_results.php" method="get">
                    <div class="form-group">
                        <input type="text" name="query" class="form-control" placeholder="Search by name, classification, zone, or location..." required>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Search">
                    </div>
                </form>
            </div>
            <div class="dashboard-card" id="chart-card">
                <h3>Monthly Issuance Overview (Last 12 Months)</h3>
                <canvas id="issuanceChart"></canvas>
            </div>
        </div>

        <div class="page-header" style="margin-top: 20px;">
            <h1>User Management</h1>
        </div>

        <h2>Manage Existing Users</h2>

        <h3>Add New User</h3>
        <form action="add_user.php" method="post" class="wrapper" style="width:auto; margin-bottom: 20px; background-color: #f9f9f9; padding: 15px;">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add User">
            </div>
        </form>

        <h3>Existing Users</h3>
        <?php if(!empty($users)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td class="action-links">
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>">Edit</a>
                        <a href="#" onclick="confirmDelete(<?php echo $user['id']; ?>); return false;" class="delete">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No users found.</p>
        <?php endif; ?>

        <div class="page-header" style="margin-top: 20px;">
            <h1>Clearance Management</h1>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Manage Fishing Permits</h3>
                <a href="manage_fishing_permits.php" class="btn btn-primary">Manage</a>
            </div>
            <div class="dashboard-card">
                <h3>Manage Locational Clearances</h3>
                <a href="manage_locational.php" class="btn btn-primary">Manage</a>
            </div>
            <div class="dashboard-card">
                <h3>Manage Zoning Certificates</h3>
                <a href="manage_zoning.php" class="btn btn-primary">Manage</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('issuanceChart');
        const chartLabels = <?php echo $chart_labels; ?>;
        const chartValues = <?php echo $chart_values; ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: '# of Issuances',
                    data: chartValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Ensure y-axis increments by whole numbers
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });

        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = 'delete_user.php?id=' + userId;
            }
        }
    </script>
</body>
</html>
