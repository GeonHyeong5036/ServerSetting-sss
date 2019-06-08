<?php
  class AlarmDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createAlarmToken($kakaoId, $token){
      if(!$this->isAlarmExist($kakaoId, $token)){
        $stmt = $this->con->prepare("INSERT into alarmToken (kakaoId, token) values (?, ?)");
        $stmt->bind_param("ss", $kakaoId, $token);
        if($stmt->execute()){
          return ALARM_CREATED;
        }else{
          return ALARM_FAILURE;
        }
      }else if($this->isKakaoIdOfAlarmExist($kakaoId)){
        $stmt = $this->con->prepare("UPDATE alarmToken SET token = ? WHERE kakaoId = ?");
        $stmt->bind_param("ss", $token, $kakaoId);
        if($stmt->execute()){
          return ALARM_UPDATE;
        }else{
          return ALARM_UPDATE_FAILURE;
        }
      }
      return ALARM_EXISTS;
    }

    public function getAllAlarm(){
      $stmt = $this->con->prepare("SELECT id, _type, _from, _time from alarm order by _time");
      $stmt->execute();
      $stmt->bind_result($id, $_type, $_from, $_time);
      $alarms = array();
      while($stmt->fetch()){
          $alarm = array();
          $alarm['id'] = $id;
          $alarm['type']=$_type;
          $alarm['from'] = $_from;
          $alarm['time'] = $_time;
          array_push($alarms, $alarm);
      }
      return $alarms;
    }

    private function isAlarmExist($kakaoId, $token){
      $stmt = $this->con->prepare("SELECT id from alarmToken where kakaoId = ? AND token =?");
      $stmt->bind_param("ss", $kakaoId, $token);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }

    private function isKakaoIdOfAlarmExist($kakaoId){
      $stmt = $this->con->prepare("SELECT id from alarmToken where kakaoId = ?");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->store_result();
      return $stmt->num_rows > 0;
    }

    // public function deleteAlarm($id){
    //     $stmt = $this->con->prepare("DELETE FROM alarm WHERE id = ?");
    //     $stmt->bind_param("i", $id);
    //     if($stmt->execute())
    //         return true;
    //     return false;
    // }
  }

?>
