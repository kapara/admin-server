<?php
  class Funcs {
    //Output values
    public function createResponse($status, $message, $data = []) {
      $response = [
          'status' => $status,
          'message' => $message,
          'data' => $data
      ];
      
      return json_encode($response);
    }

    public function validateInput($input) {
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

    //Encrypt
    public function xorEncrypt($input) {
      return base64_encode($input);
    }
  }    
?>
