<?php
include 'db.php'; 
include 'header.php'; 
?>

<div class="container">
    <h1>Наше Меню</h1>

    <?php
    // Отримання всіх категорій
    $sql_categories = "SELECT id, name FROM categories ORDER BY name";
    $result_categories = $conn->query($sql_categories);

    if ($result_categories->num_rows > 0) {
        while ($row_cat = $result_categories->fetch_assoc()) {
            $category_id = $row_cat['id'];
            $category_name = htmlspecialchars($row_cat['name']);

            // Виведення секції для поточної категорії
            echo "<div class='category-section'>";
            echo "<h2>" . $category_name . "</h2>";

            // Отримання страв для поточної категорії
            $sql_dishes = "SELECT id, name, description, price, image_path FROM dishes WHERE category_id = $category_id";
            $result_dishes = $conn->query($sql_dishes);

            if ($result_dishes->num_rows > 0) {
                echo "<div class='menu-dishes'>";
                while ($row_dish = $result_dishes->fetch_assoc()) {
                    echo "<div class='dish-card'>";
                    if (!empty($row_dish['image_path'])) {
                        echo "<img src='" . htmlspecialchars($row_dish["image_path"]) . "' alt='" . htmlspecialchars($row_dish["name"]) . "'>";
                    }
                    echo "<h3>" . htmlspecialchars($row_dish["name"]) . "</h3>";
                    echo "<p class='description'>" . htmlspecialchars($row_dish["description"]) . "</p>";
                    echo "<p class='price'>" . htmlspecialchars($row_dish["price"]) . " грн</p>";
                    echo "<button class='add-to-cart-btn' data-id='" . $row_dish['id'] . "'>Додати до кошика</button>";
                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<p>Немає страв у цій категорії.</p>";
            }
            echo "</div>"; 
        }
    } else {
        echo "<p>Немає доступних категорій.</p>";
    }

    $conn->close();
    ?>
</div>
<script src="script.js"></script>
<?php include 'footer.php'; ?>