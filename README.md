# National ID Registration System (NRB)

## Overview
A web-based application built with PHP and MySQL to manage National ID registrations, applicant searches, and ID card issuance for the National Registration Bureau.

## Prerequisites
- XAMPP installed.
- Web browser (Chrome).
- SQL workbench

## Installation & Setup

1. Start XAMPP: Open the XAMPP Control Panel and start both Apache and MySQL.
2. Import the Database:
   - Open your browser and go to `http://localhost/phpmyadmin`
   - Click on the Import tab.
   - Choose the `nrb_database.sql` file included in this folder and click Import. This will create the `nrb_db` database and all required tables.
3. Place the Files:
   - Copy the `nrb_system` folder into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\nrb_system`).
4. Configuration:
   - The database connection uses the default XAMPP credentials (User: `root`, Password: blank). If your setup requires a password, update the credentials in `db_connect.php`.
5. Run the Application:
   - Open your browser and navigate to: `http://localhost/nrb_system/index.php`

## Features
- Dashboard with real-time statistics.
- Applicant registration with automatic generation of Pending Applications.
- Guardian linking for child registration.
- Search functionality using prepared statements (Prevents SQL Injection).
- Automatic sequential ID Card number generation.
- Constraint enforcement (Duplicate ID prevention and Foreign Key RESTRICT demonstration).

## Security (Task 3 Compliance)
- Uses PDO Prepared Statements to prevent SQL Injection.
- Uses htmlspecialchars() on all outputs to prevent XSS.
- Input validation on required fields.
- Try-catch blocks to handle database duplicate errors gracefully.