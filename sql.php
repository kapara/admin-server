<?php
  function getUserByUsername($username) {
    global $connection;
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $query = $connection->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
        
    return $row["username"];
  }

  function getUserByEmail($email_hash) {
    global $connection;
    $sql = "SELECT * FROM users WHERE email = '$email_hash'";
    $query = $connection->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    
    return $row;
  }
?>
