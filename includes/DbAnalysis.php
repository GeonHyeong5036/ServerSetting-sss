<?php
  class DbAnalysis{
    private $con;
    private $index=-1;
    private $availableCellPositionList = array();

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function getAvailableMeetingTimes($array){
      global $availableCellPositionList;
      foreach ($array as $kakaoid) {
        $this->getAvailableCellPostion($kakaoid);
      }
      $availableMeetingTimes = array_unique($availableCellPositionList);

      foreach ($availableCellPositionList as $key) {
        echo $key. ' ';
      }
      $availableMeetingTimes= arsort($availableMeetingTimes);
      return $availableMeetingTimes;
    }

    private function getAvailableCellPostion($kakaoId){
      global $availableCellPositionList;
      $stmt = $this->con->prepare("SELECT DISTINCT cellPosition FROM timeTable WHERE userid IN (SELECT id FROM users WHERE kakaoId = ?) order by cellPosition;");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($cellPosition);

      while($stmt->fetch()){
        global $index;
        $index++;
        $availableCellPositionList[$index] = $cellPosition;
      }
      foreach ($availableCellPositionList as $key) {
        echo $key. ' ';
      }
    }
  }
?>
