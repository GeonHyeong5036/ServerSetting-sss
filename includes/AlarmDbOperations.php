<?php
  class AlarmDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createAlarm($_type, $_to, $_from){
      $stmt = $this->con->prepare("INSERT INTO alarm (_type, _to, _from, _time) VALUES (?, (SELECT kakaoId fROM alarmToken WHERE token = ?), (SELECT kakaoId fROM alarmToken WHERE token = ?), (SELECT DATE_FORMAT((SELECT DATE_ADD((SELECT NOW()), INTERVAL 9 HOUR)), '%d/%m %H:%i')))");
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

    public function getAlarm($_from){
      echo "1 : ".$this->getNameBykakaoId($_from);
      $stmt = $this->con->prepare("SELECT id, _type, _to, _from, _time from alarm where _from = ? order by _time");
      $stmt->bind_param("s", $_from);
      $stmt->execute();
      $stmt->bind_result($id, $_type, $_to, $_from, $_time);
      // echo "2 : ".$this->getNameBykakaoId($_from);
      // $q = $this->getNameBykakaoId($_to);
      // echo "\nq : ".$q;
      $alarmList = array();
      while($stmt->fetch()){
        // echo "3 : ".$this->getNameBykakaoId($_from);
        $alarm = array();
        // $_to = $this->getNameBykakaoId($_to);
        // $_from = $this->getNameBykakaoId($alarm['from']);
        $alarm['id'] = $id;
        $alarm['type']=$_type;
        $alarm['to']= $_to;
        // echo "4 : ".$this->getNameBykakaoId($_from);
        $alarm['from'] = $_from;
        // echo "5 : ".$this->getNameBykakaoId($_from);
        // $q = $this->getNameBykakaoId($_to);
        // echo "\nq : ".$_from;
        $alarm['time'] = $_time;
        array_push($alarmList, $alarm);
      }
      echo "6 : ".$this->getNameBykakaoId($_from);
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

    public function getNameBykakaoId($kakaoId){
      $stmt = $this->con->prepare("SELECT name from users where kakaoId = ?");
      if($kakaoId == null)
        echo "\nnull : ".$kakaoId;
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($name);
      $stmt->fetch();
      return $name;
    }

    public function deleteAlarm($alarmId){
        $stmt = $this->con->prepare("DELETE FROM alarm WHERE id = ?");
        $stmt->bind_param("i", $alarmId);
        if($stmt->execute())
            return true;
        return false;
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
