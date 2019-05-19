<?php
  class DbAnalysis{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function getAvailableMeetingTimes($array){
      $array_list = array();
      foreach ($array as $key) {
        $availableCellPositionList = $this->getAvailableCellPostion($key);
        array_push($array_list, $availableCellPositionList);
      }
      $availableMeetingTimes = array_unique($array_list);
      return arsort($availableMeetingTimes);
    }

    public function getAvailableCellPostion($kakaoId){
      $stmt = $this->con->prepare("SELECT DISTINCT cellPosition FROM timeTable WHERE userid IN (SELECT id FROM users WHERE kakaoId = ?) order by cellPosition;");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($cellPosition);

      $availableCellPositionList = array();
      while($stmt->fetch()){
        $availableCellPosition = array();
        $availableCellPosition['cellPosition'] = $cellPosition;
        array_push($availableCellPositionList, $availableCellPosition);
      }
      return $availableCellPositionList;
    }
  }
?>
