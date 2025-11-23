<?php
require_once 'config.php';
require_once 'auth.php';

class MessageManager {
    private $pdo;
    private $auth;
    
    public function __construct($pdo, $auth) {
        $this->pdo = $pdo;
        $this->auth = $auth;
    }
    
    // Сохранение сообщения в базу данных
    public function addMessage($message_text, $recipient) {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO messages (user_id, message_text, recipient) VALUES (?, ?, ?)");
        return $stmt->execute([
            $this->auth->getCurrentUserId(),
            trim($message_text),
            $recipient
        ]);
    }
    
    // Получение сообщений текущего пользователя
    public function getUserMessages() {
        if (!$this->auth->isLoggedIn()) {
            return [];
        }
        
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.username 
            FROM messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.user_id = ? 
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$this->auth->getCurrentUserId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$messageManager = new MessageManager($pdo, $auth);
?>
