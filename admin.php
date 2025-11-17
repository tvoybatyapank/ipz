<?php
include 'header.php';
include 'db.php';

// Перевірка, чи авторизований користувач як адміністратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Обробка POST-запиту для додавання або редагування страви
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_id = isset($_POST['dish_id']) && !empty($_POST['dish_id']) ? $_POST['dish_id'] : null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $image_path = null;
    $upload_success = true;

    // Секція завантаження зображення
    if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["dish_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $check = getimagesize($_FILES["dish_image"]["tmp_name"]);
        if ($check === false) {
            echo "Файл не є зображенням.";
            $upload_success = false;
        }

        if ($_FILES["dish_image"]["size"] > 500000) {
            echo "Вибачте, ваш файл занадто великий.";
            $upload_success = false;
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Вибачте, дозволено завантажувати лише JPG, JPEG, PNG & GIF файли.";
            $upload_success = false;
        }

        if ($upload_success) {
            if (move_uploaded_file($_FILES["dish_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                echo "Вибачте, виникла помилка під час завантаження вашого файлу.";
                $upload_success = false;
            }
        }
    }

    // Обробка запиту до бази даних (додавання або оновлення страви)
    if ($upload_success) {
        if ($dish_id) {
            $sql = "UPDATE dishes SET name=?, description=?, price=?, category_id=?";
            if ($image_path) {
                $sql .= ", image_path=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdsis", $name, $description, $price, $category_id, $image_path, $dish_id);
            } else {
                $sql .= " WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdsi", $name, $description, $price, $category_id, $dish_id);
            }
        } else {
            $sql = "INSERT INTO dishes (name, description, price, category_id, image_path) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdis", $name, $description, $price, $category_id, $image_path);
        }

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Операція успішна!</p>";
        } else {
            echo "<p style='color:red;'>Помилка виконання запиту: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Обробка GET-запиту для видалення страви
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM dishes WHERE id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Помилка підготовки запиту: " . $conn->error);
    }
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<p style='color:green;'>Страву успішно видалено!</p>";
    } else {
        echo "<p style='color:red;'>Помилка видалення страви: " . $stmt->error . "</p>";
    }
    $stmt->close();
    header("Location: admin.php");
    exit();
}

?>

<div class="admin-panel-container">
    <h1>Панель Адміністратора</h1>

    <div class="admin-section">
        <h2>Додати/Редагувати страву</h2>
        <form action="admin.php" method="post" enctype="multipart/form-data">
            <input type="hidden" id="dish_id" name="dish_id">
            <label for="name">Назва страви:</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Опис:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="price">Ціна:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="category_id">Категорія:</label>
            <select id="category_id" name="category_id" required>
                <?php
                // Динамічне заповнення списку категорій з бази даних
                $sql_categories = "SELECT id, name FROM categories";
                $result_categories = $conn->query($sql_categories);
                while ($row = $result_categories->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                }
                ?>
            </select>

            <label for="dish_image">Фото страви:</label>
            <input type="file" id="dish_image" name="dish_image" accept="image/*">

            <input type="submit" name="submit_dish" value="Зберегти страву">
        </form>
    </div>

    <hr>

    <div class="admin-section">
        <h2>Список Страв</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Назва</th>
                    <th>Опис</th>
                    <th>Ціна</th>
                    <th>Категорія</th>
                    <th>Фото</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // SQL-запит для отримання всіх страв з їх категоріями
                $sql = "SELECT d.id, d.name, d.description, d.price, d.category_id, d.image_path, c.name AS category_name
                         FROM dishes d JOIN categories c ON d.category_id = c.id";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>";
                    if (!empty($row['image_path'])) {
                        echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Фото страви' style='width: 50px; height: auto;'>";
                    } else {
                        echo "Немає фото";
                    }
                    echo "</td>";
                    echo "<td><a href='#' onclick=\"editDish(" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ")\">Редагувати</a> | <a href='admin.php?delete_id=" . $row['id'] . "'>Видалити</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <hr>

    <script>
    function editDish(dish) {
        document.getElementById('dish_id').value = dish.id;
        document.getElementById('name').value = dish.name;
        document.getElementById('description').value = dish.description;
        document.getElementById('price').value = dish.price;
        document.getElementById('category_id').value = dish.category_id;
        document.querySelector('input[name="submit_dish"]').value = 'Оновити страву';
    }
    </script>
</div>

<?php
$conn->close();
include 'footer.php';
?>