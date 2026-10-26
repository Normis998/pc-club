<?php
require __DIR__ . '/../config/bd.php';
require __DIR__ . '/../src/auth.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');

    if (strlen($username) < 3 || strlen($password) < 4) {
        $error = 'Логин от 3 символов, пароль от 4 символов.';
    } elseif (registerUser($pdo, $username, $password, $email)) {
        header('Location: login.php');
        exit;
    } else {
        $error = 'Пользователь с таким логином уже существует.';
    }
}
$title = 'Регистрация';
require __DIR__ . '/../templates/header.php';
?>
<h1>Регистрация</h1>
<?php if ($error): ?><p style="color:#fca5a5; margin-bottom:1rem;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST" style="max-width: 400px;">
    <div style="margin-bottom: 1rem;"><label>Логин:</label><input type="text" name="username" required></div>
    <div style="margin-bottom: 1rem;"><label>Email:</label><input type="email" name="email"></div>
    <div style="margin-bottom: 1.5rem;"><label>Пароль:</label><input type="password" name="password" required></div>
    <button type="submit" class="btn">Создать аккаунт</button>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>