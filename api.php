<?php
    require_once './debug.php';
    require_once './config.php';
    require_once './db.php';
    require_once './jwt.php';
    require_once './funcs.php';
    require_once './sql.php';

    $funcs = new Funcs();
    $sql = new SQL();
    $jwt = new JWT();

    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if($data) {
                $email = isset($data['email']) ? $data['email'] : '';
                $password = isset($data['password']) ? $data['password'] : '';
    
                if (!$data || empty($data['email']) || empty($data['password'])) {
                    echo $funcs->createResponse('error', 'Missing required fields.', []);
                    exit;
                }
    
                $email_hash = base64_encode($data['email']);
                $password = $data['password'];
            
                $user = $sql->getUserByEmail($email_hash);
                
                $password_hash = $user['password'];
    
                if(password_verify($password, $password_hash)) {
                    $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
                    $payload = ['user' => $user['username']];
                    $token = $jwt->generate_jwt($headers, $payload);
    
                    echo $funcs->createResponse('success', 'Logged in successfully.', ['token' => $token]);
                } else {
                    echo $funcs->createResponse('error', "Incorrect login information.", []);
                    exit;
                }
            } 
            
            else {
                echo $funcs->createResponse('error', 'Wrong POST request.', []);
                exit;
            }
        break;
        case 'GET':
            $url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $query_str = parse_url($url, PHP_URL_QUERY);
            $res = parse_str($query_str, $query_params);

            echo var_dump($res);die();

            $bearer_token = $jwt->get_bearer_token();
            $is_jwt_valid = isset($bearer_token) ? $jwt->is_jwt_valid($bearer_token) : false;
    
            if ($is_jwt_valid) {
                $username = $jwt->getPayload($bearer_token);
    
                // echo createResponse('debug', 'response', ['username' => $username]);
    
                $user = $sql->getUserByUsername($username->user);
                if ($user) {
                    echo $funcs->createResponse('success', 'Logged in successfully.', ['user' => $user]);
                }
            } else {
                echo $funcs->createResponse('error', 'Wrong GET request.', []);
                exit;
            }
        break;
        default:
            echo $funcs->createResponse('error', 'Global wrong GET request.', []);
            exit;
    }
?>
