<?php
  class DbAnalysis{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function getAvailableMeetingTimes($array){
      $availableCellPositionList = array();
      foreach ($array as $kakaoid) {
        $cellPositionLiist = $this->getAvailableCellPostion($kakaoid);
        array_push($availableCellPositionList, $cellPositionLiist);
      }
      //$availableMeetingTimes = array_unique($availableCellPositionList);
      return arsort($availableMeetingTimes);
    }

    public function getAvailableCellPostion($kakaoId){
      $stmt = $this->con->prepare("SELECT DISTINCT cellPosition FROM timeTable WHERE userid IN (SELECT id FROM users WHERE kakaoId = ?) order by cellPosition;");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($cellPosition);

      while($stmt->fetch()){
        $cellPositionLiist = array();
        $cellPositionLiist['cellPosition'] = $cellPosition;
      }
      return $cellPositionLiist;
    }
  }
?>
