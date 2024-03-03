<?php
  // header('Access-Control-Allow-Origin: *');
  // header('Access-Control-Allow-Headers: Overwrite, Destination, Depth, User-Agent, X-File-Size, If-Modified-Since, X-File-Name, Cache-Control, Origin, Content-Type, Content-Length, Authorization, X-Custom-Header, X-Requested-With, X-Auth-Token, Accept, Key, api-csrf');
  // header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: *");
  header("Access-Control-Allow-Methods: *");

  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
      header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
      header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    }
  }
?>
