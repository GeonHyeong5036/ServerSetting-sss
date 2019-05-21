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
        $groupId = $this->getGroupIdByKakaoId($title, $tag);

        foreach ($kakaoIdList as $kakaoid) {
          $userId = $this->getUserIdByKakaoId($kakaoid);
          if(!$this->createUserGroupReation($userId, $groupId))
            return USERANDGROUP_FAILURE;
        }
        return GROUP_CREATED;
      }else{
        return GROUP_FAILURE;
      }
    }

    private function createUserGroupReation($userId, $groupId){
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
      while($stmt->fetch()){
        $ids = array();
        $ids['id'] = $id;
        array_push($idList, $ids);
      }
      return $idList;
    }

    public function getManagerListOfGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT manager FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->bind_result($manager);

      $managerList = array();
      while($stmt->fetch()){
        $managers = array();
        $managers['manager'] = $manager;
        array_push($managerList, $managers);
      }
      return $managerList;
    }

    public function getTitleListOfGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT title FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->bind_result($title);

      $titleList = array();
      while($stmt->fetch()){
        $titles = array();
        $titles['title'] = $title;
        array_push($titleList, $titles);
      }
      return $titleList;
    }

    public function getTagListOfGroup($kakaoId){
      $userId = $this->getUserIdByKakaoId($kakaoId);
      $stmt = $this->con->prepare("SELECT tag FROM groups WHERE id IN (SELECT groupId FROM userGroup where userid = ?) AND isActive = 1;");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->bind_result($tag);

      $tagList = array();
      while($stmt->fetch()){
        $tags = array();
        $tags['tag'] = $tag;
        array_push($tagList, $tags);
      }
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
        $kakao['kakaoId'] = $kakaoId;
        $kakao['name'] = $name;

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

    private Function getGroupIdByKakaoId($title, $tag){
      $stmt = $this->con->prepare("SELECT id FROM groups WHERE title = ? and tag = ?;");
      $stmt->bind_param("ss", $title, $tag);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }
  }
