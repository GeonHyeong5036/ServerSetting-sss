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
        $stmt = $this->con->prepare("INSERT into users (kakaoId, name, profileImagePath, member) values (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $kakaoId, $name, $profileImagePath, $member);
        if($stmt->execute()){
          return USER_CREATED;
        }else{
          return USER_FAILURE;
        }
      }else if($this->updateUser($kakaoId, $name, $profileImagePath, $member)){
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

    private function updateUser($kakaoId, $name, $profileImagePath, $member){
      if($member == '1'){
        $stmt = $this->con->prepare("UPDATE users SET name = ?, profileImagePath = ?, member = 1 WHERE kakaoId = ?");
        $stmt->bind_param("sss", $name, $profileImagePath, $kakaoId);
        $stmt->execute();
        return true;
      }
      $stmt = $this->con->prepare("UPDATE users SET name = ?, profileImagePath = ? WHERE kakaoId = ?");
      $stmt->bind_param("sss", $name, $profileImagePath, $kakaoId);
      $stmt->execute();
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

    public function createTimeTable($kakaoId, $type, $title, $place, $cellPosition){
      $userId = $this->getIdByKakaoId($kakaoId);
      if($userId==null){
        return USERID_MISSING;
      }
      if(!$this->isTimeTableExist($userId, $type, $title, $place, $cellPosition)){
        $stmt = $this->con->prepare("INSERT into timeTable (userId, type, title, place, cellPosition) values (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $userId, $type, $title, $place, $cellPosition);
        if($stmt->execute()){
          return TIMETABLE_CREATED;
        }else{
          return TIMETABLE_FAILURE;
        }
      }
      return TIMETABLE_EXISTS;
    }

    public function updateTimeTable($kakaoId, $type, $title, $place, $cellPosition){
      $stmt = $this->con->prepare("UPDATE timeTable SET type = ?, title = ?, place = ? WHERE id IN (SELECT id from timeTable where userId IN (SELECT id from users where kakaoId = ?) and cellPosition = ?);");
      $stmt->bind_param("ssssi", $type, $title, $place, $kakaoId, $cellPosition);
      if($stmt->execute())
        return true;
      return false;
    }

    // private function getIdByUserIdAtTimeTable($userId, $cellPosition){
    //   $stmt = $this->con->prepare("SELECT id from timeTable where userId = ? and cellPosition = ?;");
    //   $stmt->bind_param("ii", $userId, $cellPosition);
    //   $stmt->execute();
    //   $stmt->bind_result($id);
    //   $stmt->fetch();
    //   return $id;
    // }

    public function getTimeTables($kakaoId){
      $userId = $this->getIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT id, userId, type, title, place, cellPosition FROM timeTable where userId = ? order by cellPosition");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->bind_result($id, $userId, $type, $title, $place, $cellPosition);

      $timeTables = array();
      while($stmt->fetch()){
        $timeTable = array();
        $timeTable['id'] = $id;
        $timeTable['userId']=$userId;
        $timeTable['type']=$type;
        $timeTable['title']=$title;
        $timeTable['place']=$place;
        $timeTable['cellPosition'] = $cellPosition;
        array_push($timeTables, $timeTable);
      }
      return $timeTables;
    }

    public function deleteTimeTable($kakaoId, $cellPosition){
      $userId = $this->getIdByKakaoId($kakaoId);
      if($userId==null){
        return false;
      }

      $stmt = $this->con->prepare("DELETE FROM timeTable WHERE userId = ? and cellPosition = ?");
      $stmt->bind_param("ii", $userId, $cellPosition);
      if($stmt->execute())
        return true;
      return false;
    }

    public function deleteAllTimeTable($kakaoId){
      $stmt = $this->con->prepare("DELETE FROM timeTable WHERE userId In(select id from users where kakaoId = ?)");
      $stmt->bind_param("s", $kakaoId);
      if($stmt->execute())
        return true;
      return false;
    }

    private function isTimeTableExist($userId, $type, $title, $place, $cellPosition){
      $stmt = $this->con->prepare("SELECT id from timeTable where ((userId = ?) and  (type = ?) and (title = ?) and (place = ?) and (cellPosition = ?))");
      $stmt->bind_param("isssi", $userId, $type, $title, $place, $cellPosition);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }

    // public function createGroup($kakaoId, $title, $place, $cellPosition){
    //   $userId = $this->getIdByKakaoId($kakaoId);
    //   if($userId==null){
    //     return USERID_MISSING;
    //   }
    //   if(!$this->isTimeTableExist($userId, $title, $place, $cellPosition)){
    //     $stmt = $this->con->prepare("INSERT into timeTable (userId, title, place, cellPosition) values (?, ?, ?, ?)");
    //     $stmt->bind_param("issi", $userId, $title, $place, $cellPosition);
    //     if($stmt->execute()){
    //       return TIMETABLE_CREATED;
    //     }else{
    //       return TIMETABLE_FAILURE;
    //     }
    //   }
    //   return TIMETABLE_EXISTS;
    // }



  }
