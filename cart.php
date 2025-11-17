<?php
include 'header.php';
?>

<div class="page-content-wrapper">
    <div class="container">
        <h1>Ваш Кошик</h1>

        <table id="cart-table">
            <thead>
                <tr>
                    <th>Назва</th>
                    <th>Ціна</th>
                    <th>Кількість</th>
                    <th>Сума</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Загальна сума:</td>
                    <td id="total-price">0.00 грн</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="checkout-form">
            <h2>Оформлення Замовлення</h2>
            <form id="checkout-form">
                <label for="name">Ваше ім'я:</label>
                <input type="text" id="name" name="name" required><br><br>
                
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required><br><br>
                
                <label for="email">Електронна пошта:</label>
                <input type="email" id="email" name="email" required><br><br>
                
                <label for="address">Адреса доставки:</label>
                <textarea id="address" name="address" required></textarea><br><br>
                
                <input type="submit" value="Підтвердити замовлення">
            </form>
        </div>
    </div>
</div>

<div id="order-status" style="display:none; text-align:center; margin-top: 20px;"></div>

<script>
/* JavaScript-код для управління кошиком та обробки форми */
document.addEventListener('DOMContentLoaded', () => {
    // Отримання даних кошика з локального сховища
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    const tableBody = document.querySelector('#cart-table tbody');
    let totalPrice = 0;

    // Функція для оновлення відображення кошика на сторінці
    function updateCartDisplay() {
        tableBody.innerHTML = '';
        totalPrice = 0;

        for (const dishId in cart) {
            const item = cart[dishId];
            const itemTotal = item.price * item.quantity;
            totalPrice += itemTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.price.toFixed(2)} грн</td>
                <td>
                    <input type="number" value="${item.quantity}" min="1" class="item-quantity" data-id="${dishId}">
                </td>
                <td>${itemTotal.toFixed(2)} грн</td>
                <td>
                    <button class="remove-btn" data-id="${dishId}">Видалити</button>
                </td>
            `;
            tableBody.appendChild(row);
        }

        document.getElementById('total-price').textContent = `${totalPrice.toFixed(2)} грн`;
    }

    // Обробник подій для зміни кількості товарів
    tableBody.addEventListener('change', (event) => {
        if (event.target.classList.contains('item-quantity')) {
            const dishId = event.target.dataset.id;
            const newQuantity = parseInt(event.target.value);
            if (newQuantity > 0) {
                cart[dishId].quantity = newQuantity;
            } else {
                delete cart[dishId];
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }
    });

    // Обробник подій для видалення товарів
    tableBody.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-btn')) {
            const dishId = event.target.dataset.id;
            delete cart[dishId];
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }
    });

    // Обробник подій для відправки форми замовлення
    document.getElementById('checkout-form').addEventListener('submit', async (event) => {
        event.preventDefault();

        const name = document.getElementById('name').value;
        const phone = document.getElementById('phone').value;
        const email = document.getElementById('email').value; 
        const address = document.getElementById('address').value;

        // Валідація імені та телефону
        const nameRegex = /^[a-zA-Zа-яА-ЯіІїЇєЄ\s'-]+$/;
        if (!nameRegex.test(name)) {
            alert("Ім'я може містити лише літери, пробіли, апострофи та дефіси.");
            return;
        }

        const phoneRegex = /^[0-9\s()+-]+$/;
        if (!phoneRegex.test(phone)) {
            alert("Будь ласка, введіть коректний номер телефону.");
            return;
        }

        const cartData = JSON.parse(localStorage.getItem('cart')) || {};
        const totalAmount = parseFloat(document.getElementById('total-price').textContent);

        // Формування об'єкта даних для відправки на сервер
        const data = {
            name,
            phone,
            email, 
            address,
            cart: cartData,
            total_amount: totalAmount
        };

        // Відправка даних на сервер за допомогою Fetch API
        const response = await fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        const orderStatusDiv = document.getElementById('order-status');

        // Обробка відповіді від сервера
        if (result.success) {
            orderStatusDiv.style.display = 'block';
            orderStatusDiv.innerHTML = `<h2 style="color: green;">Замовлення успішно оформлено!</h2><p>Ваш номер замовлення: **${result.order_id}**</p><p>Дякуємо!</p>`;
            localStorage.removeItem('cart');
            updateCartDisplay();
        } else {
            orderStatusDiv.style.display = 'block';
            orderStatusDiv.innerHTML = `<h2 style="color: red;">Помилка:</h2><p>${result.message}</p>`;
        }
    });

    updateCartDisplay();
});
</script>

<?php include 'footer.php'; ?>