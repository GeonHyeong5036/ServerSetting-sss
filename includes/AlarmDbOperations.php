<?php
  class AlarmDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createAlarm($_type, $_to, $_from){
      $stmt = $this->con->prepare("INSERT INTO alarm (_type, _to, _from, _time) VALUES (?, (SELECT kakaoId fROM alarmToken WHERE token = ?), (SELECT kakaoId fROM alarmToken WHERE token = ?), (SELECT DATE_FORMAT((SELECT DATE_ADD((SELECT NOW()), INTERVAL 9 HOUR)), '%m/%d %H:%i')))");
      $stmt->bind_param("sss", $_type, $_to, $_from);
      if($stmt->execute()){
        return ALARM_CREATED;
      }else{
        return ALARM_FAILURE;
      }
    }
    // $stmt = $this->con->prepare("INSERT INTO alarm (_type, _to, _from, _time) SELECT ?, ?, ?, (SELECT DATE_FORMAT((SELECT DATE_ADD((SELECT NOW()), INTERVAL 9 HOUR)), '%X %d %m %H %i')) FROM DUAL WHERE NOT EXISTS (SELECT * FROM alarm WHERE _type = ? AND _to = ? AND _from =? AND _time = (SELECT DATE_FORMAT((SELECT DATE_ADD((SELECT NOW()), INTERVAL 9 HOUR)), '%X %d %m %H %i')))");

    public function createAlarmToken($kakaoId, $token){
      if($this->isKakaoIdOfAlarmExist($kakaoId)){
        $stmt = $this->con->prepare("UPDATE alarmToken SET token = ? WHERE kakaoId = ?");
        $stmt->bind_param("ss", $token, $kakaoId);
        if($stmt->execute()){
          return ALARM_UPDATE;
        }else{
          return ALARM_UPDATE_FAILURE;
        }
      }else if(!$this->isAlarmExist($kakaoId, $token)){
        $stmt = $this->con->prepare("INSERT into alarmToken (kakaoId, token) values (?, ?)");
        $stmt->bind_param("ss", $kakaoId, $token);
        if($stmt->execute()){
          return ALARM_CREATED;
        }else{
          return ALARM_FAILURE;
        }
      }
      return ALARM_EXISTS;
    }

    public function getAlarm($to){
      $stmt = $this->con->prepare("SELECT A.id, _type, _to, B.name, _time from alarm A LEFT JOIN users B ON A._from = B.kakaoId WHERE A._to = ? order by _time");
      $stmt->bind_param("s", $to);
      $stmt->execute();
      $stmt->bind_result($id, $_type, $_to, $_from, $_time);
      $alarmList = array();
      while($stmt->fetch()){
        $alarm = array();
        $alarm['id'] = $id;
        $alarm['type']=$_type;
        $alarm['to']= $_to;
        $alarm['from'] = $_from;
        $alarm['time'] = $_time;
        array_push($alarmList, $alarm);
      }
      return $alarmList;
    }

    public function getAlarmToken($kakaoId){
      $stmt = $this->con->prepare("SELECT token from alarmToken where kakaoId IN (SELECT kakaoId from users where kakaoId = ? and member = 1)");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($token);
      $stmt->fetch();
      return $token;
    }

    public function deleteAlarm($alarmId){
        $stmt = $this->con->prepare("DELETE FROM alarm WHERE id = ?");
        $stmt->bind_param("i", $alarmId);
        if($stmt->execute())
            return true;
        return false;
    }
    public function getRecentAlarmId(){
      $stmt = $this->con->prepare("SELECT A.id, _type, _to, B.name, _time from alarm A LEFT JOIN users B ON A._from = B.kakaoId order by id DESC LIMIT 1");
      $stmt->execute();
      $stmt->bind_result($id, $_type, $_to, $_from, $_time);
      $stmt->fetch();
      $alarm = array();
      $alarm['id'] = $id;
      $alarm['type']=$_type;
      $alarm['to']= $_to;
      $alarm['from'] = $_from;
      $alarm['time'] = $_time;

      return $alarm;
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
  }

?>
