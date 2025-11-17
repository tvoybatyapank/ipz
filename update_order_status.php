<?php
session_start();
include 'db.php';

// Перевірка, чи авторизований користувач як адміністратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // 1. Оновлення статусу замовлення в базі даних
    $sql_update = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_status, $order_id);

    if ($stmt_update->execute()) {
        // 2. Отримання пошти клієнта для відправки листа
        $sql_email = "SELECT c.email, c.name FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?";
        $stmt_email = $conn->prepare($sql_email);
        $stmt_email->bind_param("i", $order_id);
        $stmt_email->execute();
        $result_email = $stmt_email->get_result();
        $customer_data = $result_email->fetch_assoc();

        if ($customer_data && !empty($customer_data['email'])) {
            $to = $customer_data['email'];
            $subject = "Оновлення статусу вашого замовлення №" . $order_id;
            $message = "Привіт, " . htmlspecialchars($customer_data['name']) . "!\n\n";
            $message .= "Статус вашого замовлення №" . $order_id . " було оновлено.\n";
            $message .= "Новий статус: " . htmlspecialchars($new_status) . "\n\n";
            $message .= "Дякуємо, що обрали нас!";
            $headers = "From: S.Traktir@gmail.com\r\n"; 
            $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

            // Відправлення електронного листа
            if (mail($to, $subject, $message, $headers)) {
            } else {
                error_log("Помилка відправки листа на адресу: " . $to);
            }
        }

        echo "Статус замовлення №" . htmlspecialchars($order_id) . " успішно оновлено на " . htmlspecialchars($new_status);
    } else {
        echo "Помилка оновлення статусу: " . $stmt_update->error;
    }

    $stmt_update->close();
    $conn->close();

    // Перенаправлення назад на сторінку замовлень
    header("Location: orders.php");
    exit();
} else {
    header("Location: orders.php");
    exit();
}
?>