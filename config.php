<?php
session_start();

// Конфигурация базы данных
$host = 'localhost';
$dbname = 'feedback_system';
$username = 'imko_user';      // Имя пользователя которое создали
$password = 'password123'; // Пароль который установили

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Успешное подключение к базе данных!";
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
