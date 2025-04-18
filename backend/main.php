<?php
//Итак вот мой код на чистом php. Всё по api эндпоинтам. Пока мне нужно время чтобы разобраться с laravel. Пусть этот код побудет здесь.
//В ходе выполнения у меня кажется появились вопросы по поводу проекта, собственно из-за которых я не стал сразу делать на laravel. Думаю это следует обсудить, если будет на то время.
$connect = new mysqli("localhost","-","-","-");

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
                $connect->close();
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
            $connect->close();
        
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
            
            $connect->close();
        }
        else if ($request[0] == 'get-slots') {
            if($_POST['password'] == "master123") {
                        
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
            $connect->close();
                
            }
            else {
            http_response_code(401);
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

//    Таблица slots:
//       id (int, автоинкремент).
//       time (varchar, "14:00").
//       status (varchar, "Свободно" или "Занято").
//       name (varchar, nullable).
//       phone (varchar, nullable).
//       service (varchar, nullable).
#$connect = new mysqli("localhost", "semechra_pbtwo", "SdtQ2012s0!qqq", "semechra_pbtwo");

?>
