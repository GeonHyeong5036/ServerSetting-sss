<?php
  class MeetingDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createMeeting($kakaoIdList, $cellPositionList, $groupId, $type, $manager, $title, $place){
      $tableDb = new DbOperations;

      $stmt = $this->con->prepare("INSERT into meeting(type, manager, title, place) values (?, ?, ?, ?)");
      $stmt->bind_param("siss", $type, $manager, $title, $place);

      if($stmt->execute()){
        $meetingId = $this->getMeetingIdByColumn($manager, $title, $place);

        if(!$this->createGroupMeetingRelation($groupId, $meetingId))
          return MEETINGRELATION_FAILURE;

        foreach ($kakaoIdList as $kakaoId) {
          $userId = $this->getUserIdByKakaoId($kakaoId);

          if(!$this->createUserMeetingRelation($userId, $meetingId))
            return MEETINGRELATION_FAILURE;


          foreach ($cellPositionList as $cellPosition) {
            if($tableDb->createTimeTable($kakaoId, "m", $title, $place, $cellPosition) != TIMETABLE_CREATED){
              return MEETING_FAILURE;
            }
          }
        }
        return MEETING_CREATED;
      }else
        return MEETING_FAILURE;
    }

    private function createUserMeetingRelation($userId, $meetingId){
      $stmt = $this->con->prepare("INSERT into userMeeting(userId, meetingId) values (?, ?)");
      $stmt->bind_param("ii", $userId, $meetingId);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
    }

    private function createGroupMeetingRelation($groupId, $meetingId){
      $stmt = $this->con->prepare("INSERT into groupMeeting(groupId, meetingId) values (?, ?)");
      $stmt->bind_param("ii", $groupId, $meetingId);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
    }

    public function getIdListOfMeeting($groupId){
      $stmt = $this->con->prepare("SELECT id FROM meeting WHERE id IN (SELECT meetingId FROM groupMeeting where groupId = ?) AND isActive = 1");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($id);

      $idList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $idList[$index] = $id;
      }
      $idList = array_values($idList);
      return $idList;
    }

    public function getTypeListOfMeeting($groupId){
      $stmt = $this->con->prepare("SELECT type FROM meeting WHERE id IN (SELECT meetingId FROM groupMeeting where groupId = ?) AND isActive = 1");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($type);

      $typeList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $typeList[$index] = $type;
      }
      $typeList = array_values($typeList);
      return $typeList;
    }

    public function getManagerListOfMeeting($groupId){
      $stmt = $this->con->prepare("SELECT manager FROM meeting WHERE id IN (SELECT meetingId FROM groupMeeting where groupId = ?) AND isActive = 1");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($manager);

      $managerList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $managerList[$index] = $manager;
      }
      $managerList = array_values($managerList);
      return $managerList;
    }

    public function getTitleListOfMeeting($groupId){
      $stmt = $this->con->prepare("SELECT title FROM meeting WHERE id IN (SELECT meetingId FROM groupMeeting where groupId = ?) AND isActive = 1");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($title);

      $titleList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $titleList[$index] = $title;
      }
      $titleList = array_values($titleList);
      return $titleList;
    }

    public function getPlaceListOfMeeting($groupId){
      $stmt = $this->con->prepare("SELECT place FROM meeting WHERE id IN (SELECT meetingId FROM groupMeeting where groupId = ?) AND isActive = 1");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($place);

      $placeList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $placeList[$index] = $place;
      }
      $placeList = array_values($placeList);
      return $placeList;
    }

    public function getCellPositionOfMeeting($groupId){
      $stmt = $this->con->prepare("SELECT cellPosition FROM timeTable WHERE title IN (SELECT title FROM meeting WHERE id IN (SELECT meetingId FROM groupMeeting WHERE groupId = ?));");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($cellPosition);

      $cellPositionList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $cellPositionList[$index] = $cellPosition;
      }
      $cellPositionList = array_unique($cellPositionList);
      $cellPositionList = array_values($cellPositionList);
      return $cellPositionList;
    }

    public Function getUserByMeetingId($id){
      $stmt = $this->con->prepare("SELECT kakaoId, name FROM users WHERE id IN (SELECT userId FROM userMeeting WHERE meetingId = ?);");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->bind_result($kakaoId, $name);

      $kakaoList = array();
      while($stmt->fetch()){
        $kakao = array();
        $kakao['userId'] = $kakaoId;
        $kakao['profileNickname'] = $name;

        array_push($kakaoList, $kakao);
      }
      return $kakaoList;
    }

    private Function getUserIdByKakaoId($kakaoId){
      $stmt = $this->con->prepare("SELECT id FROM users WHERE kakaoId = ?;");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

    private Function getMeetingIdByColumn($manager, $title, $place){
      $stmt = $this->con->prepare("SELECT id FROM meeting WHERE manager = ? and title = ? and place = ?;");
      $stmt->bind_param("iss", $manager, $title, $place);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

    public function updateMeeting($groupId, $type, $manager, $title, $place){
      $stmt = $this->con->prepare("UPDATE meeting SET type = ?, manager = ?, title = ?, place = ? WHERE id = ?;");
      $stmt->bind_param("isssi", $type, $manager, $title, $place, $groupId);
      if($stmt->execute())
        return true;
      return false;
    }

    public Function deleteMeeting($meetingId, $cellPositionList){
      $tableDb = new DbOperations;
      $kakaoIdList = $this->getKakaoIdbyMeetingId($meetingId);
      
      $cellPositionList = explode('[', $cellPositionList);
      $cellPositionList = explode(']', $cellPositionList[1]);
      $cellPositionList = explode(', ', $cellPositionList[0]);


      foreach ($kakaoIdList as $kakaoId) {
        foreach ($kakaoId as $key) {
          foreach ($cellPositionList as $cellPosition) {
            if(!$tableDb->deleteTimeTable($key, $cellPosition)){
              return true;
            }
          }
        }
      }

      $stmt = $this->con->prepare("DELETE FROM meeting WHERE id = ?");
      $stmt->bind_param("i", $meetingId);
      if($stmt->execute())
        return true;
      return false;
    }

    public Function getKakaoIdbyMeetingId($meetingId){
      $stmt = $this->con->prepare("SELECT kakaoId FROM users WHERE id IN (SELECT userId FROM userMeeting WHERE meetingId = ?);");
      $stmt->bind_param("i", $meetingId);
      $stmt->execute();
      $stmt->bind_result($id);

      $kakaoIdList = array();
      while($stmt->fetch()){
        $kakaoId = array();
        $kakaoId['kakaoId'] = $id;

        array_push($kakaoIdList, $kakaoId);
      }
      return $kakaoIdList;
    }
  }
