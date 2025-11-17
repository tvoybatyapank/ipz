<?php
session_start();
include 'db.php';

// Перевірка, чи авторизований користувач як адміністратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Обробка повного очищення замовлень
if (isset($_GET['clear_all']) && $_GET['clear_all'] == 'true') {
    $conn->begin_transaction();

    try {
        $conn->query("DELETE FROM order_items");

        $conn->query("DELETE FROM orders");

        $conn->commit();
        echo "Всі замовлення успішно очищено!";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "Помилка при очищенні замовлень: " . $e->getMessage();
    }
}

// 2. Обробка поштучного видалення замовлення
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $conn->begin_transaction();

    try {
        $sql_items = "DELETE FROM order_items WHERE order_id = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $delete_id);
        $stmt_items->execute();
        $stmt_items->close();

        $sql_order = "DELETE FROM orders WHERE id = ?";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("i", $delete_id);
        $stmt_order->execute();
        $stmt_order->close();

        $conn->commit();
        echo "Замовлення №" . htmlspecialchars($delete_id) . " успішно видалено.";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "Помилка видалення замовлення: " . $e->getMessage();
    }
}

$conn->close();
header("Location: orders.php");
exit();
?>