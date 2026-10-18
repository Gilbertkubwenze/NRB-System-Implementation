<?php
require_once 'db_connect.php';

$message = '';

if (isset($_GET['delete_id'])) {
    $deleteID = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM PERSON WHERE PersonID = ?");
        $stmt->execute([$deleteID]);
        $message = "<div class='alert alert-success'>Person record deleted successfully.</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "<div class='alert alert-danger'>Error: Cannot delete this person because they have an existing Application or ID Card record. (Foreign Key Constraint Enforced).</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

$searchTerm = '';
$results = [];

if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $sql = "SELECT p.PersonID, p.Surname, p.OtherNames, p.PersonType, p.Nationality,
            COALESCE(c.IdentificationNo, r.ResidencePermitNo, al.PassportNumber, rf.RefugeeNumber) AS DocumentNumber
            FROM PERSON p
            LEFT JOIN CITIZEN c ON p.PersonID = c.PersonID
            LEFT JOIN RESIDENT r ON p.PersonID = r.PersonID
            LEFT JOIN ALIEN al ON p.PersonID = al.PersonID
            LEFT JOIN REFUGEE rf ON p.PersonID = rf.PersonID
            WHERE p.Surname LIKE ? OR p.OtherNames LIKE ? OR c.IdentificationNo LIKE ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"]);
    $results = $stmt->fetchAll();
} else {
    $sql = "SELECT p.PersonID, p.Surname, p.OtherNames, p.PersonType, p.Nationality,
            COALESCE(c.IdentificationNo, r.ResidencePermitNo, al.PassportNumber, rf.RefugeeNumber) AS DocumentNumber
            FROM PERSON p
            LEFT JOIN CITIZEN c ON p.PersonID = c.PersonID
            LEFT JOIN RESIDENT r ON p.PersonID = r.PersonID
            LEFT JOIN ALIEN al ON p.PersonID = al.PersonID
            LEFT JOIN REFUGEE rf ON p.PersonID = rf.PersonID
            ORDER BY p.Surname";
    $results = $pdo->query($sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Applicants - NRB</title>
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
        <h2>Search Applicants</h2>
        
        <?= $message ?>

        <form method="GET" action="" style="margin-bottom: 30px; display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search by Name or ID Number..." value="<?= htmlspecialchars($searchTerm) ?>" style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px;">
            <button type="submit" class="btn">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Person ID</th>
                    <th>Surname</th>
                    <th>Other Names</th>
                    <th>Category</th>
                    <th>Nationality</th>
                    <th>Document Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($results) > 0): ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['PersonID']) ?></td>
                            <td><?= htmlspecialchars($row['Surname']) ?></td>
                            <td><?= htmlspecialchars($row['OtherNames']) ?></td>
                            <td><?= htmlspecialchars($row['PersonType']) ?></td>
                            <td><?= htmlspecialchars($row['Nationality']) ?></td>
                            <td><?= htmlspecialchars($row['DocumentNumber']) ?></td>
                            <td>
                                <a href="search.php?delete_id=<?= $row['PersonID'] ?>" class="btn btn-danger" style="padding: 8px 12px; font-size: 14px;" onclick="return confirm('Are you sure you want to attempt to delete this record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">No applicants found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>