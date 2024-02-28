<<?php
    require_once './debug.php';
    require_once './config.php';
    require_once './db.php';
    require_once './sql.php';

    $sql = new SQL();

    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'POST':
          echo $funcs->createResponse('error', 'Wrong POST request.', []);
          exit;
        break;
        case 'GET':
          $news = $sql->getNews();
          if ($news) {
            echo $funcs->createResponse('success', 'Logged in successfully.', ['news' => $news]);
          } else {
            echo $funcs->createResponse('error', 'Wrong GET request.', []);
            exit;
          }
        break;
        default:
          echo $funcs->createResponse('error', 'Wrong GET request.', []);
          exit;
    }
?>
