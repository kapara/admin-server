<?php
  class SQL {
    public function getUserByUsername($username) {
      global $connection;
      $sql = "SELECT * FROM users WHERE username = '$username'";
      $query = $connection->prepare($sql);
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
          
      return $row["username"];
    }

    public function getUserByEmail($email_hash) {
      global $connection;
      $sql = "SELECT * FROM users WHERE email = '$email_hash'";
      $query = $connection->prepare($sql);
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
      
      return $row;
    }

    public function getNews() {
      global $connection;
      $sql = "SELECT * FROM news";
      $query = $connection->prepare($sql);
      $query->execute();
      $rows = $query->fetchAll(PDO::FETCH_ASSOC);
      
      return $rows;
    }
  }
?>
