<?php
session_start();

// Пароль для доступа к админке (измените на свой)
$admin_password = 'admin123';

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Если форма входа отправлена
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        // Показываем форму входа
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = "Неверный пароль!";
        }
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Вход в панель администратора</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                    width: 100%;
                    max-width: 400px;
                }
                h1 {
                    text-align: center;
                    color: #333;
                    margin-bottom: 30px;
                }
                .form-group {
                    margin-bottom: 20px;
                }
                label {
                    display: block;
                    margin-bottom: 5px;
                    color: #555;
                }
                input[type="password"] {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    font-size: 16px;
                    box-sizing: border-box;
                }
                button {
                    width: 100%;
                    padding: 12px;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                }
                button:hover {
                    background: #0056b3;
                }
                .error {
                    color: #dc3545;
                    text-align: center;
                    margin-bottom: 15px;
                    padding: 10px;
                    background: #f8d7da;
                    border-radius: 5px;
                }
                .info {
                    text-align: center;
                    margin-top: 20px;
                    color: #666;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>🔐 Вход в админку</h1>
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required autofocus>
                    </div>
                    <button type="submit">Войти</button>
                </form>
                <div class="info">
                    Панель управления заявками автосервиса
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Подключение к базе данных
try {
    $pdo = new PDO("mysql:host=localhost;dbname=autoservice", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Обработка удаления заявки
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header("Location: admin.php");
    exit;
}

// Получение заявок
$search = $_GET['search'] ?? '';
if ($search) {
    $searchTerm = "%$search%";
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? OR service_type LIKE ? ORDER BY created_at DESC");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
}
$bookings = $stmt->fetchAll();
$count = count($bookings);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Заявки</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .count { 
            background: #28a745; 
            color: white; 
            padding: 8px 15px; 
            border-radius: 20px;
            font-weight: bold;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .search-form {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .search-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
        }
        .search-form button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background-color: #343a40; 
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        tr:hover {
            background-color: #e9ecef;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-right: 10px;
        }
        .back-btn:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Панель администратора - Заявки</h1>
        <div class="stats">
            <span class="count">Всего заявок: <?= $count ?></span>
            <a href="?logout" class="logout-btn">🚪 Выйти</a>
        </div>
    </div>

    <div class="search-form">
        <form method="GET" style="display: flex; width: 100%; gap: 10px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Поиск по имени, телефону, email или услуге...">
            <button type="submit">🔍 Поиск</button>
            <?php if ($search): ?>
                <a href="admin.php" style="padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">❌ Сброс</a>
            <?php endif; ?>
        </form>
    </div>

    <a href="Reg.html" class="back-btn">← Форма записи</a>

    <?php if ($count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Услуга</th>
                    <th>Описание проблемы</th>
                    <th>Согласие</th>
                    <th>Дата подачи</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['id']) ?></td>
                    <td><?= htmlspecialchars($booking['name']) ?></td>
                    <td><?= htmlspecialchars($booking['phone']) ?></td>
                    <td><?= htmlspecialchars($booking['email'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($booking['service_type']) ?></td>
                    <td><?= htmlspecialchars($booking['problem_description'] ?: '—') ?></td>
                    <td><?= $booking['agree'] ? '✅ Да' : '❌ Нет' ?></td>
                    <td><?= htmlspecialchars($booking['created_at']) ?></td>
                    <td class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_id" value="<?= $booking['id'] ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Удалить заявку от <?= htmlspecialchars($booking['name']) ?>?')">🗑️ Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <h3>📭 Заявок не найдено</h3>
            <p><?= $search ? 'Попробуйте изменить условия поиска' : 'Как только клиенты начнут оставлять заявки, они появятся здесь' ?></p>
        </div>
    <?php endif; ?>

    <script>
        // Автофокус на поле поиска
        document.querySelector('input[name="search"]')?.focus();
        
        // Подтверждение удаления
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить эту заявку?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>