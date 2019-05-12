<?php
  class DbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createUser($kakaoId, $name, $member){
      if(!$this->isKakaoIdExist($kakaoId)){
        $stmt = $this->con->prepare("INSERT into users (kakaoId, name, member) values (?, ?, ?)");
        $stmt->bind_param("sss", $kakaoId, $name, $member);
        if($stmt->execute()){
          return USER_CREATED;
        }else{
          return USER_FAILURE;
        }
      }else if($this->isMemberAlready($kakaoId, $member)){
          return USER_UPDATE;
      }
      return USER_EXISTS;
    }

    private function isKakaoIdExist($kakaoId){
      $stmt = $this->con->prepare("SELECT id from users where kakaoId = ?");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }

    private function isMemberAlready($kakaoId, $member){
      if($member == '1'){
        $stmt = $this->con->prepare("UPDATE users SET member = 1 WHERE kakaoId = ?");
        $stmt->bind_param("s", $kakaoId);
        $stmt->execute();
        return true;
      }
      return false;
    }

    private function getIdByKakaoId($kakaoId){
      $stmt = $this->con->prepare("SELECT id from users where kakaoId = ?");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

    public function getUser($kakaoId){
        $stmt = $this->con->prepare("SELECT id, kakaoId, name, member FROM users where kakaoId = ?");
        $stmt->bind_param("s", $kakaoId);
        $stmt->execute();
        $stmt->bind_result($id, $kakaoId, $name, $member);
        $stmt->fetch();
        $user = array();
        $user['id'] = $id;
        $user['kakaoId']=$kakaoId;
        $user['name'] = $name;
        $user['member'] = $member;
        return $user;
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
