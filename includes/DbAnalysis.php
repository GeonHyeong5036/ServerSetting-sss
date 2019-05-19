<?php
  class DbAnalysis{
    private $availableMeetingTimes = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39);
    private $con;
    private $index=-1;
    private $targetCellPositionList = array();

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function getAvailableMeetingTimes($array){
      global $targetCellPositionList, $availableMeetingTimes;

      foreach ($array as $kakaoid) {
        $this->getUnAvailableCellPostion($kakaoid);
      }
      $targetCellPositionList = array_unique($targetCellPositionList);
      sort($targetCellPositionList);

      $availableMeetingTimes = array_diff($availableMeetingTimes, $targetCellPositionList);
      $availableMeetingTimes = array_values($availableMeetingTimes);

      return $availableMeetingTimes;
    }

    private function getUnAvailableCellPostion($kakaoId){
      global $targetCellPositionList;
      $stmt = $this->con->prepare("SELECT DISTINCT cellPosition FROM timeTable WHERE userid IN (SELECT id FROM users WHERE kakaoId = ?) order by cellPosition;");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($cellPosition);

      while($stmt->fetch()){
        global $index;
        $index++;
        $targetCellPositionList[$index] = $cellPosition;
      }
    }

    private function countAvailableUser(){

    }
  }
?>
