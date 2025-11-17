<?php
include 'header.php'; 
?>

<div class="page-content-wrapper">
    <div class="hero">
        <h1>Свіжі страви, швидка доставка!</h1>
        <p>Замовляйте улюблені страви онлайн і насолоджуйтесь якістю та швидкістю.</p>
        <a href="menu.php" class="btn btn-primary">Переглянути меню</a>
    </div>

    <div class="slider-container">
        <div class="slider-wrapper">
            <div class="slide active">
                <img src="banner1.jpg" alt="Банер 1">
            </div>
            <div class="slide">
                <img src="banner2.jpg" alt="Банер 2">
            </div>
            <div class="slide">
                <img src="banner3.jpg" alt="Банер 3">
            </div>
        </div>
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
        <div class="dots-container">
            <span class="dot active" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
        </div>
    </div>
</div>
<div class="about-us-section">
    <div class="container">
        <h2>Про нас</h2>
        <section>
    <h2>Заказать суши с доставкой в Днепре</h2>
    <p>Вкусно поесть просто – закажите суши в <strong>Самохотовъ Трактир</strong> не выходя из дома. 
    Мы заботимся о ваших предпочтениях и гарантируем качество каждого блюда.</p>
  </section>

  <section>
    <h2>Быстрая доставка суши Днепр</h2>
    <p>Оформляйте заказ онлайн — и любимые суши уже в пути. Мы работаем ежедневно с 10:00 до 22:00 и доставляем свежие блюда по всему городу.</p>
  </section>

  <section>
    <h2>Почему выбирают Самохотовъ Трактир</h2>
    <ul>
      <li>Свежие ингредиенты и проверенные поставщики.</li>
      <li>Опытные сушисты — настоящие мастера японской кухни.</li>
      <li>Быстрая доставка даже в часы пик.</li>
      <li>Классические и эксклюзивные роллы, регулярные новинки.</li>
      <li>Удобное оформление заказа онлайн или по телефону, любая форма оплаты.</li>
    </ul>
  </section>

  <section>
    <h2>Большой выбор блюд</h2>
    <p>В меню сотни вариантов: классика, авторские сеты, вегетарианские роллы, темпура и многое другое. 
    Также у нас вы найдете:</p>
    <ul>
      <li>Пиццу на любой вкус;</li>
      <li>Street food: шаурма, стрит-роллы, снек-боулы;</li>
      <li>Боулы и салаты;</li>
      <li>Супы;</li>
      <li>Корейское меню;</li>
      <li>WOK-боксы;</li>
      <li>Десерты и напитки.</li>
    </ul>
  </section>

  <section>
    <h2>Адреса точек самовывоза</h2>
    <ul>
      <li>ул. Вкусная, 13</li>
      <li>просп. Восточный, 45</li>
      <li>ул. Самурайская, 77-А</li>
    </ul>
  </section>

  <section>
    <h2>Выгодные условия</h2>
    <ul>
      <li>Скидка 10% при самовывозе;</li>
      <li>Скидка 10% в день рождения;</li>
      <li>Накопительные баллы для оплаты заказов;</li>
      <li>Регулярные акции и кэшбэк.</li>
    </ul>
  </section>

  <section>
    <h2>Самохотовъ Трактир — современный сервис доставки суши</h2>
    <p>С нами вы получаете: широкий выбор суши и других блюд, быструю доставку, удобное оформление заказа и приятные бонусы.</p>
    <p><strong>Не откладывайте — закажите суши в Днепре с доставкой уже сегодня!</strong></p>
  </section>
    </div>
</div>

<script>
    let slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("slide");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) { slideIndex = 1 }
        if (n < 1) { slideIndex = slides.length }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active";
    }
</script>

<?php
include 'footer.php'; 
?>