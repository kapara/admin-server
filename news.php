<<?php
    require_once './debug.php';
    require_once './config.php';
    require_once './db.php';
    require_once './funcs.php';
    require_once './sql.php';

    $sql = new SQL();
    $funcs = new Funcs();

    $method = $_SERVER['REQUEST_METHOD'];
    var_dump($method);die();

    switch($method) {
        case 'POST':
          echo $funcs->createResponse('error', 'Wrong POST request.', []);
          exit;
        break;
        case 'GET':
          $news = $sql->getNews();
          
          if ($news) {
            echo $funcs->createResponse('success', 'Response', ['news' => $news]);
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
