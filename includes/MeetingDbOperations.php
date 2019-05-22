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

echo $type. $manager. $title. $place;

      if($stmt->execute()){
        $meetingId = $this->getMeetingIdByColumn($manager, $title, $place);

        foreach ($kakaoIdList as $kakaoid) {
          $userId = $this->getUserIdByKakaoId($kakaoid);
          if(!$this->createUserMeetingReation($userId, $meetingId) && !$this->createGroupMeetingReation($groupId, $meetingId)){
            return MEETINGRELATION_FAILURE;
          }else{
            foreach ($cellPositionList as $cellPosition) {
              if($tableDb->createTimeTable($kakaoId, $type, $title, $place, $cellPosition) != TIMETABLE_CREATED){
                return MEETING_FAILURE;
              }
            }
          }
        }
        return MEETING_CREATED;
      }else
        return MEETING_FAILURE;
    }

    private function createUserMeetingReation($userId, $meetingId){
      $stmt = $this->con->prepare("INSERT into userMeeting(userId, meetingId) values (?, ?)");
      $stmt->bind_param("ii", $userId, $meetingId);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
    }

    private function createGroupMeetingReation($groupId, $meetingId){
      $stmt = $this->con->prepare("INSERT into groupMeeting(groupId, meetingId) values (?, ?)");
      $stmt->bind_param("ii", $groupId, $meetingId);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
    }

    public function getIdListGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT id FROM meeting WHERE id IN (SELECT meetingId FROM userMeeting where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
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

    public function getTypeListGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT id FROM meeting WHERE id IN (SELECT meetingId FROM userMeeting where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
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

    public function getManagerListOfGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT manager FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
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

    public function getTitleListOfGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT title FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
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

    public function getTagListOfGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT tag FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->bind_result($tag);

      $tagList = array();
      $index = -1;

      while($stmt->fetch()){
        $index++;
        $tagList[$index] = $tag;
      }
      $tagList = array_values($tagList);
      return $tagList;
    }

    public Function getUserByGroupId($id){
      $stmt = $this->con->prepare("SELECT kakaoId, name FROM users WHERE id IN (SELECT userId FROM userGroup WHERE groupId = ?);");
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
      $stmt = $this->con->prepare("SELECT id FROM meeting WHERE manager = ? and title = ? and tag = ?;");
      $stmt->bind_param("iss", $manager, $title, $place);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }
  }
