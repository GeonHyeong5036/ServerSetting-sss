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
        $stmt = $this->con->prepare("INSERT into users (kakaoId, name, member) values (?, ?, ?)");
        $stmt->bind_param("sss", $kakaoId, $name, $member);
        if($stmt->execute()){
          return USER_CREATED;
        }else{
          return USER_FAILURE;
        }
      }
      return USER_EXISTS;
    }

    private function isKakaoIdExist($kakaoId){
      $stmt = $this->con->prepare("SELECT id from users where (kakaoId = ? and member = 1)");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }

    private function getIdByKakaoId($kakaoId){
      $stmt = $this->con->prepare("SELECT id from users where kakaoId = ?");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

    public function createFriend($userKakaoId, $friendKakaoId){
      $userId = getIdByKakaoId($userKakaoId);
      $friendId = getIdByKakaoId($friendKakaoId);
      $stmt = $this->con->prepare("INSERT into friendRelationShip (userId, friendId) values (?, ?)");
      $stmt->bind_param("ss", $userId, $friendId);
      if($stmt->execute()){
        return FRIEND_CREATED;
      }else{
        return FRIEND_FAILURE;
      }
    }
  }
