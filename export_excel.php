<?php
session_start();
include 'db.php';

// Перевірка, чи авторизований користувач як адміністратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_export_' . date('Y-m-d') . '.csv');

// Створення "файлу" для запису даних у вихідний потік
$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($output, array('ID замовлення', 'Дата', 'Статус', 'Загальна сума', 'Ім\'я клієнта', 'Телефон', 'Адреса', 'Склад замовлення'));

// SQL-запит для отримання всіх даних про замовлення
$sql = "SELECT 
            o.id, 
            o.created_at, 
            o.status, 
            o.total_amount, 
            c.name AS customer_name,
            c.phone_number,
            c.address
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Отримання деталей замовлення
        $order_items_sql = "SELECT d.name, oi.quantity FROM order_items oi JOIN dishes d ON oi.dish_id = d.id WHERE oi.order_id = ?";
        $stmt_items = $conn->prepare($order_items_sql);
        $stmt_items->bind_param("i", $row['id']);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();

        $dish_list = [];
        while ($item = $items_result->fetch_assoc()) {
            $dish_list[] = $item['name'] . ' (x' . $item['quantity'] . ')';
        }
        $stmt_items->close();

        // Створення масиву для запису в CSV
        $csv_row = [
            $row['id'],
            $row['created_at'],
            $row['status'],
            $row['total_amount'],
            $row['customer_name'],
            $row['phone_number'],
            $row['address'],
            implode(', ', $dish_list) 
        ];

        // Запис рядка в CSV-файл
        fputcsv($output, $csv_row);
    }
}

fclose($output);
$conn->close();
exit;
?>