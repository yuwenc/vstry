<?php
namespace Core;

/**
 * 数据库操作类
 * @author chenyuwen
 *
 */
class Database
{
    /**
     * 链接目标对象
     * @var string
     */
    protected $db_target = NULL;
    
    /**
     * 用户名
     * @var string
     */
    protected $user_name = NULL;
    
    /**
     * 密码
     * @var string
     */
    protected $password = NULL;
    
    /**
     * 参数
     * @var array
     */
    protected $params = array();
    
    /**
     * PDO对象
     * @var PDO
     */
    protected $pdo = NULL;
    
    /**
     * 数据声明
     * @var \PDOStatement
     */
    protected $statement = null;
    
    /**
     * runtime执行的所有sql
     * @var array
     */
    public static $queries = array ();
    
    /**
     * 最后一次执行的sql
     * @var string
     */
    public static $last_query = NULL;
    
    /**
     * 设置数据库连接参数
     * @param array $config
     * 
     * @code php
     * 
     * $db_target = 'host=127.0.0.1;port=3306;dbname=yuwenc';
     * $user_name = 'yuwenc';
     * $password = 'yuwenc';
     * $params = array (PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") ;
     * $mysql = new \Core\MySql($dbTarget, $userName, $password, $params);
     * @endcode
     */
    public function __construct($db_target, $user_name, $password, $params)
    {
        $this->db_target = $db_target;
        $this->user_name = $user_name;
        $this->password = $password;
        $this->params   = $params;
    }
    
    
    /**
     * 延迟连接数据库，当需要使用数据库操作的时候才连接
     * @return \PDO
     */
    protected function connect()
    {
        if (is_null($this->pdo))
        {
            try
            {
                $this->pdo = new \PDO($this->db_target, $this->user_name, $this->password, $this->params);
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            catch ( \PDOException $e )
            {
                exit ( "ERROR: failed to connect database: {$e->getMessage()}\n" );
            }
        }
        return $this->pdo;
    }
    
    /**
     * 关闭数据库连接
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
    
    /**
     * 开始事务
     */
    public function begin_transaction()
    {
        $this->connect()->beginTransaction ();
    }
    
    /**
     * 提交事务
     * @return
     */
    public function commit()
    {
        $this->connect()->commit ();
    }
    
    /**
     * 回滚事务
     */
    public function roll_back()
    {
        $this->connect()->rollBack ();
    }
    
    /**
     * Quotes a string for use in a query
     * @param mixed $value to quote
     * @return string
     */
    public function quote($value)
    {
        return $this->connect()->quote ( $value );
    }
    
    /**
     * 执行一个sql语句，返回一个statement 对象
     * @param string $sql query to run
     * @param array $params the prepared query params
     * @return \Core\Database
     */
    public function query($sql, array $params = NULL)
    {
        try
        {
            $time = microtime ( TRUE );
            self::$last_query = $sql;
            $this->statement = $this->connect()->prepare ( $sql );
            $this->statement->execute ( $params );
            self::$queries[] = array (microtime ( TRUE ) - $time, $sql );
            return $this;
        }
        catch ( \Exception $e )
        {
            throw new \Exception($e->getMessage()."\nsql:{$sql}");
        }
    }
    
    /**
     * 获取一笔数据
     * @param string $fetch_style
     * @param string $cursor_orientation
     * @param int $cursor_offset
     */
    public function row($fetch_style = \PDO::FETCH_ASSOC)
    {
        return $this->statement->fetch ( $fetch_style );
    }
    
    /**
     * 获取一笔数据中的某一列数据
     * @param int $column_number
     */
    public function column($column_number = 0)
    {
        return $this->statement->fetchColumn ( $column_number );
    }
    
    /**
     * 获取N笔数据
     * @param string $fetch_style
     */
    public function fetch($fetch_style = \PDO::FETCH_ASSOC)
    {
        return $this->statement->fetchAll ( $fetch_style );
    }
    
    /**
     * 返回受影响的数据行数 (for insert/delete/update)
     * 
     * @return int
     */
    public function row_count()
    {
        return $this->statement->rowCount ();
    }
    
    /**
     * 返回执行成功添加数据操作时生成的 last_insert_id (for insert)
     * 
     * @return int
     */
    public function last_insert_id()
    {
        return $this->pdo->lastInsertId ();
    }
}
