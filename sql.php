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

    public function setSingleNews($title, $content) {
      global $connection;
      $sql = "INSERT INTO news (title, content) VALUES (:title, :content)";
      $query= $connection->prepare($sql);
      $query->bindParam('title', $title, PDO::PARAM_STR);
      $query->bindParam('content', $content, PDO::PARAM_STR);
      $query->execute();
    }

    public function updateSingleNews($id, $title, $content) {
      global $connection;
      $sql = "UPDATE news SET title = :title, content = :content WHERE id = :id";
      $query= $connection->prepare($sql);
      $query->bindParam('id', $id, PDO::PARAM_INT);
      $query->bindParam('title', $title, PDO::PARAM_STR);
      $query->bindParam('content', $content, PDO::PARAM_STR);
      $query->execute();
    }

    public function statusSingleNews($id, $status) {
      var_dump($id, $status);die();
      global $connection;
      $sql = "UPDATE news SET status = :status WHERE id = :id";
      $query= $connection->prepare($sql);
      $query->bindParam('id', $id, PDO::PARAM_INT);
      $query->bindParam('status', $status, PDO::PARAM_INT);
      $query->execute();
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

    public function deleteSingleNews($id) {
      global $connection;
      $sql = "DELETE FROM news WHERE id = :id";
      $query = $connection->prepare($sql);
      $query->bindParam('id', $id, PDO::PARAM_INT);
      $query->execute();
    }
  }
?>
