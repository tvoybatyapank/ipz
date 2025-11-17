<?php
include 'db.php';
include 'header.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Валідація введених даних
    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "Будь ласка, заповніть усі поля.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Будь ласка, введіть коректну адресу електронної пошти.";
    } elseif (strlen($password) < 6) {
        $error_message = "Пароль повинен містити щонайменше 6 символів.";
    } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁіІїЇєЄ\s'-]+$/u", $name)) {
        $error_message = "Ім'я може містити лише літери, пробіли, апострофи та дефіси.";
    } else {
        // 2. Перевірка, чи не існує користувач з таким email
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Користувач з такою адресою електронної пошти вже існує.";
        } else {
            // 3. Хешування пароля перед збереженням
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 4. Вставка нового користувача в базу даних
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Реєстрація пройшла успішно! Тепер ви можете <a href='login.php'>увійти</a>.";
            } else {
                $error_message = "Помилка реєстрації. Спробуйте пізніше.";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<div class="page-content-wrapper">
    <div class="container">
        <div class="form-container">
            <h2>Реєстрація</h2>

            <?php if ($error_message): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <p style="color: green;"><?php echo $success_message; ?></p>
            <?php endif; ?>

            <form action="register.php" method="post">
                <label for="name">Ім'я:</label>
                <input type="text" id="name" name="name" required><br><br>

                <label for="email">Електронна пошта:</label>
                <input type="email" id="email" name="email" required><br><br>

                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required><br><br>

                <input type="submit" value="Зареєструватися">
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>