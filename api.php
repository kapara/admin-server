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
            $query = parse_url($url, PHP_URL_QUERY);
            $pieces = explode("&", $query);

            if (count($pieces) > 0) {          
                $page = isset($pieces[0]) ? $pieces[0] : null;
                $id = isset($pieces[1]) ? explode("id=", $pieces[1]) : [];

                if (!is_null($page)) {
                    switch ($page) {
                        case 'user':
                            $bearer_token = $jwt->get_bearer_token();
                            $is_jwt_valid = isset($bearer_token) ? $jwt->is_jwt_valid($bearer_token) : false;
                    
                            if ($is_jwt_valid) {
                                $username = $jwt->getPayload($bearer_token);                                
                                $user = $sql->getUserByUsername($username->user);
                                
                                if ($user) {
                                    echo $funcs->createResponse('success', 'Logged in successfully.', ['user' => $user]);
                                }
                            } else {
                                echo $funcs->createResponse('error', 'Wrong GET request.', []);
                                exit;
                            }
                        break;
                        case 'news':
                            if (count($id) > 0) {
                                $news = $sql->getNewById($id[0]);
                                echo $funcs->createResponse('success', 'Response', ['news' => $news]);
                            } else {
                                $news = $sql->getNews();
                                echo $funcs->createResponse('success', 'Response', ['news' => $news]);
                            }
                        break;
                        default:
                            echo $funcs->createResponse('error', 'Global wrong GET request.', []);
                            exit;
                    }
                }
            } else {
                echo $funcs->createResponse('error', 'Global wrong GET request.', []);
                exit;
            }
        break;
        default:
            echo $funcs->createResponse('error', 'Global wrong GET request.', []);
            exit;
    }
?>
