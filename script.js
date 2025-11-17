document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const dishId = event.target.dataset.id;

            // Отримуємо дані про страву 
            const dishCard = event.target.closest('.dish-card');
            const dishName = dishCard.querySelector('h3').textContent;
            const dishPrice = parseFloat(dishCard.querySelector('.price').textContent.replace(' грн', ''));

            // Завантажуємо кошик з localStorage або створюємо новий
            let cart = JSON.parse(localStorage.getItem('cart')) || {};

            // Перевіряємо чи є страва вже в кошику
            if (cart[dishId]) {
                cart[dishId].quantity++;
            } else {
                cart[dishId] = {
                    name: dishName,
                    price: dishPrice,
                    quantity: 1
                };
            }

            localStorage.setItem('cart', JSON.stringify(cart));

            alert(`${dishName} додано до кошика!`);
        });
    });
});