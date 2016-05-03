<?php
namespace Core;

/**
 * ORM数据库操作类
 *
 * 
 * @code
 * 
 * namespace Company;
 * 
 * // 公司部门
 * class Department extends \Core\Orm
 * {
 *    public static $table = 'vs_department';
 *    public static $key = 'department_id';
 *    
 *    public static $has_to = array(
 *        'user' => array('model'=>'\Compay\Relation', 'relation_key'=>'user_id')
 *    );
 * }
 * 
 * // 用户
 * class User extends \Core\Orm
 * {
 *    public static $table = 'vs_user';
 *    public static $key = 'user_id'
 *    
 *    public static $has_to = array(
 *        'department' => array('model'=>'\Compay\Relation', 'relation_key'=>'user_id')
 *    )
 * }
 * 
 * // 关系记录与部门与用户之间的一对一关系（即公司部门与用户多对多关系）
 * class Relation extends \Core\Orm
 * {
 *    public static $table = 'vs_relation';
 *    public static $key = 'relation_id';
 *    
 *    public static $has_to = array(
 *        'user' => array('model'=>'\Company\User', 'relation_key'=>'user_id'),
 *        'department' => array('model'=>'\Company\Department', 'relation_key'=>'department_id'),
 *    );
 * }
 * 
 * use age:
 * 获取用户所属的所有部门
 * $user = new \Company\User(5);
 * $departments = $user->department;
 * foreach($departments as $department)
 * {
 *    var_dump($department->department);
 * }
 * 
 * 
 * // 获取某个部门的所有用户
 * $department = new \Company\Department(1);
 * $users = $department->user;
 * foreach($users as $user)
 * {
 *    var_dump($user->user); 
 * }
 * 
 * @endcode
 */
Abstract class ORM
{
    
    /**
     * 对象包含的数据
     * @var array
     */
    public $data = array ();
    
    /**
     * 关联对象的数据
     * @var array
     */
    public $related = array ();
    
    /**
     * 改变的数据
     * @var array
     */
    public $changed = array ();
    
    /**
     * 是否加载数据
     * @var bool
     */
    public $loaded = FALSE;
    
    /**
     * 是否保存数据
     * @var bool
     */
    public $saved = FALSE;
    
    /**
     * 表名
     * @var string
     */
    public static $table;
    
    /**
     * 主键
     * @var string
     */
    public static $key = 'id';
    
    /**
     * 属于关系: 一对一关系
     * @var array
     */
    public static $belongs_to = array ();
    
    /**
     * 拥有关系: 一对多
     * @var array
     */
    public static $has_to = array ();
    
    /**
     * 设置数据库连接
     *
     * @param $connect array
     * @return \Core\Database
     * @todo
     */
    public static function database()
    {}
    
    /**
     * 获取多行数据对象
     *
     * @param $where array       	
     * @param $limit int       	
     * @param $offset int       	
     * @param $order_by sring       	
     */
    public static function fetch(array $where = NULL, $limit = 0, $offset = 0, $order_by = NULL)
    {
        $model = get_called_class ();
        $result = array ();
        $select = static::database()->select('*', $model::$table, $where, $limit, $offset, $order_by);
        $rows = static::database()->fetch($select[0], $select[1]);
        if (is_array ( $rows ))
        {
            foreach ( $rows as $row )
            {
                $result [] = new $model ( $row );
            }
        }
        return $result;
    }
    
    /**
     * 自定义查下数据
     *
     * @param $where array       	
     * @param $limit int       	
     * @param $offset int       	
     * @param $order_by sring       	
     */
    public static function sql($sql, $style = 'fetch')
    {
        $model = get_called_class ();
        
        if($style == 'fetch')
        {
            $result = array ();
            $rows = static::database()->fetch($sql);
            if (is_array ( $rows ))
            {
                foreach ( $rows as $row )
                {
                    $result [] = new $model ( $row );
                }
            }
            return $result;
        }
        elseif($style == 'row') 
        {
            $row = static::database()->row($sql);
            if(empty($row))
            {
                return NULL;
            }
            return new $model($row);
        }
        elseif($style == 'column')
        {
            $result = static::database()->column($sql);
            return $result;
        }
        elseif($style == 'insert')
        {
            static::database()->query($sql);
            return static::database()->pdo->lastInsertId();
        }
        elseif($style == 'update')
        {
            $statement = static::database()->query($sql);
            if ($statement)
            {
                return $statement->rowCount ();
            }
        }
        elseif($style == 'delete')
        {
            $statement = static::database()->query($sql);
            if ($statement)
            {
                return $statement->rowCount ();
            }
        }
    }
    
    /**
     * 简单统计数据记录总数
     *
     * @param $where array       	
     * @return int
     */
    public static function count(array $where = NULL)
    {
        $model = get_called_class ();
        $select = static::database()->select('COUNT(*)', $model::$table, $where);
        return static::database()->column($select[0], $select[1]);
    }

    /**
     * 简单统计数据记录总和
     *
     * @param $where array          
     * @return int
     */
    public static function sum(array $where = NULL, $column)
    {
        $model = get_called_class ();
        $select = static::database()->select("SUM({$column})", $model::$table, $where);
        return static::database()->column($select[0], $select[1]);
    }
    
    /**
     * 获取包含一行数据的对象
     *
     * @param $where array       	
     * @return int
     */
    public static function row(array $where = NULL, $order = NULL)
    {
        $model = get_called_class ();
        $select = static::database()->select('*', $model::$table, $where, 1, 0, $order);
        $row = static::database()->row($select[0], $select[1]);
        if(empty($row))
        {
            return NULL;
        }
        return new $model($row);
    }
    
    /**
     * 初始化一行数据记录对象，传入参数可以是主键ID或者整行数据
     *
     * @param $id int|array       	
     */
    public function __construct($primary_key = 0)
    {
        if (! $primary_key)
        {
            return;
        }
        if(is_array($primary_key) || is_object($primary_key))
        {
            $this->data = (array) $primary_key;
            $this->loaded = TRUE;
        }
        else 
        {
            $this->data [static::$key] = $primary_key;
        }
        $this->saved = TRUE;
    }
    
    /**
     * 获取数据表主键
     *
     * @return int
     *
     */
    public function key()
    {
        return isset ( $this->data [static::$key] ) ? $this->data [static::$key] : NULL;
    }
    
    /**
     * 将对象转为数组返回
     *
     * @return array
     *
     */
    public function to_array()
    {
        if ($this->load ())
        {
            return $this->data;
        }
        return NULL;
    }
    
    /**
     * 为数据对象设置一个属性
     *
     * @param $key string       	
     * @param $v mixed       	
     *
     */
    public function __set($key, $value)
    {
        if (! array_key_exists ( $key, $this->data ) or $this->data [$key] !== $value)
        {
            $this->data [$key] = $value;
            $this->changed [$key] = $value;
            $this->saved = FALSE;
        }
    }
    
    /**
     * 动态获取对象属性
     *
     * @param $key string       	
     * @return mixed
     *
     */
    public function __get($key)
    {
        if (isset ( $this->data [$key] ))
        {
            return $this->data [$key];
        }
        $this->load ();
        return isset ( $this->data [$key] ) ? $this->data [$key] : $this->related ( $key );
    }
    
    /**
     * 判断runtime指定数据是否存在
     *
     * @see isset()
     */
    public function __isset($key)
    {
        if (isset ( $this->data [static::$key] ) and ! $this->loaded)
        {
            $this->load ();
        }
        return array_key_exists ( $key, $this->data ) or isset ( $this->related [$key] );
    }
    
    /**
     * 清除runtime指定数据
     *
     * @see unset()
     */
    public function __unset($key)
    {
        unset ( $this->data [$key], $this->changed [$key], $this->related [$key] );
    }
    
    /**
     * 重新加载数据对象
     *
     * @return boolean
     */
    public function reload()
    {
        $key = $this->key ();
        $this->data = $this->changed = $this->related = array ();
        $this->loaded = FALSE;
        if (! $key)
        {
            return;
        }
        $this->data [static::$key] = $key;
        return $this->load ();
    }
    
    /**
     * 清除runtime数据
     */
    public function clear()
    {
        $this->data = $this->changed = $this->related = array ();
        $this->loaded = $this->saved = FALSE;
    }
    
    /**
     * 根据条件加载一条数据对象
     *
     * @return boolean
     */
    public function load()
    {
        $row = array ();
        if ($this->loaded)
        {
            return TRUE;
        }
        if (empty ( $this->data [static::$key] ))
        {
            $this->clear ();
            return FALSE;
        }
        $row = self::row(array(static::$key => $this->data [static::$key]));
        if ($row)
        {
            $this->data = $row->to_array();
            return $this->saved = $this->loaded = TRUE;
        }
        else 
        {
            // 如果没有数据,还原数据
            $this->clear ();
            return FALSE;
        }
    }
    
    /**
     * 加载有关系的数据
     *
     * @param $alias string       	
     * @return object
     */
    protected function related($alias)
    {
        if (isset ( $this->related [$alias] ))
        {
            return $this->related [$alias];
        }
        
        if (isset ( static::$belongs_to [$alias] ))
        {
            $belongs_to = static::$belongs_to [$alias];
            $model = $belongs_to['model'];
            $key = $belongs_to['relation_key'];
            return $this->related [$alias] = new $model ( $this->data [$key] );
        }
        elseif (isset ( static::$has_to [$alias] ))
        {
            $has_to = static::$has_to [$alias];
            $model = $has_to['model'];
            $key = $has_to['relation_key'];
            $this->related [$alias] = $model::fetch(array ($key => $this->data [$key] ));
            return $this->related [$alias];
        
        }
        return NULL;
    }
    
    /**
     * 封装插入和更新数据
     * 原则:如果有主键则更新，没有主键则写入
     */
    public function save()
    {
        if (! $this->changed)
        {
            return false;
        }
        if(!empty($this->data [static::$key]))
        {
        	return $this->update();
        }
        else
        {
        	return $this->insert();
        }
    }
    
    /**
     * 插入数据
     *
     * @param $data array       	
     * @return int
     */
    public function insert()
    {
        if (! $this->changed)
        {
            return false;
        }
        $id = static::database()->insert(static::$table, $this->changed);
        $this->data [static::$key] = $id;
        $this->loaded = $this->saved = 1;
        $this->changed = array ();
        return $id;

    }
    
    /**
     * 更新数据
     *
     * @param $data array       	
     * @return int
     */
    public function update()
    {
        if (! $this->changed)
        {
            return false;
        }
        $count = static::database()->update(static::$table, $this->changed, array (static::$key => $this->data [static::$key] ));
        $this->saved = 1;
        $this->changed = array ();
        return $count;
    }
    
    /**
     * 删除数据
     *
     * @return int
     */
    public function delete()
    {
        if (! isset ( $this->data [static::$key] ))
        {
            throw new \Exception ( 'this class data[' . static::$key . '] is empty' );
        }
        $count = static::database()->delete(static::$table, array (static::$key => $this->data [static::$key] ));
        $this->clear ();
        return $count;
    }
    
    /**
     * 删除拥有has_to关联的数据
     *
     * @return int
     */
    public function delete_has_relations()
    {
        $count = 0;
        foreach ( static::$has_to as $has_to )
        {
            $model = $has_to['model'];
            $key = $has_to['relation_key'];
            $rows = $model::fetch ( array ($key => $this->data [$key] ));
            foreach ( $rows as $object )
            {
                $count += $object->delete ();
            }
        }
        return $count;
    }
    
    /**
     * 获取相关的数据
     */
    public function get_has_relation()
    {
        foreach ( static::$has_to as $relation => $has_to )
        {
            $model = $has_to['model'];
            $key = $has_to['relation_key'];
            $this->related [$relation] = $model::fetch ( array ($key => $this->data [$key] ));
            foreach ( $this->related [$relation] as &$object )
            {
                $object->load();
            }
        }
        return $this;
    }
}