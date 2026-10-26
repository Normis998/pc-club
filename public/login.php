<?php
require __DIR__ . '/../config/bd.php';
require __DIR__ . '/../src/auth.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (loginUser($pdo, trim($_POST['username']), $_POST['password'])) {
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный логин или пароль.';
}
$title = 'Вход';
require __DIR__ . '/../templates/header.php';
?>
<h1>Вход в систему</h1>
<?php if ($error): ?><p style="color:#fca5a5; margin-bottom:1rem;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST" style="max-width: 400px;">
    <div style="margin-bottom: 1rem;"><label>Логин:</label><input type="text" name="username" required></div>
    <div style="margin-bottom: 1.5rem;"><label>Пароль:</label><input type="password" name="password" required></div>
    <button type="submit" class="btn">Войти</button>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>