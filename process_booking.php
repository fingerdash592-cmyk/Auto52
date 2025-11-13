<?php
header('Content-Type: application/json');

// Включение полного вывода ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Настройки базы данных для XAMPP
$host = 'localhost';
$dbname = 'autoservice';
$username = 'root';
$password = '';  // Пустой пароль для XAMPP

// Логирование для отладки
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Начало обработки\n", FILE_APPEND);

try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    file_put_contents('debug_log.txt', "✅ Подключение к БД успешно\n", FILE_APPEND);

    // Получение данных из формы
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $service_type = $_POST['serviceType'] ?? '';
    $problem_description = $_POST['problemDescription'] ?? '';
    $agree = isset($_POST['agree']) ? 1 : 0;

    file_put_contents('debug_log.txt', "📝 Получены данные: $name, $phone, $service_type\n", FILE_APPEND);

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
        file_put_contents('debug_log.txt', "❌ Ошибки валидации: " . implode(', ', $errors) . "\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Подготовка и выполнение SQL-запроса
    $sql = "INSERT INTO bookings (name, phone, email, service_type, problem_description, agree) 
            VALUES (:name, :phone, :email, :service_type, :problem_description, :agree)";
    
    file_put_contents('debug_log.txt', "🔧 SQL запрос: $sql\n", FILE_APPEND);
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':service_type' => $service_type,
        ':problem_description' => $problem_description,
        ':agree' => $agree
    ]);

    if ($result) {
        $lastId = $pdo->lastInsertId();
        file_put_contents('debug_log.txt', "✅ Данные сохранены! ID записи: $lastId\n", FILE_APPEND);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Спасибо! Ваша заявка принята. Мы свяжемся с вами в ближайшее время.'
        ]);
    } else {
        throw new Exception('Не удалось сохранить данные в базу');
    }

} catch (PDOException $e) {
    $error_msg = "❌ Ошибка базы данных: " . $e->getMessage();
    file_put_contents('debug_log.txt', $error_msg . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Ошибка при сохранении данных. Попробуйте позже.'
    ]);
} catch (Exception $e) {
    $error_msg = "❌ Общая ошибка: " . $e->getMessage();
    file_put_contents('debug_log.txt', $error_msg . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Произошла ошибка. Попробуйте позже.'
    ]);
}

file_put_contents('debug_log.txt', "🏁 Конец обработки\n\n", FILE_APPEND);
?>