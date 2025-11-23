<?php
require_once 'config.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Авторизация пользователя
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            return true;
        }
        return false;
    }
    
    // Регистрация нового пользователя
    public function register($username, $email, $password) {
        // Проверяем, нет ли уже такого пользователя
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return false; // Пользователь уже существует
        }
        
        // Создаем нового пользователя
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$username, $email, $password_hash])) {
            return $this->login($username, $password);
        }
        
        return false;
    }
    
    // Выход из системы
    public function logout() {
        session_destroy();
        header('Location: feedback.php');
        exit;
    }
    
    // Проверка авторизации
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Получение текущего пользователя
    public function getCurrentUser() {
        return $_SESSION['username'] ?? null;
    }
    
    // Получение ID текущего пользователя
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

$auth = new Auth($pdo);
?>
