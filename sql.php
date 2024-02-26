<?php
  function getUserByUsername($username) {
    global $connection;
    $sql = "SELECT * FROM requests WHERE username = '$username'";
    $query = $connection->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);

    $user = Array(
        'email' => $row->email,
        'username' => $row->username,
    );
    
    return $user;
  }

  function getUserByEmail($email_hash) {
    global $connection;
    $sql = "SELECT * FROM requests WHERE email = '$email_hash'";
    $query = $connection->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    
    return $row;
  }
?>
