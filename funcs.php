<?php

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
  ?>
