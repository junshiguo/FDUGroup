<?php
/**
 * Base data model of the ActiveRecord pattern.
 *
 * @author Xiangyan Sun
 */

/**
 * _RModelQueryer
 * Continuous-passing style SQL query builder.
 */
class _RModelQueryer {
    private $model;
    private $query_where = "", $query_order = "", $query_join = array();
    private $args_where;

    public function __construct($model)
    {
        $this->model = $model;
        $this->query_where = "";
        $this->args_where = array();
        $this->query_order = "";
    }

    private function _args()
    {
        return $this->args_where;
    }

    private function _select_fields()
    {
        $model = $this->model;
        $fields = "";
        /* Add fields from self */
        foreach ($model::$mapping as $member => $db_member) {
            $fields .= Rays::app()->getDBPrefix().$model::$table.".{$model::$mapping[$member]},";
        }
        /* Add fields from joined members */
        foreach ($this->query_join as $rel_member) {
            list($m, $constraint) = $model::$relation[$rel_member];
            foreach ($m::$mapping as $member => $db_member) {
                $fields .= Rays::app()->getDBPrefix().$m::$table.".{$m::$mapping[$member]},";
            }
        }
        return rtrim($fields, ",");
    }

    private function _join_clause()
    {
        $model = $this->model;
        $clause = "";
        foreach ($this->query_join as $member) {
            list($m, $constraint) = $model::$relation[$member];
            $modeltable = Rays::app()->getDBPrefix().$model::$table;
            $mtable = Rays::app()->getDBPrefix().$m::$table;
            $clause = "$clause LEFT JOIN $mtable ON " . $this->_substitute($constraint);
        }
        return $clause;
    }

    private function _select($suffix = "")
    {
        $model = $this->model;
        $modeltable = Rays::app()->getDBPrefix().$model::$table;
        $fields = $this->_select_fields();
        $join = $this->_join_clause();
        $sql = "SELECT $fields FROM $modeltable $join $this->query_where $this->query_order $suffix";

        $stmt = RModel::getConnection()->prepare($sql);
        $stmt->execute($this->_args());

        /* Fetch result and construct objects */
        $rs = $stmt->fetchAll();
        $ret = array();
        foreach ($rs as $row) {
            /* Construct self object */
            /* NOTE:
             * We use indices here because we EXPLICITLY specified the fields.
             * To make things correct we MUST iterate all fields using same order here and in _select_fields()
             */
            $obj = new $model();
            $i = 0;
            foreach ($model::$mapping as $member => $db_member) {
                $obj->$member = $row[$i++];
            }
            /* Construct joined member objects */
            foreach ($this->query_join as $rel_member) {
                list($m, $constraint) = $model::$relation[$rel_member];
                $obj->$rel_member = new $m();
                foreach ($m::$mapping as $member => $db_member) {
                    $obj->$rel_member->$member = $row[$i++];
                }
            }
            $ret[] = $obj;
        }
        return $ret;
    }

    /**
     * Do SQL query, return count of matching rows
     * @return Count of matching rows
     */
    public function count()
    {
        $model = $this->model;
        $stmt = RModel::getConnection()->prepare("SELECT COUNT(*) FROM ".Rays::app()->getDBPrefix().$model::$table." $this->query_where");
        $stmt->execute($this->_args());
        $row = $stmt->fetch();
        return $row[0];
    }

    /**
     * Do SQL query, return first matching object
     * @return First object matching given query, if no objects are found, null is returned
     */
    public function first()
    {
        $ret = $this->_select("LIMIT 1");
        if (count($ret) == 0)
            return null;
        else
            return $ret[0];
    }

    /**
     * Do SQL query, return all matching objects in given row range
     * @return All objects matching given query which row id is in the given range, if no objects are found, a empty-sized array is returned
     */
    public function range($firstrow, $rowcount)
    {
        return $this->_select("LIMIT $firstrow, $rowcount");
    }

    /**
     * Do SQL query, return all matching objects
     * @return All objects matching given query, if no objects are found, a empty-sized array is returned
     */
    public function all()
    {
        return $this->_select();
    }

    /**
     * Do SQL query, delete all matching objects
     */
    public function delete()
    {
        $model = $this->model;
        $stmt = RModel::getConnection()->prepare("DELETE FROM ".Rays::app()->getDBPrefix().$model::$table." $this->query_where $this->query_order");
        $stmt->execute($this->_args());
    }

    /**
     * Do SQL update query
     */
    public function update($update, $args = array())
    {
        $model = $this->model;
        $update = $this->_substitute($update);
        $stmt = RModel::getConnection()->prepare("UPDATE ".Rays::app()->getDBPrefix().$model::$table." SET $update $this->query_where");
        $stmt->execute(array_merge($args, $this->_args()));
    }

    private function _substitute($constraint)
    {
        /* Substitute [member]s */
        return preg_replace_callback('/\[(.+?)\]/', function ($matches) {
            $s = explode(".", $matches[1]);
            if (count($s) == 1) {
                $model = $this->model;
                $member = $s[0];
            }
            else {
                $model = $s[0];
                $member = $s[1];
            }
            return Rays::app()->getDBPrefix() . $model::$table . "." . $model::$mapping[$member];
        }, $constraint);
    }

    /**
     * Add a custom where clause
     * @param string $constraint Custom where clause to add, use "[name]" to indicate a member field
     * @param string $args Arguments to pass to the clause
     * @return This object
     */
    public function where($constraint, $args = array())
    {
        if ($this->query_where == "") {
            $this->query_where = "WHERE ";
        }
        else {
            $this->query_where .= " AND ";
        }
        $this->query_where .= $this->_substitute("($constraint)");
        if (is_array($args)) {
            $this->args_where = array_merge($this->args_where, $args);
        }
        else {
            $this->args_where[] = $args;
        }
        return $this;
    }

    private function _find($constraints)
    {
        $model = $this->model;
        $constraint = "";
        $args = array();
        for ($i = 0; $i < count($constraints); $i += 2) {
            if ($constraint != "") {
                $constraint .= " AND ";
            }
            $constraint .= "[{$constraints[$i]}] = ?";
            $args[] = $constraints[$i + 1];
        }
        return $this->where($constraint, $args);
    }

    /**
     * Add a simple matching constraint
     * find(id) : (primary_key) == id
     * find(member, value) : (member) == value
     * find(constraints) : constraints is an array of 2 * N values which consists of N constraints
     * @return This object
     */
    public function find($memberName, $memberValue = null)
    {
        if ($memberValue == null) {
            if (is_array($memberName)) {
                return $this->_find($memberName);
            }
            else {
                $model = $this->model;
                return $this->_find(array($model::$primary_key, $memberName));
            }
        }
        else {
            return $this->_find(array($memberName, $memberValue));
        }
    }

    /**
     * Add a like matching constraint
     * @param string $memberName Member to be matched
     * @param string $memberValue Value to be matched
     * @return This object
     */
    public function like($memberName, $memberValue)
    {
        return $this->where("[$memberName] LIKE ?", "%$memberValue%");
    }

    /**
     * Add a free-form order clause
     * @param string $order "asc" or "desc", case insensitive
     * @param string $expression An expression used for ordering
     * @return This object
     */
    public function order($order, $expression)
    {
        if ($this->query_order == "") {
            $this->query_order = "ORDER BY ";
        }
        else {
            $this->query_order .= ", ";
        }
        $expression = $this->_substitute($expression);
        $this->query_order .= "($expression) $order";
        return $this;
    }

    /**
     * Add an ascending order clause
     * @param string $memberName Column namd for ordering
     * @return This object
     */
    public function order_asc($memberName)
    {
        $model = $this->model;
        return $this->order("ASC", "[$memberName]");
    }

    /**
     * Add a descending order clause
     * @param string $memberName Column name for ordering
     * @return This object
     */
    public function order_desc($memberName)
    {
        $model = $this->model;
        return $this->order("DESC", "[$memberName]");
    }

    /**
     * Add a relation join pre-defined by data model
     * @param string $memberName Member for joining, must be defined in $relation array
     * @return This object
     */
    public function join($memberName)
    {
        $this->query_join[] = $memberName;
        return $this;
    }
}

abstract class RModel {
    private static $connection = null;

    /**
     * Get PDO connection object
     * @return PDO connection object
     */
    public static function getConnection()
    {
        if (self::$connection == null) {
            $dbConfig = Rays::app()->getDbConfig();
            self::$connection = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['db_name']};charset={$dbConfig['charset']}", $dbConfig['user'], $dbConfig['password']);
        }
        return self::$connection;
    }

    /**
     * Find the object using primary key
     * @param int $id Value of the primary key of the object
     * @return The object or null if not found
     */
    public static function get($id)
    {
        $model = get_called_class();
        return (new _RModelQueryer(get_called_class()))->find($model::$primary_key, $id)->first();
    }

    public static function find($memberName = null, $memberValue = null)
    {
        if ($memberName == null)
            return new _RModelQueryer(get_called_class());
        return (new _RModelQueryer(get_called_class()))->find($memberName, $memberValue);
    }

    public static function where($constraint, $args = array())
    {
        return (new _RModelQueryer(get_called_class()))->where($constraint, $args);
    }

    /**
     * Save current object
     * @return Id as primary key in database.
     */
    public function save()
    {
        $model = get_called_class();
        /* Build SQL statement */
        $columns = "";
        $values = "";
        $delim = "";
        $primary_key = $model::$primary_key;
        if (isset($this->$primary_key)) {
            $primary_key = "";
        }
	    foreach ($model::$mapping as $member => $column) {
            if ($member != $primary_key) {
                $columns = "$columns$delim$column";
                $values = "$values$delim?";
                $delim = ", ";
            }
        }
        $sql = "REPLACE INTO ".Rays::app()->getDBPrefix().$model::$table." ($columns) VALUES ($values)";

        /* Now prepare SQL statement */
        $stmt = RModel::getConnection()->prepare($sql);
        $args = array();
        foreach ($model::$mapping as $member => $column) {
            if ($member != $primary_key) {
                $args[] = $this->$member;
            }
        }
        $stmt->execute($args);
        $primary_key = $model::$primary_key;
        if (!isset($this->$primary_key)) {
            $this->$primary_key = RModel::getConnection()->lastInsertId();
        }
        return $this->$primary_key;
    }

    /**
     * Delete this object in database. Note the members of this object is not altered.
     */
    public function delete()
    {
        $model = get_called_class();
        $primary_key = $model::$primary_key;
        $sql = "DELETE FROM ".Rays::app()->getDBPrefix().$model::$table." WHERE {$model::$mapping[$primary_key]} = {$this->$primary_key}";
        RModel::getConnection()->exec($sql);
    }
}
