<?php
include 'db.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "<p style='color:red;'>Невірний пароль.</p>";
        }
    } else {
        echo "<p style='color:red;'>Користувача з таким ім'ям не знайдено.</p>";
    }
    $stmt->close();
}
?>

<div class="page-content-wrapper">
    <div class="container">
        <div class="form-container">
            <h2>Вхід</h2>
            <form action="login.php" method="POST">
                <label for="username">Ім'я користувача:</label>
                <input type="text" id="username" name="username" required><br><br>
                
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required><br><br>
                
                <input type="submit" value="Увійти">
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>