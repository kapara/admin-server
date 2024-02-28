<?php  
    $config = array(
        'db_hostname' => 'localhost',
        'db_name' => 'm120341_admin',
        'db_username' => 'm120341_admin',
        'db_password' => '?YoKhF?XqR^f',
    );

    try {
        $connection = new PDO("mysql:host=" . $config['db_hostname'] . ";dbname=" . $config['db_name'], 
            $config['db_username'], 
            $config['db_password'], 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 

    catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
?>
