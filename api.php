<?php
    require_once './debug.php';
    require_once './config.php';
    require_once './db.php';
    require_once './jwt.php';
    require_once './funcs.php';

    //Processing API requests
    if($_SERVER['REQUEST_METHOD'] == 'POST') {     
        //Check and process entered data
        $data = json_decode(file_get_contents('php://input'), true);
        if($data) {
            $email = isset($data['email']) ? $data['email'] : '';
            $password = isset($data['password']) ? $data['password'] : '';

            if (!$data || empty($data['email']) || empty($data['password'])) {
                echo createResponse('error', 'Missing required fields.', []);
                exit;
            }

            $email_hash = base64_encode($data['email']);
            $password = $data['password'];
        
            $sql = "SELECT * FROM requests WHERE email = '$email_hash'";
            $query = $connection->prepare($sql);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            
            $password_hash = $row['password'];

            if(password_verify($password, $password_hash)) {
                $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
                $payload = ['user' => $row['username']];
                $jwt = generate_jwt($headers, $payload);

                echo createResponse('success', 'Logged in successfully.', ['token' => $jwt]);
            } else {
                echo createResponse('error', "Incorrect login information.", []);
                exit;
            }
        } 
        
        else {
            echo createResponse('error', 'Wrong POST request.', []);
            exit;
        }
    } else if($_SERVER['REQUEST_METHOD'] == 'GET') {
        $bearer_token = get_bearer_token();
        $is_jwt_valid = isset($bearer_token) ? is_jwt_valid($bearer_token) : false;

        if ($is_jwt_valid) {
            $email = getPayload($bearer_token);
            echo($email);die();

            $sql = "SELECT * FROM requests WHERE username = '$email'";
            $query = $connection->prepare($sql);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($user = $database->getUserByUsernameOrEmail($row['username'])) {
                echo createResponse('success', 'Logged in successfully.', ['user' => $user]);
            }
        } else {
            echo createResponse('error', 'Wrong GET request.', []);
            exit;
        }
    }
?>
