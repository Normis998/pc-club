<?php
declare(strict_types=1); // ЭТО ДОЛЖНО БЫТЬ САМОЙ ПЕРВОЙ СТРОКОЙ!

// Теперь уже идут настройки отладки
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$host   = '127.0.0.1';
$dbname = 'pc_club';
$dbuser = 'root';
$dbpass = '12345'; // Тот самый пароль, который вы задали в консоли

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}