<?php
  class TimeTableDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
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
      return TIMETABLE_FAILURE;
    }

    public function updateTimeTable($kakaoId, $type, $title, $place, $cellPosition){
      $id = $this->getIdByKakaoIdAtTimeTable($kakaoId, $cellPosition);
      $stmt = $this->con->prepare("UPDATE timeTable SET type = ?, title = ?, place = ? WHERE id = ?;");
      $stmt->bind_param("sssi", $type, $title, $place, $id);
      if($stmt->execute())
        return true;
      return false;
    }

    private function getIdByKakaoIdAtTimeTable($kakaoId, $cellPosition){
      $stmt = $this->con->prepare("SELECT id from timeTable where userId IN (SELECT id from users where kakaoId = ?) and cellPosition = ?;");
      $stmt->bind_param("si", $kakaoId, $cellPosition);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

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
      $stmt = $this->con->prepare("DELETE FROM timeTable WHERE userId IN (SELECT id from users where kakaoId = ?) and cellPosition = ?");
      $stmt->bind_param("ii", $kakaoId, $cellPosition);
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
    private function getIdByKakaoId($kakaoId){
      $stmt = $this->con->prepare("SELECT id from users where kakaoId = ?");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }
  }
?>
