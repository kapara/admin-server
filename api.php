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
    $upload_directory = './upload/';
    $root_path = 'https://api.albitmax.cz/api.php?upload/';

    if($method == 'POST') {
        $url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $page = parse_url($url, PHP_URL_QUERY);

        $data = $_FILES ? $_FILES : json_decode(file_get_contents('php://input'), true);

        if(!$data) {
            echo $funcs->createResponse('error', 'Missing required fields.', []);
            exit;
        }
        
        switch($page) {
            case 'deleteNews':
                $id = isset($data['id']) ? (int)$data['id'] : '';

                if (!empty($id)) {
                    $new = $sql->deleteSingleNews($id);
                    echo $funcs->createResponse('success', 'News #'.$id.' was deleted');
                    exit;
                }

                echo $funcs->createResponse('error', 'Missing required fields.', []);
                exit;
            break;
            case 'updateNews':    
                $id = isset($data['id']) ? (int)$data['id'] : '';
                $title = isset($data['title']) ? $data['title'] : '';
                $content = isset($data['content']) ? $data['content'] : '';
                $status = isset($data['status']) ? (int)$data['status'] : '';

                if (!empty($id)) {
                    $new = $sql->updateSingleNews($id, $title, $content, $status);
                    echo $funcs->createResponse('success', 'News #'.$id.' was updated');
                    exit;
                }

                echo $funcs->createResponse('error', 'Missing required fields.', []);
                exit;
            break;
            case 'createNews':  
                $title = isset($data['title']) ? $data['title'] : '';
                $content = isset($data['content']) ? $data['content'] : '';

                if (!empty($title) && !empty($content)) {
                    $new = $sql->setSingleNews($title, $content);
                    echo $funcs->createResponse('success', 'News was created');
                    exit;
                }

                echo $funcs->createResponse('error', 'Missing required fields.', []);
                exit;
            break;
            case 'uploadImage':
                $data = isset($data) ? $data : '';
                $file_name_array = explode(".", $data['file']['name']);
                $file_name = time() . '.' . end($file_name_array);
                
                $upload_file = $upload_directory . $file_name;
                $image_link = $root_path . $file_name;
                
                if(!file_exists($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }
                
                if(move_uploaded_file($data['file']['tmp_name'], $upload_file)) {
                    echo json_encode([
                        'message' => 'File uploaded successfully', 
                        'image_link' => $image_link
                    ]);
                }                
            break;
            case 'auth':
                $email = isset($data['email']) ? $data['email'] : '';
                $password = isset($data['password']) ? $data['password'] : '';
    
                if (empty($data['email']) || empty($data['password'])) {
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
            break;
            default: 
                echo $funcs->createResponse('error', 'Wrong POST request.', []);
                exit;
        }
    } else if ($method == 'GET') {
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
                            $news = $sql->getNewById($id[1]);
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
    } else {
        echo $funcs->createResponse('error', 'Global wrong GET request.', []);
        exit;
    }
?>
