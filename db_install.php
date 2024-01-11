<?php

// This script is used to create the database and tables for the first time or reinstall them

// Change these to fit your need!
$servername = "<DATABASE_SERVER>";
$username = "<DB_USER>";
$password = "<PASSWORD>";
$dbname = "<DATABASE_NAME>";

$conn = new PDO("mysql:host=$servername", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = "";

function checkDatabaseExists($conn, $dbname) {
    try {
        $result = $conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        return $result->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false; // If there's an error, assume the database does not exist
    }
}

function installDatabase($conn, $dbname, $sqlFilePath) {
    if (checkDatabaseExists($conn, $dbname)) {
        return "The database '$dbname' already exists.";
    } else {
        try {
            $conn->exec("CREATE DATABASE `$dbname`");
            $conn->exec("USE `$dbname`");
            $sql = file_get_contents($sqlFilePath);
            $conn->exec($sql);
            return "Database and tables created successfully.";
        } catch(PDOException $e) {
            return "Error during installation: " . $e->getMessage();
        }
    }
}

function recreateDatabase($conn, $dbname, $sqlFilePath) {
    try {
        $conn->exec("DROP DATABASE IF EXISTS `$dbname`");
        return installDatabase($conn, $dbname, $sqlFilePath);
    } catch(PDOException $e) {
        return "Error during reinstallation: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['install'])) {
        $message = installDatabase($conn, $dbname, 'setup.sql');
    } elseif (isset($_POST['reinstall'])) {
        $message = recreateDatabase($conn, $dbname, 'setup.sql');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Install Page</title>
    <style>
        .container {
            margin-top: 50px;
            text-align: center;
        }
        button {
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
        }
        .message {
            margin-top: 20px;
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Import Database .sql file</h1>
        <p>Click the appropriate button below to install or reinstall the database and tables.</p>
        <form action="" method="post">
            <button type="submit" name="install">Install Database</button>
            <button type="submit" name="reinstall">Reinstall Database</button>
        </form>
        <?php if (!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
