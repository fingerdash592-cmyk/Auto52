<?php
header('Content-Type: application/json');

// Настройки базы данных для XAMPP
$host = 'localhost';
$dbname = 'autoserviceclient';
$username = 'root';    // Стандартный пользователь XAMPP
$password = '';        // Стандартный пароль XAMPP (пустой)

// Включение отладки (убрать в продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение данных из формы
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $service_type = $_POST['serviceType'] ?? '';
    $problem_description = $_POST['problemDescription'] ?? '';
    $agree = isset($_POST['agree']) ? 1 : 0;

    // Валидация данных
    $errors = [];

    if (empty($name)) {
        $errors[] = 'ФИО обязательно для заполнения';
    }

    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    }

    if (empty($service_type)) {
        $errors[] = 'Тип услуги обязателен для выбора';
    }

    if (!$agree) {
        $errors[] = 'Необходимо согласие на обработку данных';
    }

    // Если есть ошибки, возвращаем их
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Подготовка и выполнение SQL-запроса
    $sql = "INSERT INTO bookings (name, phone, email, service_type, problem_description, agree) 
            VALUES (:name, :phone, :email, :service_type, :problem_description, :agree)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':service_type' => $service_type,
        ':problem_description' => $problem_description,
        ':agree' => $agree
    ]);

    // Успешный ответ
    echo json_encode([
        'success' => true, 
        'message' => 'Спасибо! Ваша заявка принята. Мы свяжемся с вами в ближайшее время.'
    ]);

} catch (PDOException $e) {
    // Ошибка базы данных
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Общая ошибка
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка: ' . $e->getMessage()
    ]);
}
?>