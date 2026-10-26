<?php
function createReservation(PDO $pdo, int $userId, int $computerId, string $start, string $end): bool {
    try {
        $pdo->beginTransaction();
        
        // Блокируем ПК
        $stmt = $pdo->prepare("SELECT id FROM computers WHERE id = ? AND status = 'available' FOR UPDATE");
        $stmt->execute([$computerId]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            return false;
        }

        // Проверяем пересечение времени
        $check = $pdo->prepare("
            SELECT 1 FROM reservations 
            WHERE computer_id = ? AND status = 'active'
            AND (start_time < ? AND end_time > ?) LIMIT 1
        ");
        $check->execute([$computerId, $end, $start]);
        if ($check->fetch()) {
            $pdo->rollBack();
            return false;
        }

        // Создаем бронь
        $insert = $pdo->prepare("INSERT INTO reservations (user_id, computer_id, start_time, end_time) VALUES (?, ?, ?, ?)");
        $insert->execute([$userId, $computerId, $start, $end]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}