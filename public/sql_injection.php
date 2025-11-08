<?php
    require_once "db.php";

    $statement = $connection->prepare("SELECT * FROM user WHERE email = ? AND password = ?");
    $statement->execute(array($_POST["email"], $_POST["password"]));

    if ($statement->fetch()) {
        echo "Logged in successfully";
    } else {
        echo "Invalid credentials.";
    }

    while ($row = $statement->fetch()) {
        // ...
    }
?>