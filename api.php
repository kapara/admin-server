<?php
    require_once './debug.php';
    require_once './config.php';
    require_once './db.php';
    require_once './jwt.php';
    require_once './funcs.php';

    if(!checkRequestLimit($_SERVER['REMOTE_ADDR'])) {
        echo createResponse('error', 'Too many requests! Try again later.', []);
        exit;
    }

    if(!checkRequestTime($_SERVER['REMOTE_ADDR'])) {
        echo createResponse('error', 'Request too common! Try again later.', []);
        exit;
    }

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
                $payload = ['user' => $user];
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
            $username = getPayload($bearer_token);
            echo($username);die();

            $sql = "SELECT * FROM requests WHERE username = '$username'";
            $query = $connection->prepare($sql);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($user = $database->getUserByUsernameOrEmail($username)) {
                echo createResponse('success', 'Logged in successfully.', ['user' => $row[$user['username']]]);
            }
        } else {
            echo createResponse('error', 'Wrong GET request.', []);
            exit;
        }
    }
?>
