<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'PC Club') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a href="index.php" class="logo">🎮 PC Club</a>
    <nav>
        <a href="index.php">Главная</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="booking.php">Забронировать</a>
            <a href="my-reservations.php">Мои брони</a>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="admin.php" style="color: #fca5a5; font-weight: bold;">⚙️ Админ-панель</a>
            <?php endif; ?>
            <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>
<main>