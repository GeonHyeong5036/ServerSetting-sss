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

    public function getAsManyUserAsAvailable($kakaoIdList, $option){
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
      if($option == "1"){ //전체 시간표에서 최다수의 시간표 cell를 알려준다.
        $minInt = min($availableMeetingTimes);
        $filter_availableMeetingTimes = preg_grep("/^$minInt/i", $availableMeetingTimes);
        $filter_availableMeetingTimes = array_keys($filter_availableMeetingTimes);
        sort($filter_availableMeetingTimes);
        return $filter_availableMeetingTimes;
      }else if($option == "2"){ //요일별 최대수의 시간표 cell을 알려준다.
        $filter_availableMeetingTimes = array();
        for($day = 0; $day < 5; $day++){
          $cellListByday = array();
          for($cell = $day; $cell < 40 ; $cell += 5){
            $cellListByday[$cell] = $availableMeetingTimes[$cell];
          }
          $minInt = min($cellListByday);
          $filter_cellListByday = preg_grep("/^$minInt/i", $cellListByday);
          $filter_cellListByday = array_keys($filter_cellListByday);
          $filter_availableMeetingTimes = array_merge($filter_availableMeetingTimes, $filter_cellListByday);
        }
        sort($filter_availableMeetingTimes);
        return $filter_availableMeetingTimes;
      }else if($option == "3"){ //시간별 최대수의 시간표 cell을 알려준다.
        $filter_availableMeetingTimes = array();
        for($time = 0; $time <= 35; $time += 5){
          $cellListByday = array();
          for($cell = $time; $cell < $time+5 ; $cell++){
            $cellListByday[$cell] = $availableMeetingTimes[$cell];
          }
          $minInt = min($cellListByday);
          $filter_cellListByday = preg_grep("/^$minInt/i", $cellListByday);
          $filter_cellListByday = array_keys($filter_cellListByday);
          $filter_availableMeetingTimes = array_merge($filter_availableMeetingTimes, $filter_cellListByday);
        }
        sort($filter_availableMeetingTimes);
        return $filter_availableMeetingTimes;
      }
    }

    // private function getGreatestNumberByDay(){
    // }
    // Sun, Mon, Tue, Wed, Thu, Fri, Sat - infos

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
