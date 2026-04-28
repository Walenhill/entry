<?php
// Загрузка переменных окружения из .env файла
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Подключение к базе данных через переменные окружения
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_DATABASE') ?: 'booking_db';

$connect = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($connect->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $connect->connect_error]);
    exit();
}

$request = explode('/', $_GET['q']); //Запрос делится по частям в массив

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':#GET запросы
        if ($request[0] == 'get-slots') //api/get-slots    с фильтром: ?role=master — все слоты ?role=client — только свободные. // Для /master добавь проверку пароля (хардкод 'master123').
        { 
            if ($request[1] == 'master')
            {
                include('auth.html');
                exit();
            } 
            elseif ($request[1] == 'client')
            {
                header('Content-type: application/json');
              
                $check = $connect->prepare("SELECT time, status FROM slots WHERE status = 'Свободно'");
                $check->execute();
                $slots = $check->get_result();
                $slotList = [];
                while ($slot = mysqli_fetch_assoc($slots)) {
                    $slotList[] = $slot;
                }
                echo json_encode($slotList); //Возвращает: [{ "time": "14:00", "status": "Свободно" }, { "time": "15:00", "status": "Занято" }] (JSON).
                $slots->free();
            }
        }
        break;
    case 'POST':#POST запросы
        if ($request[0] == 'add-slot') //POST /api/add-slot
        {
            header('Content-type: application/json');
            //Принимает: { "time": "17:00" } (JSON).
            //Действие: сохраняет слот в базу как "Свободно".
            
            $time = $_POST['time'];
            
            $add_slot = $connect->prepare("INSERT INTO slots(`time`, `status`) VALUES( ? , 'Свободно')");
            $add_slot->bind_param("s", $time);
            $add_slot->execute();

            http_response_code(201);
            
            $info = 
            ["success" => true, "slot" => ["time" => $time, "status" => "Свободно"] ];
            
            echo json_encode($info); //Возвращает: { "success": true, "slot": { "time": "17:00", "status": "Свободно" } }.

            $add_slot->free();
        
        }
        else if ($request[0] == 'book-slot')  //POST /api/book-slot
        {
            header('Content-type: application/json');
            //Принимает: { "time": "14:00", "name": "Маша", "phone": "+79991234567", "service": "Шугаринг ног" } (JSON).
            //Действие: меняет статус слота на "Занято", сохраняет данные клиента.

            $time = $_POST['time'];
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $service = $_POST['service'];
            
            $book_slot = $connect->prepare("INSERT INTO slots (`time`, `status`, `name`, `phone`, `service`) VALUES (?, 'Занято', ?, ?, ?)");
            $book_slot->bind_param("ssss", $time, $name, $phone, $service);
            
            $book_slot->execute();
            
            http_response_code(201);
            
            $info = ["success" => true, "slot" => ["time" => $time, "status" => "Занято", "name" => $name, "phone" => $phone, "service" => $service] ];
            echo json_encode($info); //Возвращает: { "success": true, "slot": { "time": "14:00", "status": "Занято", "name": "Маша", "phone": "+79991234567", "service": "Шугаринг ног" } }.

            $book_slot->close();
        }
        else if ($request[0] == 'get-slots') {
            // Получаем пароль из переменной окружения
            $masterPassword = getenv('MASTER_PASSWORD') ?: 'master123';
            
            if($_POST['password'] == $masterPassword) {
                        
                header('Content-type: application/json');
                    
                $check = $connect->prepare("SELECT time, status FROM slots");
                $check->execute();
                $slots = $check->get_result();
                $slotList = [];
                while ($slot = mysqli_fetch_assoc($slots)) {
                    $slotList[] = $slot;
                }
                echo json_encode($slotList); //Возвращает: [{ "time": "14:00", "status": "Свободно" }, { "time": "15:00", "status": "Занято" }] (JSON).
                $slots->free();
            }
            else {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
            }
        }
        break;
    case 'PUT':
        //
        break;
    case 'DELETE':
        //
        break;
}

$connect->close();
?>
