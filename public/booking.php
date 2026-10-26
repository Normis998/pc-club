<?php
require __DIR__ . '/../config/bd.php';
require __DIR__ . '/../src/booking.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = ''; $error = '';

// Получаем ID компьютера из GET-запроса (если перешли с главной)
$selectedPcId = (int)($_GET['pc_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $computerId = (int)($_POST['computer_id'] ?? 0);
    $start = $_POST['start_time'] ?? '';
    $duration = (int)($_POST['duration'] ?? 0);

    if ($computerId && $start && $duration) {
        $startTs = strtotime($start);
        
        if ($startTs < time() - 300) {
            $error = 'Нельзя бронировать на прошедшее время.';
        } else {
            $endTs = strtotime("+{$duration} hours", $startTs);
            $startStr = date('Y-m-d H:i:s', $startTs);
            $endStr = date('Y-m-d H:i:s', $endTs);

            if (createReservation($pdo, $_SESSION['user_id'], $computerId, $startStr, $endStr)) {
                $message = 'Место успешно забронировано на ' . $duration . ' ч.!';
            } else {
                $error = 'Это место уже занято на выбранное время, либо ПК на обслуживании.';
            }
        }
    } else {
        $error = 'Заполните все поля.';
    }
}

$computers = $pdo->query("SELECT * FROM computers WHERE status = 'available' ORDER BY seat_number")->fetchAll();
$title = 'Бронирование';
require __DIR__ . '/../templates/header.php';
?>
<h1>Бронирование ПК</h1>
<?php if ($message): ?><p style="color:#86efac; margin-bottom:1rem;"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:#fca5a5; margin-bottom:1rem;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<form method="POST" style="max-width: 500px;">
    <div style="margin-bottom: 1rem;">
        <label>Выберите компьютер:</label>
        <select name="computer_id" required>
            <option value="">-- Выберите --</option>
            <?php foreach ($computers as $pc): ?>
                <!-- Проверяем, совпадает ли ID из GET-запроса с ID в списке -->
                <option value="<?= $pc['id'] ?>" <?= ($pc['id'] === $selectedPcId) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pc['seat_number']) ?> — <?= htmlspecialchars($pc['description']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <label>Время начала:</label>
        <input type="datetime-local" name="start_time" required>
    </div>
    
    <div style="margin-bottom: 1.5rem;">
        <label>Пакет времени:</label>
        <select name="duration" required>
            <option value="4">4 часа</option>
            <option value="6">6 часов</option>
            <option value="8">8 часов</option>
            <option value="10">10 часов</option>
            <option value="12">12 часов</option>
        </select>
    </div>
    
    <button type="submit" class="btn">Забронировать</button>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>