<?php
  class DbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createUser($imageURL, $name){
      if(!$this->isImageURLExist($imageURL)){
        $stmt = $this->con->prepare("insert into users (imageURL, name) values (?, ?)");
        $stmt->bind_param("ss", $imageURL, $name);
        if($stmt->execute()){
          return USER_CREATED;
        }else{
          return USER_FAILURE;
        }
      }
      return USER_EXISTS;
    }

    private function isImageURLExist($imageURL){
      $stmt = $this->con->prepare("select userId from users where email = ?");
      $stmt->bind_param("s", $imageURL);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }
  }
