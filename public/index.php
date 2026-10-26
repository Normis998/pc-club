<?php
require __DIR__ . '/../config/bd.php';

// Запрос проверяет: если текущее время (NOW) попадает в бронь, то is_busy = 1
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM reservations r 
         WHERE r.computer_id = c.id 
           AND r.status = 'active' 
           AND NOW() BETWEEN r.start_time AND r.end_time) as is_busy 
        FROM computers c 
        ORDER BY c.seat_number";
$computers = $pdo->query($sql)->fetchAll();

$title = 'Главная';
require __DIR__ . '/../templates/header.php';
?>
<h1>Наши компьютеры</h1>
<p style="color:#94a3b8; margin-bottom:1.5rem;">Зарегистрируйтесь, чтобы забронировать свободное место.</p>
<div class="pc-grid">
    <?php foreach ($computers as $pc): 
        // Проверяем, можно ли кликнуть по карточке
        $isClickable = (!empty($_SESSION['user_id']) && $pc['status'] === 'available' && $pc['is_busy'] == 0);
    ?>
        
        <?php if ($isClickable): ?>
            <!-- Если можно бронировать, оборачиваем в ссылку -->
            <a href="booking.php?pc_id=<?= $pc['id'] ?>" class="pc-card" style="text-decoration: none; color: inherit; display: block; cursor: pointer;" title="Нажмите, чтобы забронировать">
        <?php else: ?>
            <!-- Иначе просто карточка -->
            <div class="pc-card">
        <?php endif; ?>

            <h3><?= htmlspecialchars($pc['seat_number']) ?></h3>
            <p><?= htmlspecialchars($pc['description']) ?></p>
            
            <?php if ($pc['status'] === 'maintenance'): ?>
                <span class="badge maintenance">Обслуживание</span>
            <?php elseif ($pc['is_busy'] > 0): ?>
                <span class="badge busy">Занят сейчас</span>
            <?php else: ?>
                <span class="badge available">Доступен</span>
            <?php endif; ?>
            
        <?php if ($isClickable): ?>
            </a>
        <?php else: ?>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>