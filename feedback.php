<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'messages.php';

// Обработка выхода
if (isset($_GET['logout'])) {
    $auth->logout();
}

// Обработка авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        $success_message = "Успешная авторизация!";
    } else {
        $error_message = "Ошибка авторизации! Проверьте логин и пароль.";
    }
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['reg_username'] ?? '';
    $email = $_POST['reg_email'] ?? '';
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['reg_confirm_password'] ?? '';
    
    if ($password !== $confirm_password) {
        $error_message = "Пароли не совпадают!";
    } elseif (strlen($password) < 6) {
        $error_message = "Пароль должен содержать минимум 6 символов!";
    } else {
        if ($auth->register($username, $email, $password)) {
            $success_message = "Регистрация успешна! Добро пожаловать!";
        } else {
            $error_message = "Ошибка регистрации! Возможно, пользователь с таким именем или email уже существует.";
        }
    }
}

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if ($auth->isLoggedIn()) {
        $message_text = $_POST['message_text'] ?? '';
        $recipient = $_POST['recipient'] ?? '';
        
        if (!empty($message_text) && !empty($recipient)) {
            if ($messageManager->addMessage($message_text, $recipient)) {
                $success_message = "Сообщение успешно отправлено!";
            } else {
                $error_message = "Ошибка при отправке сообщения!";
            }
        } else {
            $error_message = "Заполните все поля!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма обратной связи</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Обратная связь</h1>
        </div>
        
        <div class="content">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($auth->isLoggedIn()): ?>
                <!-- Пользователь авторизован - показываем форму обратной связи -->
                <div class="user-info">
                    <h3>Добро пожаловать, <?php echo htmlspecialchars($auth->getCurrentUser()); ?>!</h3>
                    <p>Вы успешно авторизованы в системе</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="recipient">Адресат:</label>
                        <select id="recipient" name="recipient" required>
                            <option value="">Выберите адресата</option>
                            <option value="support@example.com">Техническая поддержка</option>
                            <option value="admin@example.com">Администратор</option>
                            <option value="manager@example.com">Менеджер</option>
                            <option value="info@example.com">Общие вопросы</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message_text">Текст сообщения:</label>
                        <textarea id="message_text" name="message_text" required placeholder="Введите ваше сообщение..."></textarea>
                    </div>
                    
                    <button type="submit" name="send_message" class="btn btn-primary">Отправить сообщение</button>
                    <a href="?logout=1" class="btn btn-danger">Выйти</a>
                </form>
                
                <!-- История сообщений -->
                <div class="messages-section">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">Мои сообщения</h3>
                    <?php
                    $messages = $messageManager->getUserMessages();
                    if (empty($messages)): ?>
                        <div class="no-messages">
                            У вас пока нет отправленных сообщений.
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message-card">
                                <div class="message-header">
                                    <span class="message-recipient"><?php echo htmlspecialchars($message['recipient']); ?></span>
                                    <span class="message-date"><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></span>
                                </div>
                                <div class="message-text">
                                    <?php echo nl2br(htmlspecialchars($message['message_text'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
            <?php else: ?>
                <!-- Пользователь не авторизован - показываем форму входа/регистрации -->
                <div class="form-tabs">
                    <div class="form-tab active" onclick="showForm('login')">Вход</div>
                    <div class="form-tab" onclick="showForm('register')">Регистрация</div>
                </div>
                
                <!-- Форма входа -->
                <div id="login-form" class="form-container active">
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Имя пользователя:</label>
                            <input type="text" id="username" name="username" required placeholder="Введите имя пользователя">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Пароль:</label>
                            <input type="password" id="password" name="password" required placeholder="Введите пароль">
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary">Войти</button>
                    </form>
                </div>
                
                <!-- Форма регистрации -->
                <div id="register-form" class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label for="reg_username">Имя пользователя:</label>
                            <input type="text" id="reg_username" name="reg_username" required placeholder="Придумайте имя пользователя">
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_email">Email:</label>
                            <input type="email" id="reg_email" name="reg_email" required placeholder="Введите ваш email">
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_password">Пароль:</label>
                            <input type="password" id="reg_password" name="reg_password" required placeholder="Придумайте пароль (мин. 6 символов)">
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_confirm_password">Подтвердите пароль:</label>
                            <input type="password" id="reg_confirm_password" name="reg_confirm_password" required placeholder="Повторите пароль">
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-success">Зарегистрироваться</button>
                    </form>
                </div>
                
                <div class="test-users">
                    <strong>Тестовые пользователи:</strong><br>
                    Логин: <code>user1</code> | Пароль: <code>password</code><br>
                    Логин: <code>user2</code> | Пароль: <code>password</code>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showForm(formType) {
            // Скрываем все формы
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active');
            });
            
            // Убираем активный класс со всех вкладок
            document.querySelectorAll('.form-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Показываем нужную форму и активируем вкладку
            if (formType === 'login') {
                document.getElementById('login-form').classList.add('active');
                document.querySelectorAll('.form-tab')[0].classList.add('active');
            } else {
                document.getElementById('register-form').classList.add('active');
                document.querySelectorAll('.form-tab')[1].classList.add('active');
            }
        }
    </script>
</body>
</html>
