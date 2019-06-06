<?php
  class DbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createUser($kakaoId, $name, $profileImagePath, $member){
      if(!$this->isKakaoIdExist($kakaoId)){
        $stmt = $this->con->prepare("INSERT into users (kakaoId, name, profileImagePath) values (?, ?, ?)");
        $stmt->bind_param("sssi", $kakaoId, $name, $profileImagePath);
        if($stmt->execute()){
          return USER_CREATED;
        }else{
          return USER_FAILURE;
        }
      }else if($this->updateUser($kakaoId, $name, $profileImagePath, $member)){
          return USER_UPDATE;
      }
      return USER_NOT_MEMBER;
    }

    private function isKakaoIdExist($kakaoId){
      $stmt = $this->con->prepare("SELECT id from users where kakaoId = ?");
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

    private function updateUser($kakaoId, $name, $profileImagePath, $member){
      if($member == '1'){
        $stmt = $this->con->prepare("UPDATE users SET name = ?, profileImagePath = ?, member = 1 WHERE kakaoId = ?");
        $stmt->bind_param("sss", $name, $profileImagePath, $kakaoId);
        $stmt->execute();
        return true;
      }
      return false;
    }

    public function getUser($kakaoId){
        $stmt = $this->con->prepare("SELECT id, kakaoId, name, profileImagePath, member FROM users where kakaoId = ?");
        $stmt->bind_param("s", $kakaoId);
        $stmt->execute();
        $stmt->bind_result($id, $kakaoId, $name, $profileImagePath, $member);
        $stmt->fetch();
        $user = array();
        $user['id'] = $id;
        $user['kakaoId']=$kakaoId;
        $user['name'] = $name;
        $user['profileImagePath'] = $profileImagePath;
        $user['member'] = $member;
        return $user;
    }

    public function createFriend($userKakaoId, $friendKakaoId){
      $userId = $this->getIdByKakaoId($userKakaoId);
      $friendId = $this->getIdByKakaoId($friendKakaoId);
      if($userId==null || $friendId==null){
        return USERID_MISSING;
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
      $stmt = $this->con->prepare("SELECT * from friendRelationShip where (userId = ? and friendId = ?) or (userId = ? and friendId = ?)");
      $stmt ->bind_param("iiii", $userId, $friendId, $friendId, $userId);
      $stmt ->execute();
      $stmt ->store_result();

      return ($stmt->num_rows > 0);
    }
  }
