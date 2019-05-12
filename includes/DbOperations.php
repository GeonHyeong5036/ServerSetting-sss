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
      $userId = $this->getIdByKakaoId($userKakaoId);
      $friendId = $this->getIdByKakaoId($friendKakaoId);
      if($userId==null || $friendId==null){
        return FRIEND_MISSING;
      }else if($userId==$friendId){
        return FRIEND_SAME;
      }

      if(!$this->isFriendShipExist($userId, $friendId)){
        $stmt = $this->con->prepare("INSERT into friendRelationShip (userId, friendId) values (?, ?)");
        $stmt->bind_param("ii", $userId, $friendId);
        if($stmt->execute()){
          return FRIEND_CREATED;
        }else{
          return FRIEND_FAILURE;
        }
      }
      return FRIEND_EXISTS;
    }

    private function isFriendShipExist($userId, $friendId){
      $stmt1 = $this->con->prepare("SELECT * from friendRelationShip where (userId = ? and friendId = ?)");
      $stmt1 ->bind_param("ii", $userId, $friendId);
      $stmt1 ->execute();
      $stmt1 ->store_result();

      $stmt2 = $this->con->prepare("SELECT * from friendRelationShip where (userId = ? and friendId = ?)");
      $stmt2 ->bind_param("ii", $friendId, $userId);
      $stmt2 ->execute();
      $stmt2 ->store_result();

      return (($stmt1->num_rows > 0) || ($stmt2->num_rows > 0));
    }
  }
