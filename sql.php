<?php
  class SQL {
    public function getUserByUsername($username) {
      global $connection;
      $sql = "SELECT * FROM users WHERE username = :username";
      $query = $connection->prepare($sql);
      $query->bindParam('username', $username, PDO::PARAM_STR);
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
          
      return $row["username"];
    }

    public function getUserByEmail($email_hash) {
      global $connection;
      $sql = "SELECT * FROM users WHERE email = :email_hash";
      $query = $connection->prepare($sql);
      $query->bindParam('email_hash', $email_hash, PDO::PARAM_STR);
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

    public function getNewById($id) {
      global $connection;
      $sql = "SELECT * FROM news WHERE id = :id";
      $query = $connection->prepare($sql);
      $query->bindParam('id', $id, PDO::PARAM_INT);
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
      
      return $row;
    }
  }
?>
