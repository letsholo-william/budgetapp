<?php
require_once 'config.php';

function db_connect() {
    global $db_host, $db_name, $db_user, $db_pass;

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
        exit;
    } catch (Exception $e) {
        echo "Application Error: " . $e->getMessage();
        exit;
    }
}
