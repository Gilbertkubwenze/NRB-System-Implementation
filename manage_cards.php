<?php
require_once 'db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_card'])) {
    $appID = $_POST['applicationID'];
    $cardNo = trim($_POST['cardNumber']);
    $issueDate = $_POST['issueDate'];
    $expiryDate = date('Y-m-d', strtotime($issueDate . ' +10 years'));

    try {
        $stmt = $pdo->prepare("INSERT INTO ID_CARD (IdentificationNo, DateOfIssue, DateOfExpiry, CardStatus, ApplicationID) VALUES (?, ?, ?, 'Active', ?)");
        $stmt->execute([$cardNo, $issueDate, $expiryDate, $appID]);
        
        $pdo->prepare("UPDATE APPLICATION SET ApplicationStatus = 'Approved' WHERE ApplicationID = ?")->execute([$appID]);
        
        $message = "<div class='alert alert-success'>ID Card successfully issued!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

$sql = "SELECT app.ApplicationID, p.Surname, p.OtherNames, p.PersonID 
        FROM APPLICATION app 
        JOIN PERSON p ON app.PersonID = p.PersonID 
        LEFT JOIN ID_CARD ic ON app.ApplicationID = ic.ApplicationID
        WHERE (app.ApplicationStatus = 'Approved' OR app.ApplicationStatus = 'Pending') 
        AND ic.CardID IS NULL";
$pendingCards = $pdo->query($sql)->fetchAll();

$allCards = $pdo->query("SELECT IdentificationNo FROM ID_CARD WHERE IdentificationNo LIKE 'MWI-%'")->fetchAll();
$max = 1000000; 
foreach ($allCards as $c) {
    $num = (int)str_replace('MWI-', '', $c['IdentificationNo']);
    if ($num > $max) { $max = $num; }
}
$nextCardNo = "MWI-" . ($max + 1);
$todayDate = date('Y-m-d');

$issuedCardsSql = "SELECT ic.CardID, ic.IdentificationNo, ic.DateOfIssue, ic.CardStatus, p.Surname, p.OtherNames 
                   FROM ID_CARD ic 
                   JOIN APPLICATION app ON ic.ApplicationID = app.ApplicationID
                   JOIN PERSON p ON app.PersonID = p.PersonID";
$issuedCards = $pdo->query($issuedCardsSql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage ID Cards - NRB</title>
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
        <h2>Manage ID Card Issuance</h2>
        <?= $message ?>

        <form method="POST" action="" id="issueForm">
            <div class="form-group">
                <label>Select Applicant to Issue Card</label>
                <select name="applicationID" id="appSelect" required onchange="updateFields()">
                    <option value="">-- Select Applicant --</option>
                    <?php foreach ($pendingCards as $pc): ?>
                        <option value="<?= $pc['ApplicationID'] ?>" data-card="<?= htmlspecialchars($nextCardNo) ?>" data-date="<?= htmlspecialchars($todayDate) ?>">
                            App #<?= $pc['ApplicationID'] ?> - <?= htmlspecialchars($pc['Surname']) ?>, <?= htmlspecialchars($pc['OtherNames']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>ID Card Number</label>
                <input type="text" name="cardNumber" id="cardNo" required>
            </div>

            <div class="form-group">
                <label>Date of Issue</label>
                <input type="text" name="issueDate" id="issueDate" required>
            </div>

            <button type="submit" name="issue_card" class="btn btn-success">Issue Card</button>
        </form>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">

        <?php if (isset($_GET['show_cards'])): ?>
            <a href="manage_cards.php" class="btn btn-secondary" style="margin-bottom: 20px;">Hide Issued ID Cards</a>
            <h3>Issued ID Cards List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Card ID</th>
                        <th>ID Number</th>
                        <th>Applicant Name</th>
                        <th>Date of Issue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($issuedCards as $card): ?>
                        <tr>
                            <td><?= htmlspecialchars($card['CardID']) ?></td>
                            <td><?= htmlspecialchars($card['IdentificationNo']) ?></td>
                            <td><?= htmlspecialchars($card['Surname']) ?>, <?= htmlspecialchars($card['OtherNames']) ?></td>
                            <td><?= htmlspecialchars($card['DateOfIssue']) ?></td>
                            <td><?= htmlspecialchars($card['CardStatus']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <a href="manage_cards.php?show_cards=1" class="btn btn-secondary">Display Issued ID Cards</a>
        <?php endif; ?>
    </div>

    <script>
        function updateFields() {
            var select = document.getElementById("appSelect");
            var selectedOption = select.options[select.selectedIndex];
            if (selectedOption.value) {
                document.getElementById("cardNo").value = selectedOption.getAttribute("data-card");
                document.getElementById("issueDate").value = selectedOption.getAttribute("data-date");
            } else {
                document.getElementById("cardNo").value = "";
                document.getElementById("issueDate").value = "";
            }
        }
    </script>
</body>
</html>