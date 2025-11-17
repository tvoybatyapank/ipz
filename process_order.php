<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if ($data === null) {
        echo json_encode(['success' => false, 'message' => 'Некоректні дані']);
        exit;
    }

    $name = $data['name'] ?? null;
    $phone = $data['phone'] ?? null;
    $email = $data['email'] ?? null; 
    $address = $data['address'] ?? null;
    $cart = $data['cart'] ?? [];
    $total_amount = $data['total_amount'] ?? 0;

    if (empty($name) || empty($phone) || empty($email) || empty($address) || empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Всі поля є обов\'язковими, і кошик не може бути порожнім.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Збереження або оновлення інформації про клієнта
        $customer_id = null;
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            $sql_customer = "SELECT id FROM customers WHERE user_id = ?";
            $stmt = $conn->prepare($sql_customer);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Запис вже існує, оновлюємо його
                $customer = $result->fetch_assoc();
                $customer_id = $customer['id'];
                $sql_update_customer = "UPDATE customers SET name = ?, phone_number = ?, email = ?, address = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update_customer);
                $stmt_update->bind_param("ssssi", $name, $phone, $email, $address, $customer_id); 
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                // Запису не існує, створюємо новий
                $sql_insert_customer = "INSERT INTO customers (name, phone_number, email, address, user_id) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert_customer);
                $stmt_insert->bind_param("ssssi", $name, $phone, $email, $address, $user_id); 
                $stmt_insert->execute();
                $customer_id = $conn->insert_id;
                $stmt_insert->close();
            }
            $stmt->close();
        } else {
            // Якщо користувач не авторизований
            $sql_insert_customer = "INSERT INTO customers (name, phone_number, email, address) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert_customer);
            $stmt_insert->bind_param("ssss", $name, $phone, $email, $address); 
            $stmt_insert->execute();
            $customer_id = $conn->insert_id;
            $stmt_insert->close();
        }

        // 2. Збереження інформації про замовлення
        $sql_order = "INSERT INTO orders (customer_id, total_amount) VALUES (?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("id", $customer_id, $total_amount);
        $stmt_order->execute();
        $order_id = $conn->insert_id;
        $stmt_order->close();

        // 3. Збереження деталей замовлення
        $sql_items = "INSERT INTO order_items (order_id, dish_id, quantity) VALUES (?, ?, ?)";
        $stmt_items = $conn->prepare($sql_items);

        foreach ($cart as $dish_id => $item) {
            $quantity = $item['quantity'];
            $stmt_items->bind_param("iii", $order_id, $dish_id, $quantity);
            $stmt_items->execute();
        }
        $stmt_items->close();

        $conn->commit();

        echo json_encode(['success' => true, 'order_id' => $order_id]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Помилка при оформленні замовлення: ' . $e->getMessage()]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неправильний метод запиту.']);
}
?>