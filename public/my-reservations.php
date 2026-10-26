<?php
require __DIR__ . '/../config/bd.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Обработка отмены брони
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];
    // Отменяем только если это бронь текущего пользователя
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->execute([$cancelId, $_SESSION['user_id']]);
    header('Location: my-reservations.php'); // Перезагружаем страницу
    exit;
}

$stmt = $pdo->prepare("
    SELECT r.*, c.seat_number, c.description 
    FROM reservations r
    JOIN computers c ON r.computer_id = c.id
    WHERE r.user_id = ? AND r.status = 'active'
    ORDER BY r.start_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();

$title = 'Мои брони';
require __DIR__ . '/../templates/header.php';
?>
<h1>Мои брони</h1>
<?php if (empty($reservations)): ?>
    <p style="color:#94a3b8;">У вас пока нет активных броней.</p>
<?php else: ?>
    <div class="pc-grid" style="grid-template-columns: 1fr; max-width: 800px;">
        <?php foreach ($reservations as $res): ?>
            <div class="pc-card" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3><?= htmlspecialchars($res['seat_number']) ?> (<?= htmlspecialchars($res['description']) ?>)</h3>
                    <p>
                        <strong>Начало:</strong> <?= date('d.m.Y H:i', strtotime($res['start_time'])) ?><br>
                        <strong>Конец:</strong> <?= date('d.m.Y H:i', strtotime($res['end_time'])) ?>
                    </p>
                </div>
                <!-- Кнопка отмены -->
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="cancel_id" value="<?= $res['id'] ?>">
                    <button type="submit" class="btn" style="background: #7c2d12; color: white;">Отменить</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>