<?php
  class DbAnalysis{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function getAvailableMeetingTimes($sql){
      $stmt = $this->con->prepare($sql);
      $stmt->execute();
      $stmt->bind_result($cellPosition);

      $availableMeetingTimes = array();
      while($stmt->fetch()){
        $availableMeetingTime = array();
        $availableMeetingTime['cellPosition'] = $cellPosition;
        array_push($availableMeetingTimes, $availableMeetingTime);
      }
      return $availableMeetingTimes;
    }
  }
?>
