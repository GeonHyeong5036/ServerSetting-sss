<?php
  class GroupDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createGroup($kakaoIdList, $manager, $title, $tag){
      $manager = $this->getUserIdByKakaoId($manager);
      $stmt = $this->con->prepare("INSERT into groups(manager, title, tag) values (?, ?, ?)");
      $stmt->bind_param("iss", $manager, $title, $tag);
      if($stmt->execute()){
        $groupId = $this->getGroupIdByColumn($title, $tag);

        foreach ($kakaoIdList as $kakaoid) {
          $userId = $this->getUserIdByKakaoId($kakaoid);
          if(!$this->createUserGroupRelation($userId, $groupId))
            return USERANDGROUP_FAILURE;
        }
        return GROUP_CREATED;
      }else{
        return GROUP_FAILURE;
      }
    }

    private function createUserGroupRelation($userId, $groupId){
      $stmt = $this->con->prepare("INSERT into userGroup(userId, groupId) values (?, ?)");
      $stmt->bind_param("ii", $userId, $groupId);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
    }

    public function getIdListGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT id FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
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

    private Function getGroupIdByColumn($title, $tag){
      $stmt = $this->con->prepare("SELECT id FROM groups WHERE title = ? and tag = ?;");
      $stmt->bind_param("ss", $title, $tag);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

    public Function deleteGroup($groupId, $cellPositionList){
      $meetingDb = new MeetingDbOperations;
      $meetingIdList = $this->getMeetingIdbyGroupId($groupId);

      foreach ($meetingIdList as $meeting) {
        foreach ($meeting as $key) {
          if(!$meetingDb->deleteMeeting($key, $cellPositionList))
            return true;
        }
      }
      $stmt = $this->con->prepare("DELETE FROM groups WHERE id = ?");
      $stmt->bind_param("i", $groupId);
      if($stmt->execute())
        return true;
      return false;
    }

    public Function getMeetingIdbyGroupId($groupId){
      $stmt = $this->con->prepare("SELECT meetingId FROM groupMeeting WHERE groupId = ?;");
      $stmt->bind_param("i", $groupId);
      $stmt->execute();
      $stmt->bind_result($id);

      $meetingIdList = array();
      while($stmt->fetch()){
        $meetingId = array();
        $meetingId['meetingId'] = $id;

        array_push($meetingIdList, $meetingId);
      }
      return $meetingIdList;
    }
  }
