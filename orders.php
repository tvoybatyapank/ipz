<?php
include 'header.php';
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Визначаємо критерій сортування
$sort_by = 'o.created_at';
$sort_order = 'DESC';

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'amount':
            $sort_by = 'o.total_amount';
            $sort_order = 'DESC';
            break;
        case 'status':
            $sort_by = 'o.status';
            $sort_order = 'ASC';
            break;
        case 'date':
        default:
            $sort_by = 'o.created_at';
            $sort_order = 'DESC';
            break;
    }
}
?>

<div class="admin-panel-container">
    <h1>Актуальні Замовлення</h1>

    <div class="sort-links">
        Сортувати за:
        <a href="orders.php?sort=date">Датою</a> |
        <a href="orders.php?sort=amount">Сумою</a> |
        <a href="orders.php?sort=status">Статусом</a>
    </div>

    <div class="clear-all-orders">
        <a href="delete_orders.php?clear_all=true" onclick="return confirm('Ви впевнені, що хочете видалити всі замовлення? Цю дію не можна скасувати.');" class="btn btn-danger">Очистити всі замовлення</a>
    </div>

    <div class="export-button-container">
        <a href="export_excel.php" class="btn btn-success">Експортувати в Excel</a>
    </div>

    <div class="orders-list">
        <?php
        $sql = "SELECT 
                    o.id AS order_id, 
                    o.total_amount, 
                    o.created_at, 
                    o.status,
                    c.name AS customer_name, 
                    c.phone_number, 
                    c.address
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                ORDER BY " . $sort_by . " " . $sort_order;

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                echo "<div class='order-item'>";
                echo "<h3>Замовлення №" . $order['order_id'] . " <a href='delete_orders.php?delete_id=" . $order['order_id'] . "' onclick=\"return confirm('Ви впевнені, що хочете видалити це замовлення?');\" class='delete-link'>(Видалити)</a></h3>";
                echo "<p><strong>Клієнт:</strong> " . htmlspecialchars($order['customer_name']) . "</p>";
                echo "<p><strong>Телефон:</strong> " . htmlspecialchars($order['phone_number']) . "</p>";
                echo "<p><strong>Адреса:</strong> " . htmlspecialchars($order['address']) . "</p>";
                echo "<p><strong>Загальна сума:</strong> " . htmlspecialchars($order['total_amount']) . " грн</p>";
                echo "<p><strong>Час замовлення:</strong> " . htmlspecialchars($order['created_at']) . "</p>";
                echo "<p><strong>Статус:</strong> " . htmlspecialchars($order['status']) . "</p>";

                // Форма для зміни статусу
                echo "<form action='update_order_status.php' method='post'>";
                echo "<input type='hidden' name='order_id' value='" . $order['order_id'] . "'>";
                echo "<select name='status'>";
                $statuses = ['Нове', 'Готується', 'Доставляється', 'Виконано', 'Скасовано'];
                foreach ($statuses as $s) {
                    $selected = ($s == $order['status']) ? 'selected' : '';
                    echo "<option value='" . $s . "' " . $selected . ">" . $s . "</option>";
                }
                echo "</select>";
                echo "<input type='submit' value='Змінити'>";
                echo "</form>";

                // Отримання деталей замовлення
                $sql_items = "SELECT 
                                oi.quantity, 
                                d.name AS dish_name,
                                d.price
                              FROM order_items oi
                              JOIN dishes d ON oi.dish_id = d.id
                              WHERE oi.order_id = ?";
                $stmt = $conn->prepare($sql_items);
                $stmt->bind_param("i", $order['order_id']);
                $stmt->execute();
                $items_result = $stmt->get_result();

                echo "<h4>Склад замовлення:</h4>";
                echo "<ul>";
                while ($item = $items_result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($item['dish_name']) . " x " . htmlspecialchars($item['quantity']) . " (Ціна за шт: " . htmlspecialchars($item['price']) . " грн)</li>";
                }
                echo "</ul>";

                $stmt->close();
                echo "</div>";
            }
        } else {
            echo "<p>Наразі немає активних замовлень.</p>";
        }
        $conn->close();
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>