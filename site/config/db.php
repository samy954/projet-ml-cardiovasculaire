<?php

declare(strict_types=1);

/*
 * Exemple de configuration MySQL.
 * Renommer ce fichier en db.php et compléter les valeurs.
 */

const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'cardiopredict';
const DB_USER = 'root';
const DB_PASSWORD = '';
const DB_CHARSET = 'utf8mb4';

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);

    return $pdo;
}

function getOptionalDatabaseConnection(): ?PDO
{
    try {
        return getDatabaseConnection();
    } catch (Throwable $exception) {
        error_log('Connexion base de données indisponible : ' . $exception->getMessage());
        return null;
    }
}