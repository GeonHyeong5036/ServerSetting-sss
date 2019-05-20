<?php
  class DbAnalysis{
    private $con;
    private $targetCellPositionList = array();
    private $index=-1;


    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function getAvailableMeetingTimes($array){
      global $targetCellPositionList;
      $availableMeetingTimes = range(0, 39);

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

    public function getAsManyUserAsAvailable($array){
      $availableMeetingTimes = range(0, 39);

      foreach($availableMeetingTimes as $index){
        $sum = 0;
        foreach ($array as $kakaoid) {
          if($this->existUserAtCellPosition($kakaoid, $index)){
            $sum++;
          }
        }
        $availableMeetingTimes[$index] = $sum;
      }
      arsort($availableMeetingTimes);
      $maxInt = max($availableMeetingTimes);

      $filter_availableMeetingTimes = preg_grep("/^$maxInt$/i", $availableMeetingTimes);
      $filter_availableMeetingTimes = array_keys($filter_availableMeetingTimes)
      sort($filter_availableMeetingTimes);

      return $filter_availableMeetingTimes;

    }

    private function existUserAtCellPosition($kakaoId, $cellPosition){
        $stmt = $this->con->prepare("SELECT cellPosition FROM timeTable WHERE userid IN (SELECT id FROM users WHERE kakaoId = ?) AND cellPosition = ? order by cellPosition;");
        $stmt->bind_param("si", $kakaoId, $cellPosition);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
  }
?>
