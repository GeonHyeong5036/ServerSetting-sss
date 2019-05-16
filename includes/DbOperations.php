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

    public function createCourse($kakaoId, $title, $place, $sellPosition){
      $userId = $this->getIdByKakaoId($kakaoId);
      if($userId==null){
        return USERID_MISSING;
      }
      if(!$this->isCourseExist($userId, $title, $place, $sellPosition)){
        $stmt = $this->con->prepare("INSERT into course (userId, title, place, sellPosition) values (?, ?, ?, ?)");
        $stmt->bind_param("issi", $userId, $title, $place, $sellPosition);
        if($stmt->execute()){
          return COURSE_CREATED;
        }else{
          return COURSE_FAILURE;
        }
      }
      return COURSE_EXISTS;
    }

    public function updateCourse($kakaoId, $title, $place, $sellPosition){
      $userId = $this->getIdByKakaoId($kakaoId);
      if($userId==null){
        return USERID_MISSING;
      }
      $stmt = $this->con->prepare("UPDATE course SET title = ?, place = ?, sellPosition = ? WHERE userId =?");
      $stmt->bind_param("ssii", $title, $place, $sellPosition, $userId);
      if($stmt->execute())
        return true;
      return false;
    }

    public function getCourse($kakaoId){
      $userId = $this->getIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT id, userId, title, place, sellPosition FROM course where userId = ? order by sellPosition");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->bind_result($id, $userId, $title, $place, $sellPosition);
      $stmt->fetch();
      $courses = array();
      while($stmt->fetch()){
        $course = array();
        $course['id'] = $id;
        $course['userId']=$userId;
        $course['title']=$title;
        $course['place']=$place;
        $course['sellPosition'] = $sellPosition;
        array_push($courses, $course);
      }
      return $courses;
    }

    public function deleteCourse($kakaoId, $sellPosition){
      $userId = $this->getIdByKakaoId($kakaoId);
      if($userId==null){
        return USERID_MISSING;
      }
      $stmt = $this->con->prepare("DELETE FROM course WHERE userId = ? and sellPosition = ?");
      $stmt->bind_param("ii", $userId, $sellPosition);
      if($stmt->execute())
        return true;
      return false;
    }

    private function isCourseExist($userId, $title, $place, $sellPosition){
      $stmt = $this->con->prepare("SELECT id from course where ((userId = ?) and (title = ?) and (place = ?) and (sellPosition = ?))");
      $stmt->bind_param("issi", $userId, $title, $place, $sellPosition);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }

    public function createGroup($kakaoId, $title, $place, $sellPosition){
      $userId = $this->getIdByKakaoId($kakaoId);
      if($userId==null){
        return USERID_MISSING;
      }
      if(!$this->isCourseExist($userId, $title, $place, $sellPosition)){
        $stmt = $this->con->prepare("INSERT into course (userId, title, place, sellPosition) values (?, ?, ?, ?)");
        $stmt->bind_param("issi", $userId, $title, $place, $sellPosition);
        if($stmt->execute()){
          return COURSE_CREATED;
        }else{
          return COURSE_FAILURE;
        }
      }
      return COURSE_EXISTS;
    }



  }
