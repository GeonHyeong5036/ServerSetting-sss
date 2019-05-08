<?php
  class DbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createUser($kakaoId, $name){
      if(!$this->isKakaoIdExist($kakaoId)){
        $stmt = $this->con->prepare("insert into users (kakaoId, name) values (?, ?)");
        $stmt->bind_param("ss", $kakaoId, $name);
        if($stmt->execute()){
          return USER_CREATED;
        }else{
          return USER_FAILURE;
        }
      }
      return USER_EXISTS;
    }

    private function isKakaoIdExist($imageURL){
      $stmt = $this->con->prepare("select id from users where kakaoId = ?");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }
  }
