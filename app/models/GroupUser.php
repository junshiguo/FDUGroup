<?php
/**
 * Data model for user-group relation
 * @author: Raysmond
 */

class GroupUser extends RModel
{
    public $group, $user;
    public $groupId, $userId, $joinTime, $status, $comment;

    public static $primary_key = "groupId";
    public static $table = "group_has_users";
    public static $mapping = array(
        "groupId" => "gro_id",
        "userId" => "u_id",
        "joinTime" => "join_time",
        "status" => "status",
        "comment" => "join_comment"
    );
    public static $relation = array(
        "group" => array("groupId", "Group", "id"),
        "user" => array("userId", "User", "id")
    );

    /**
     * getGroups: Filter groups out from a query result
     * @param array $result Query result from GroupUser
     * @return Groups associated with each GroupUser object in the array
     */
    public static function getGroups($result)
    {
        $groups = array();
        foreach ($result as $groupUser) {
            $groups[] = $groupUser->group;
        }
        return $groups;
    }

    /**
     * getUsers: Filter users out from a query result
     * @param array $result Query result from GroupUser
     * @return Users associated with each GroupUser object in the array
     */
    public static function getUsers($result)
    {
        $users = array();
        foreach ($result as $groupUser) {
            $users[] = $groupUser->user;
        }
        return $users;
    }

    public static function removeUsers($groupId,$userIds=array()){
        $groupUser = new GroupUser();
        foreach($userIds as $id){
            $groupUser->delete(array('groupId'=>$groupId, 'userId'=>$id));
        }
    }

    public static function isUserInGroup($userId, $groupId){
        if (GroupUser::find(array("groupId", $groupId, "userId", $userId))->first() != null)
            return true;
        return false;
    }

    public function delete($assignment = []) {
        if(is_numeric($this->groupId) && is_numeric($this->userId)){
            $sql = "DELETE FROM {$this->table} WHERE {$this->columns['groupId']}={$this->groupId} AND {$this->columns['userId']} = {$this->userId} LIMIT 1";
            Data::executeSQL($sql);
        }
    }
}

