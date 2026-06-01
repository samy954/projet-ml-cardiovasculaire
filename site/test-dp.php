<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'db5020539163.hosting-data.io';
$port = '3306';
$dbname = 'dbs15715600';
$user = 'dbu352205';
$password = 'Projet.18082018!';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "Connexion SQL réussie.";
} catch (PDOException $e) {
    echo "Erreur SQL : " . $e->getMessage();
}