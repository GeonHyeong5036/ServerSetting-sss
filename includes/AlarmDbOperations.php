<?php
  class AlarmDbOperations{
    private $con;
    private $targetCellPositionList = array();
    private $index=-1;


    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createAlarm($_type, $_from, $_time){
      $stmt = $this->con->prepare("INSERT into alarm (_type, _from, _time) values (?, ?, ?)");
      $stmt->bind_param("sss", $_type, $_from, $_time);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
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

    public function deleteAlarm($id){
        $stmt = $this->con->prepare("DELETE FROM alarm WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute())
            return true;
        return false;
    }
  }

?>
