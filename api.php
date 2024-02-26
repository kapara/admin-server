<?php
    require_once './config.php';
    require_once './db.php';
    require_once './jwt.php';

    $bearer_token = get_bearer_token();
    $is_jwt_valid = isset($bearer_token) ? is_jwt_valid($bearer_token) : false;

    //Output values
    function createResponse($status, $message, $data = []) {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        
        return json_encode($response);
    }

    function validateInput($input) {
        //SQL Injection protection
        if(preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $input)) {
            return false;
        }

        // XSS protection
        if(preg_match('/<[^>]*>/', $input)) {
            return false;
        }

        return true;
    }

    //Brute force protection - Limit requests
    function checkRequestLimit($ip_address) {
        global $connection;
        $query = $connection->prepare("SELECT COUNT(*) FROM requests 
        WHERE ip_address = :ip_address AND request_time > DATE_SUB(NOW(), 
        INTERVAL 1 HOUR)");
        $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //Maximum 100 requests/hour
        if($result['COUNT(*)'] > 100) { 
            return false;
        }

        return true;
    }

    //Limitation of access time
    function checkRequestTime($ip_address) {
        global $connection;
        $query = $connection->prepare("SELECT request_time FROM requests 
        WHERE ip_address = :ip_address 
        ORDER BY request_time 
        DESC LIMIT 1");
        $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        if($result) {
            $last_request_time = strtotime($result['request_time']);
            $current_time = strtotime(date('Y-m-d H:i:s'));
            
            if($current_time - $last_request_time < 1) {
                return false;
            }
        }

        return true;
    }

    //Encrypt
    function xorEncrypt($input) {
        return base64_encode($input);
    }

    //Processing API requests
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        if(!checkRequestLimit($_SERVER['REMOTE_ADDR'])) {
            echo createResponse('error', 'Too many requests! Try again later.', []);
            exit;
        }

        if(!checkRequestTime($_SERVER['REMOTE_ADDR'])) {
            echo createResponse('error', 'Request too common! Try again later.', []);
            exit;
        }

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
            }
            
            else {
                echo createResponse('error', "Incorrect login information.", []);
                exit;
            }
        } 
        
        else {
            echo createResponse('error', 'Wrong request.', []);
            exit;
        }
    } else if($_SERVER['REQUEST_METHOD'] == 'GET') {
        if(!checkRequestLimit($_SERVER['REMOTE_ADDR'])) {
            echo createResponse('error', 'Too many requests! Try again later.', []);
            exit;
        }

        if(!checkRequestTime($_SERVER['REMOTE_ADDR'])) {
            echo createResponse('error', 'Request too common! Try again later.', []);
            exit;
        }

        //Check and process entered data
        $data = json_decode(file_get_contents('php://input'), true);
        if($data) {
            if ($is_jwt_valid) {
                $username = getPayload($bearer_token)->user->username;

                $sql = "SELECT * FROM requests WHERE email = '$email_hash'";
                $query = $connection->prepare($sql);
                $query->execute();
                $row = $query->fetch(PDO::FETCH_ASSOC);

                if ($user = $database->getUserByUsernameOrEmail($username)) {
                    echo createResponse('success', 'Logged in successfully.', ['user' => $row[$user['username']]]);
                }
            }
        } else {
            echo createResponse('error', 'Wrong request.', []);
            exit;
        }
    }
?>
