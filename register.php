<?php
require_once 'db_connect.php';
$message = '';

if (isset($_POST['add_address'])) {
    $village = trim($_POST['new_village']);
    $area = trim($_POST['new_area']);
    
    if (!empty($village) && !empty($area)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ADDRESS (Village, Area) VALUES (?, ?)");
            $stmt->execute([$village, $area]);
            $message = "<div class='alert alert-success'>New Address added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error adding address: " . $e->getMessage() . "</div>";
        }
    }
}

$addresses = $pdo->query("SELECT AddressID, Village, Area FROM ADDRESS")->fetchAll();

$officer = $pdo->query("SELECT OfficerID FROM OFFICER LIMIT 1")->fetch();
$office = $pdo->query("SELECT OfficeID FROM REGISTRATION_OFFICE LIMIT 1")->fetch();
$defaultOfficerID = $officer ? $officer['OfficerID'] : 1;
$defaultOfficeID = $office ? $office['OfficeID'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_applicant'])) {
    $surname = trim($_POST['surname']);
    $otherNames = trim($_POST['otherNames']);
    $sex = $_POST['sex'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $nationality = trim($_POST['nationality']);
    $personType = $_POST['personType'];
    $addressID = !empty($_POST['addressID']) ? $_POST['addressID'] : null;
    $documentNo = trim($_POST['documentNo']);
    $guardianDoc = trim($_POST['guardianDoc']);

    if (empty($surname) || empty($otherNames) || empty($documentNo)) {
        $message = "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO PERSON (Surname, OtherNames, Sex, DateOfBirth, Gender, Nationality, PersonType, AddressID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$surname, $otherNames, $sex, $dob, $gender, $nationality, $personType, $addressID]);
            $personID = $pdo->lastInsertId();

            if ($personType === 'CITIZEN') {
                $stmt2 = $pdo->prepare("INSERT INTO CITIZEN (PersonID, IdentificationNo) VALUES (?, ?)");
                $stmt2->execute([$personID, $documentNo]);
            } elseif ($personType === 'RESIDENT') {
                $stmt2 = $pdo->prepare("INSERT INTO RESIDENT (PersonID, ResidencePermitNo) VALUES (?, ?)");
                $stmt2->execute([$personID, $documentNo]);
            } elseif ($personType === 'ALIEN') {
                $stmt2 = $pdo->prepare("INSERT INTO ALIEN (PersonID, PassportNumber) VALUES (?, ?)");
                $stmt2->execute([$personID, $documentNo]);
            } elseif ($personType === 'REFUGEE') {
                $stmt2 = $pdo->prepare("INSERT INTO REFUGEE (PersonID, RefugeeNumber) VALUES (?, ?)");
                $stmt2->execute([$personID, $documentNo]);
            }

            $stmtApp = $pdo->prepare("INSERT INTO APPLICATION (ApplicationDate, ApplicationStatus, ApplicationType, PersonID, OfficerID, OfficeID) VALUES (?, 'Pending', 'New', ?, ?, ?)");
            $stmtApp->execute([date('Y-m-d'), $personID, $defaultOfficerID, $defaultOfficeID]);

            if (!empty($guardianDoc)) {
                $guardianSql = "SELECT PersonID, Surname FROM PERSON p 
                                JOIN CITIZEN c ON p.PersonID = c.PersonID 
                                WHERE c.IdentificationNo = ?";
                $stmtG = $pdo->prepare($guardianSql);
                $stmtG->execute([$guardianDoc]);
                $guardian = $stmtG->fetch();
                
                if ($guardian) {
                    $stmtGuardian = $pdo->prepare("INSERT INTO GUARDIAN (PersonID, GuardianSeqNo, GuardianName, Relationship, ContactNumber) VALUES (?, 1, ?, 'Parent/Guardian', 'N/A')");
                    $stmtGuardian->execute([$personID, $guardian['Surname']]);
                }
            }

            $pdo->commit();
            $message = "<div class='alert alert-success'>Applicant successfully registered! Person ID: $personID</div>";

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-danger'>Error: A person with this document number already exists. Please use a unique number for the child.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Applicant - NRB</title>
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
        <h2>Register New Applicant</h2>
        <?= $message ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Surname *</label>
                <input type="text" name="surname" required>
            </div>
            <div class="form-group">
                <label>Other Names *</label>
                <input type="text" name="otherNames" required>
            </div>
            <div class="form-group">
                <label>Sex *</label>
                <select name="sex" required>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth *</label>
                <input type="date" name="dob" required>
            </div>
            <div class="form-group">
                <label>Gender Identity *</label>
                <input type="text" name="gender" value="Male" required>
            </div>
            <div class="form-group">
                <label>Nationality *</label>
                <input type="text" name="nationality" required>
            </div>
            <div class="form-group">
                <label>Applicant Category (Person Type) *</label>
                <select name="personType" required>
                    <option value="CITIZEN">Citizen</option>
                    <option value="RESIDENT">Resident</option>
                    <option value="ALIEN">Alien</option>
                    <option value="REFUGEE">Refugee</option>
                </select>
            </div>
            <div class="form-group">
                <label>Document Number (National ID / Permit / Passport) *</label>
                <input type="text" name="documentNo" required placeholder="Enter a NEW unique number for this person">
            </div>
            
            <div class="form-group">
                <label>Guardian's ID Number (Optional - for children)</label>
                <input type="text" name="guardianDoc" placeholder="e.g. MWI-1000001 (Parent's ID)">
            </div>

            <div class="form-group">
                <label>Address</label>
                <select name="addressID" style="margin-bottom: 10px;">
                    <option value="">-- Select Address --</option>
                    <?php foreach ($addresses as $addr): ?>
                        <option value="<?= $addr['AddressID'] ?>">
                            <?= htmlspecialchars($addr['Village']) ?>, <?= htmlspecialchars($addr['Area']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 30px;">
                <p style="margin-top: 0; font-size: 14px; font-weight: bold; color: #555;">Is the address not in the list? Add a new one below:</p>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="new_village" placeholder="Village" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <input type="text" name="new_area" placeholder="Area / District" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <button type="submit" name="add_address" class="btn" style="padding: 10px 15px; font-size: 14px;">Add</button>
                </div>
            </div>

            <button type="submit" name="register_applicant" class="btn btn-success" style="width: 100%; font-size: 18px;">Register Applicant</button>
        </form>
    </div>
</body>
</html>