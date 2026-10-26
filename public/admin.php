<?php
require __DIR__ . '/../config/bd.php';
session_start();

// Защита: пускаем только админов
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die('<h1 style="color:red; text-align:center; margin-top:50px;">Доступ запрещен. Вы не администратор.</h1>');
}

$message = '';

// 1. Обработка добавления нового ПК
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pc'])) {
    $seat = trim($_POST['seat_number']);
    $desc = trim($_POST['description']);
    if ($seat && $desc) {
        try {
            $stmt = $pdo->prepare("INSERT INTO computers (seat_number, description) VALUES (?, ?)");
            $stmt->execute([$seat, $desc]);
            $message = "Компьютер $seat успешно добавлен!";
        } catch (Exception $e) {
            $message = "Ошибка: такой номер места уже существует.";
        }
    }
}

// 2. Обработка смены статуса ПК (доступен / обслуживание)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $pcId = (int)$_POST['pc_id'];
    $newStatus = $_POST['current_status'] === 'available' ? 'maintenance' : 'available';
    $stmt = $pdo->prepare("UPDATE computers SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $pcId]);
    $message = "Статус ПК изменен.";
}

// 3. Обработка отмены брони администратором
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_res_id'])) {
    $cancelId = (int)$_POST['cancel_res_id'];
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$cancelId]);
    $message = "Бронь №{$cancelId} успешно отменена администратором.";
}

// Получаем список всех ПК
$computers = $pdo->query("SELECT * FROM computers ORDER BY seat_number")->fetchAll();

// Получаем все активные брони (с именами юзеров и названиями ПК)
$reservations = $pdo->query("
    SELECT r.*, u.username, c.seat_number 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN computers c ON r.computer_id = c.id
    WHERE r.status = 'active'
    ORDER BY r.start_time DESC
")->fetchAll();

$title = 'Админ-панель';
require __DIR__ . '/../templates/header.php';
?>

<h1>⚙️ Админ-панель</h1>

<?php if ($message): ?>
    <div style="background: #14532d; color: #86efac; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div style="display: flex; gap: 2rem; flex-wrap: wrap;">
    
    <!-- ЛЕВАЯ КОЛОНКА: Управление ПК -->
    <div style="flex: 1; min-width: 300px;">
        <h2 style="border-bottom: 2px solid #334155; padding-bottom: 0.5rem;">Управление ПК</h2>
        
        <!-- Форма добавления -->
        <form method="POST" style="margin-bottom: 2rem; background: #1e293b; padding: 1.2rem; border-radius: 8px; border: 1px solid #334155;">
            <h3 style="margin-bottom: 1rem; color: #38bdf8;">Добавить ПК</h3>
            <div style="margin-bottom: 1rem;">
                <input type="text" name="seat_number" placeholder="Номер (например, PC-07)" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <input type="text" name="description" placeholder="Характеристики" required>
            </div>
            <button type="submit" name="add_pc" class="btn">Добавить</button>
        </form>

        <!-- Список всех ПК -->
        <h3 style="margin-bottom: 1rem;">Список всех ПК</h3>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($computers as $pc): ?>
                <li style="background: #1e293b; border: 1px solid #334155; padding: 0.8rem 1rem; margin-bottom: 0.5rem; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #38bdf8; font-size: 1.1rem;"><?= htmlspecialchars($pc['seat_number']) ?></strong> 
                        <span style="margin-left: 0.5rem; font-size: 0.9rem; color: <?= $pc['status'] === 'available' ? '#86efac' : '#fca5a5' ?>;">
                            (<?= $pc['status'] === 'available' ? 'Доступен' : 'Обслуживание' ?>)
                        </span>
                    </div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="pc_id" value="<?= $pc['id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $pc['status'] ?>">
                        <button type="submit" name="toggle_status" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: <?= $pc['status'] === 'available' ? '#7c2d12' : '#14532d' ?>; color: #fff; border: none;">
                            <?= $pc['status'] === 'available' ? '🔧 На ремонт' : '✅ В строй' ?>
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- ПРАВАЯ КОЛОНКА: Все брони -->
    <div style="flex: 2; min-width: 400px;">
        <h2 style="border-bottom: 2px solid #334155; padding-bottom: 0.5rem;">Все активные брони</h2>
        
        <?php if (empty($reservations)): ?>
            <p style="color: #94a3b8; margin-top: 1rem;">Пока нет активных броней.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; margin-top: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #334155; color: #38bdf8;">
                            <th style="padding: 0.8rem 0.5rem;">ПК</th>
                            <th style="padding: 0.8rem 0.5rem;">Пользователь</th>
                            <th style="padding: 0.8rem 0.5rem;">Время</th>
                            <th style="padding: 0.8rem 0.5rem;">Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr style="border-bottom: 1px solid #334155;">
                                <td style="padding: 0.8rem 0.5rem;"><strong><?= htmlspecialchars($res['seat_number']) ?></strong></td>
                                <td style="padding: 0.8rem 0.5rem;"><?= htmlspecialchars($res['username']) ?></td>
                                <td style="padding: 0.8rem 0.5rem; font-size: 0.9rem; color: #94a3b8;">
                                    <?= date('d.m H:i', strtotime($res['start_time'])) ?> — 
                                    <?= date('d.m H:i', strtotime($res['end_time'])) ?>
                                </td>
                                <td style="padding: 0.8rem 0.5rem;">
                                    <!-- Форма отмены для админа -->
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="cancel_res_id" value="<?= $res['id'] ?>">
                                        <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: #991b1b; color: #fff; border: none;">
                                            Отменить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>