<?php
  class GroupDbOperations{
    private $con;

    function __construct(){
      require_once dirname(__FILE__) . '/DbConnect.php';
      $db = new DbConnect;
      $this->con = $db->connect();
    }

    public function createGroup($kakaoIdList, $title, $type){
      $stmt = $this->con->prepare("INSERT into groups(title, tag) values (?, ?)");
      $stmt->bind_param("ss", $title, $type);
      if($stmt->execute()){
        $groupId = $this->getGroupIdByKakaoId($title, $type);

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

    private function createUserGroupReation($userId, $userId){
      $stmt = $this->con->prepare("INSERT into userGroup(userId, groupId) values (?, ?)");
      $stmt->bind_param("ii", $userId, $userId);
      if($stmt->execute()){
        return true;
      }else{
        return false;
      }
    }

    private Function getUserIdByKakaoId($kakaoId){
      $stmt = $this->con->prepare("SELECT id FROM users WHERE userid IN (SELECT id FROM users WHERE kakaoId = ?);");
      $stmt->bind_param("s", $kakaoId);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }

    private Function getGroupIdByKakaoId($title, $type){
      $stmt = $this->con->prepare("SELECT id FROM group WHERE title = ? and type = ?;");
      $stmt->bind_param("ss", $title, $type);
      $stmt->execute();
      $stmt->bind_result($id);
      $stmt->fetch();
      return $id;
    }
  }
