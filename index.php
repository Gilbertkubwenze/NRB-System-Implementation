<?php
require_once 'db_connect.php';

$totalPersons = $pdo->query("SELECT COUNT(*) FROM PERSON")->fetchColumn();
$totalCards = $pdo->query("SELECT COUNT(*) FROM ID_CARD")->fetchColumn();
$pendingApps = $pdo->query("SELECT COUNT(*) FROM APPLICATION WHERE ApplicationStatus = 'Pending'")->fetchColumn();

$pendingSql = "SELECT app.ApplicationID, app.ApplicationDate, p.Surname, p.OtherNames 
               FROM APPLICATION app 
               JOIN PERSON p ON app.PersonID = p.PersonID 
               WHERE app.ApplicationStatus = 'Pending'";
$pendingList = $pdo->query($pendingSql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NRB System Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Dashboard</a>
        <a href="register.php">Register Applicant</a>
        <a href="search.php">Search Applicants</a>
        <a href="manage_cards.php">Manage ID Cards</a>
    </div>

    <div class="container">
        <h2>National Registration Bureau Dashboard</h2>
        
        <div class="stats-container">
            <div class="stat-box bg-blue">
                <h3>Total Applicants</h3>
                <h1><?= $totalPersons ?></h1>
            </div>
            <div class="stat-box bg-green">
                <h3>ID Cards Issued</h3>
                <h1><?= $totalCards ?></h1>
            </div>
            <div class="stat-box bg-orange">
                <h3>Pending Applications</h3>
                <h1><?= $pendingApps ?></h1>
            </div>
        </div>

        <h3>Pending Applications List</h3>
        <table>
            <thead>
                <tr>
                    <th>App ID</th>
                    <th>Date Submitted</th>
                    <th>Applicant Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($pendingList) > 0): ?>
                    <?php foreach ($pendingList as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['ApplicationID']) ?></td>
                            <td><?= htmlspecialchars($row['ApplicationDate']) ?></td>
                            <td><?= htmlspecialchars($row['Surname']) ?>, <?= htmlspecialchars($row['OtherNames']) ?></td>
                            <td style="color: #ea580c; font-weight: bold;">Pending</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No pending applications at the moment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>