<?php
/**
 * Topic data model
 * @author: Raysmond, Xiangyan Sun
 */
class Topic extends RModel
{
    public $group;
    public $user;
    public $comments = array();

    public $rating;

    public $id, $groupId, $userId, $title, $createdTime, $content, $lastCommentTime, $commentCount;

    const ENTITY_TYPE = 1;

    public static $primary_key = "id";
    public static $table = "topic";
    public static $mapping = array(
        "id" => "top_id",
        "groupId" => "gro_id",
        "userId" => "u_id",
        "title" => "top_title",
        "createdTime" => "top_created_time",
        "content" => "top_content",
        "lastCommentTime" => "top_last_comment_time",
        "commentCount" => "top_comment_count"
    );

    public static $relation = array(
        "group" => array("groupId", "Group", "id"),
        "user" => array("userId", "User", "id"),
        "rating" => array("id","RatingStatistic","entityId","on"=>"RatingStatistic.entityType=1"),
    );

    public function increaseCounter(){
        if(isset($this->id)&&$this->id!=''){
            $counter = new Counter();
            $counter->increaseCounter($this->id,self::ENTITY_TYPE);
            return $counter;
        }
    }

    public function getComments()
    {
        $comments = Comment::find(array("topicId", $this->id, "pid", 0))->all();
        $result = [];
        foreach ($comments as $c) {
            $result[] = ['root' => $c, 'reply' => $c->children()];
        }
        return $result;
    }

    public function delete(){
        $tid = $this->id;

        // delete view counter
        $counter = Counter::find(["entityId",$tid,"entityTypeId",Topic::ENTITY_TYPE])->first();
        if($counter!=null)
            $counter->delete();

        // delete plus-rating data
        $plus = new RatingPlus(Topic::ENTITY_TYPE,$tid);
        $plus->delete();

        // delete all comments
        // todo delete all rows at the same time
        $comments = Comment::find("topicId",$tid)->all();
        foreach($comments as $item)
            $item->delete();

        parent::delete();
    }

    // TODO: use Model functions instead of SQL
    public function getUserFriendsTopics($uid,$limit=0,$endTime=null){
        $friends = new Friend();
        $friends->uid = $uid;
        $friends = $friends->find();

        $topics = new Topic();
        $ids = array();
        foreach($friends as $friend){
            $ids[] = $friend->fid;
        }
        $ids[] = $uid;

        $user = new User();
        $group = new Group();
        $ratingStats = new RatingStatistic();
        $entityType = Topic::ENTITY_TYPE;

        $prefix = Rays::app()->getDBPrefix();
        $sql = "SELECT "
            ."user.".User::$mapping['id'].","
            ."user.".User::$mapping['name'].","
            ."user.".User::$mapping['picture'].","
            ."topic.".Topic::$mapping['id'].","
            ."topic.".Topic::$mapping['title'].","
            ."topic.".Topic::$mapping['content'].","
            ."topic.".Topic::$mapping['createdTime'].","
            ."topic.".Topic::$mapping['commentCount'].","
            ."groups.".Group::$mapping['id'].","
            ."groups.".Group::$mapping['name'].","
            ."rating.{$ratingStats::$mapping['value']} AS plusCount"
            ." FROM ".$prefix.Topic::$table." AS topic "
            ."LEFT JOIN ".$prefix.User::$table." AS user on topic.".Topic::$mapping['userId']."=user.".User::$mapping['id']." "
            ."LEFT JOIN ".$prefix.Group::$table." AS groups on groups.{$group::$mapping['id']}=topic.".Topic::$mapping['groupId']." "
            ."LEFT JOIN {$prefix}{$ratingStats::$table} AS rating on rating.{$ratingStats::$mapping['entityType']}={$entityType} "
            ."AND rating.{$ratingStats::$mapping['entityId']}=topic.".Topic::$mapping['id']." "
            ."AND rating.{$ratingStats::$mapping['tag']}='plus' "
            ."AND rating.{$ratingStats::$mapping['type']}='count'";

        $where = " WHERE 1=1 ";
        if(!empty($ids)){
            $len = count($ids);
            $count = 0;
            $where .= "AND user.".User::$mapping['id']." IN (";
            foreach($ids as $id){
                $where.=$id;
                if($count++<$len-1){
                    $where.=",";
                }
                else{
                    $where.=') ';
                }
            }
        }


        if($endTime!=null){
            $where .="AND topic.".Topic::$mapping['createdTime']."<'{$endTime}' ";
        }

        $sql.=$where;

        $sql.="ORDER BY topic.".Topic::$mapping['id']." DESC ";

        if($limit!=0){
            $sql.="LIMIT ".$limit." ";
        }

        return Data::db_query($sql);
    }

    // TODO: use Model functions instead of SQL
    public static function getDayTopViewPosts($start=0,$limit=0){
        $topics = new Topic();
        $user = new User();
        $group = new Group();
        $counter = new Counter();
        $ratingStats = new RatingStatistic();
        $entityType = Topic::ENTITY_TYPE;

        $prefix = Rays::app()->getDBPrefix();
        $sql = "SELECT "
            ."user.{$user::$mapping['id']},"
            ."user.{$user::$mapping['name']},"
            ."user.{$user::$mapping['picture']},"
            ."topic.{$topics::$mapping['id']},"
            ."topic.{$topics::$mapping['title']},"
            ."topic.{$topics::$mapping['content']},"
            ."topic.{$topics::$mapping['createdTime']},"
            ."topic.{$topics::$mapping['commentCount']},"
            ."groups.{$group::$mapping['id']},"
            ."groups.{$group::$mapping['name']},"
            ."counter.{$counter::$mapping['dayCount']},"
            ."rating.{$ratingStats::$mapping['value']} AS plusCount "
            ." FROM {$prefix}{$topics::$table} AS topic "
            ."LEFT JOIN {$prefix}{$counter::$table} AS counter ON counter.{$counter::$mapping['entityId']}=topic.{$topics::$mapping['id']} AND counter.{$counter::$mapping['entityTypeId']}={$entityType} "
            ."LEFT JOIN {$prefix}{$user::$table} AS user on topic.{$topics::$mapping['userId']}=user.{$user::$mapping['id']} "
            ."LEFT JOIN {$prefix}{$group::$table} AS groups on groups.{$group::$mapping['id']}=topic.{$topics::$mapping['groupId']} "
            ."LEFT JOIN {$prefix}{$ratingStats::$table} AS rating on rating.{$ratingStats::$mapping['entityType']}={$entityType} "
            ."AND rating.{$ratingStats::$mapping['entityId']}=topic.{$topics::$mapping['id']} "
            ."AND rating.{$ratingStats::$mapping['tag']}='plus' "
            ."AND rating.{$ratingStats::$mapping['type']}='count'";

        $where = "WHERE 1=1 ";
//        $beginTime = date('Y-m-d: 00:00:00');

//        $where .=" AND topic.{$topics::$mapping['createdTime']}>'{$beginTime}' ";
        $where .=" AND counter.{$counter::$mapping['dayCount']} > 0 ";

        $sql.=$where;

        $sql.="ORDER BY counter.{$counter::$mapping['dayCount']} DESC ";

        if($start!=0||$limit!=0){
            $sql .= "LIMIT {$start},{$limit} ";
        }

        return Data::db_query($sql);
    }
}