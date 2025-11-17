<?php session_start(); ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Самохотовъ Трактир</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo-container">
                <img src="logo.png" class="logo-img">
                <a href="index.php" class="logo">Самохотовъ Трактир</a>
            </div>
           
            <nav>
                <ul>
                    <li>Контакт:<a href="tel:+380501234567">+380 (50) 123-45-67</a></li>
                    <li><a href="menu.php">Меню</a></li>
                    <li><a href="cart.php">Кошик</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="orders.php">Замовлення</a></li>
                            <li><a href="admin.php">Адмін</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Вихід</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вхід</a></li>
                        <li><a href="register.php">Реєстрація</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
        </div>
    </header>
    <main class="container">