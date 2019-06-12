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
      if($targetCellPositionList == null){ //분석할 시간표가 모두 비어 있을때
        $availableMeetingTimes = array_values($availableMeetingTimes);
        return $availableMeetingTimes;
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

    public function getAsManyUserAsAvailable($kakaoIdList){
      $availableMeetingTimes = range(0, 39);

      foreach($availableMeetingTimes as $index){
        $sum = 0;
        foreach ($kakaoIdList as $kakaoid) {
          if($this->existUserAtCellPosition($kakaoid, $index)){
            $sum++;
          }
        }
        $availableMeetingTimes[$index] = $sum;
      }
      arsort($availableMeetingTimes);
      $minInt = min($availableMeetingTimes);
      echo "최솟값은 :".$minInt;
      if($minInt == '0'){
        $filter_availableMeetingTimes = preg_grep("/^$minInt/i", $availableMeetingTimes);
        $filter_availableMeetingTimes = array_keys($filter_availableMeetingTimes);
        sort($filter_availableMeetingTimes);
        return $filter_availableMeetingTimes;
      }
      return NOT_EMPTY;
    }

    public function getDeduplicatedCellList($cellPositionList){
      $availableMeetingTimes = range(0, 39);
      $cellPositionList = explode('[', $cellPositionList);
      $cellPositionList = explode(']', $cellPositionList[1]);
      $cellPositionList = explode(', ', $cellPositionList[0]);

      $unique_cellPositionList = array_unique($cellPositionList);
      sort($unique_cellPositionList);
      $availableMeetingTimes = array_diff($availableMeetingTimes, $unique_cellPositionList);
      $availableMeetingTimes = array_values($availableMeetingTimes);
      return $availableMeetingTimes;
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
