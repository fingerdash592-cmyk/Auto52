<?php
$host = 'localhost';
$dbname = 'autoservice';
$username = 'ваш_пользователь';
$password = 'ваш_пароль';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "Подключение к базе данных успешно!";
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}
?>