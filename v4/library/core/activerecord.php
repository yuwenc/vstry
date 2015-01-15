<?php
namespace Core;

/**
 * sql生成类
 * 
 * 操作演示
 * @code php
 * 
 * $ar = new Activerecord ();
 * 
 * $sql = $ar->insert_batch('d_user', array(
 * 	  array('user_nickname'=>'黄瓜萝卜青菜', 'user_email'=>'279537592@qq.com', 'user_mobile'=>'18620190627'),
 * 	  array('user_nickname'=>'黄瓜', 'user_email'=>'yuwenc@live.cn', 'user_mobile'=>'18620190628'),
 * ));
 * echo $sql;
 * echo "\n";
 * 
 * $sql = $ar->from ( 'd_user' )
 *           ->where ( 'user_id', 120004 )
 *           ->count_all_results();
 * echo $sql;
 * echo "\n";
 * 
 * $sql = $ar->from ( 'd_user' )
 *           ->where ( 'user_id', 120004 )
 *           ->get();
 * echo $sql;
 * echo "\n";
 * 
 * $sql = $ar->where(array('user_id'=>120004))
 *           ->set('user_nickname', 'yuwenc')
 *           ->update('d_user');
 * echo $sql;
 * echo "\n";
 * 
 * $sql = $ar->where(array('user_id'=>120004))
 *           ->delete('d_user');
 * echo $sql;
 * echo "\n";
 * 
 * 
 * @endcode
 * 
 * 此类仅仅生成sql语句，并不进行数据库连接
 * 
 */
class Activerecord
{
	protected $ar_select             = array ();
	protected $ar_distinct           = FALSE;
	protected $ar_from               = array ();
	protected $ar_join               = array ();
	protected $ar_where              = array ();
	protected $ar_like               = array ();
	protected $ar_groupby            = array ();
	protected $ar_having             = array ();
	protected $ar_keys               = array ();
	protected $ar_limit              = FALSE;
	protected $ar_offset             = FALSE;
	protected $ar_order              = FALSE;
	protected $ar_orderby            = array ();
	protected $ar_set                = array ();
	protected $ar_wherein            = array ();
	protected $ar_aliased_tables     = array ();
	protected $ar_store_array        = array ();
	protected $ar_no_escape          = array ();
	protected $_protect_identifiers  = true;
	protected $swap_pre              = '';
	protected $dbprefix              = '';
	protected $_reserved_identifiers = array ('*');
	protected $_escape_char          = '`';
	protected $_like_escape_str      = '';
	protected $_like_escape_chr      = '';
	protected $delete_hack           = TRUE;
	protected $_count_string         = 'SELECT COUNT(*) AS ';
	protected $_random_keyword       = ' RAND()';

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	public function select($select = '*', $escape = NULL)
	{
		if (is_string ( $select ))
		{
			$select = explode ( ',', $select );
		}
		foreach ( $select as $val )
		{
			$val = trim ( $val );
			if ($val != '')
			{
				$this->ar_select [] = $val;
				$this->ar_no_escape [] = $escape;
			}
		}
		return $this;
	}

	/**
	 * Select Max
	 *
	 * Generates a SELECT MAX(field) portion of a query
	 *
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	\Core\Activerecord
	 */
	public function select_max($select = '', $alias = '')
	{
		return $this->_max_min_avg_sum ( $select, $alias, 'MAX' );
	}

	/**
	 * Select Min
	 *
	 * Generates a SELECT MIN(field) portion of a query
	 *
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	\Core\Activerecord
	 */
	public function select_min($select = '', $alias = '')
	{
		return $this->_max_min_avg_sum ( $select, $alias, 'MIN' );
	}

	/**
	 * Select Average
	 *
	 * Generates a SELECT AVG(field) portion of a query
	 *
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	\Core\Activerecord
	 */
	public function select_avg($select = '', $alias = '')
	{
		return $this->_max_min_avg_sum ( $select, $alias, 'AVG' );
	}

	/**
	 * Select Sum
	 *
	 * Generates a SELECT SUM(field) portion of a query
	 *
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	\Core\Activerecord
	 */
	public function select_sum($select = '', $alias = '')
	{
		return $this->_max_min_avg_sum ( $select, $alias, 'SUM' );
	}

	/**
	 * Processing Function for the four functions above:
	 *
	 * select_max()
	 * select_min()
	 * select_avg()
	 * select_sum()
	 *
	 * @param	string	the field
	 * @param	string	an alias
	 * @return	\Core\Activerecord
	 */
	protected function _max_min_avg_sum($select = '', $alias = '', $type = 'MAX')
	{
		if (! is_string ( $select ) or $select == '')
		{
			$this->display_error ( 'db_invalid_query' );
		}
		$type = strtoupper ( $type );
		if (! in_array ( $type, array ('MAX', 'MIN', 'AVG', 'SUM' ) ))
		{
			$this->display_error ( 'Invalid function type: ' . $type );
		}
		if ($alias == '')
		{
			$alias = $this->_create_alias_from_table ( trim ( $select ) );
		}
		$sql = $type . '(' . $this->_protect_identifiers ( trim ( $select ) ) . ') AS ' . $alias;
		$this->ar_select [] = $sql;
		return $this;
	}

	/**
	 * Determines the alias name based on the table
	 *
	 * @param	string
	 * @return	string
	 */
	protected function _create_alias_from_table($item)
	{
		if (strpos ( $item, '.' ) !== FALSE)
		{
			return end ( explode ( '.', $item ) );
		}
		return $item;
	}

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @param	bool
	 * @return	\Core\Activerecord
	 */
	public function distinct($val = TRUE)
	{
		$this->ar_distinct = (is_bool ( $val )) ? $val : TRUE;
		return $this;
	}

	/**
	 * From
	 *
	 * Generates the FROM portion of the query
	 *
	 * @param	mixed	can be a string or array
	 * @return	\Core\Activerecord
	 */
	public function from($from)
	{
		foreach ( ( array ) $from as $val )
		{
			if (strpos ( $val, ',' ) !== FALSE)
			{
				foreach ( explode ( ',', $val ) as $v )
				{
					$v = trim ( $v );
					$this->_track_aliases ( $v );
					$this->ar_from [] = $this->_protect_identifiers ( $v, TRUE, NULL, FALSE );
				}
			}
			else
			{
				$val = trim ( $val );
				$this->_track_aliases ( $val );
				$this->ar_from [] = $this->_protect_identifiers ( $val, TRUE, NULL, FALSE );
			}
		}
		return $this;
	}

	/**
	 * Join
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @param	string
	 * @param	string	the join condition
	 * @param	string	the type of join
	 * @return	\Core\Activerecord
	 */
	public function join($table, $cond, $type = '')
	{
		if ($type != '')
		{
			$type = strtoupper ( trim ( $type ) );
			if (! in_array ( $type, array ('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER' ) ))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}
		// Extract any aliases that might exist.  We use this information
		// in the _protect_identifiers to know whether to add a table prefix
		$this->_track_aliases ( $table );
		// Strip apart the condition and protect the identifiers
		if (preg_match ( '/([\w\.]+)([\W\s]+)(.+)/', $cond, $match ))
		{
			$match [1] = $this->_protect_identifiers ( $match [1] );
			$match [3] = $this->_protect_identifiers ( $match [3] );
			$cond = $match [1] . $match [2] . $match [3];
		}
		// Assemble the JOIN statement
		$join = $type . 'JOIN ' . $this->_protect_identifiers ( $table, TRUE, NULL, FALSE ) . ' ON ' . $cond;
		$this->ar_join [] = $join;
		return $this;
	}
	
	/**
	 * Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @param	mixed
	 * @param	mixed
	 * @return	\Core\Activerecord
	 */
	public function where($key, $value = NULL, $escape = TRUE)
	{
		return $this->_where ( $key, $value, 'AND ', $escape );
	}

	/**
	 * OR Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @param	mixed
	 * @param	mixed
	 * @return	\Core\Activerecord
	 */
	public function or_where($key, $value = NULL, $escape = TRUE)
	{
		return $this->_where ( $key, $value, 'OR ', $escape );
	}

	/**
	 * Where
	 *
	 * Called by where() or or_where()
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	protected function _where($key, $value = NULL, $type = 'AND ', $escape = NULL)
	{
	    if (empty($key))
	    {
	        return $this;
	    }
		if (! is_array ( $key ))
		{
			$key = array ($key => $value );
		}
		// If the escape value was not set will will base it on the global setting
		if (! is_bool ( $escape ))
		{
			$escape = $this->_protect_identifiers;
		}
		foreach ( $key as $k => $v )
		{
			$prefix = (count ( $this->ar_where ) == 0) ? '' : $type;
			if (is_null ( $v ) && ! $this->_has_operator ( $k ))
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}
			if (! is_null ( $v ))
			{
				if ($escape === TRUE)
				{
					$k = $this->_protect_identifiers ( $k, FALSE, $escape );
					$v = ' ' . $this->escape ( $v );
				}
				if (! $this->_has_operator ( $k ))
				{
					$k .= ' = ';
				}
			}
			else
			{
				$k = $this->_protect_identifiers ( $k, FALSE, $escape );
			}
			$this->ar_where [] = $prefix . $k . $v;
		}
		return $this;
	}

	/**
	 * Where_in
	 *
	 * Generates a WHERE field IN ('item', 'item') SQL query joined with
	 * AND if appropriate
	 *
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @return	\Core\Activerecord
	 */
	public function where_in($key = NULL, $values = NULL)
	{
		return $this->_where_in ( $key, $values );
	}

	/**
	 * Where_in_or
	 *
	 * Generates a WHERE field IN ('item', 'item') SQL query joined with
	 * OR if appropriate
	 *
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @return	\Core\Activerecord
	 */
	public function or_where_in($key = NULL, $values = NULL)
	{
		return $this->_where_in ( $key, $values, FALSE, 'OR ' );
	}

	/**
	 * Where_not_in
	 *
	 * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
	 * with AND if appropriate
	 *
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @return	\Core\Activerecord
	 */
	public function where_not_in($key = NULL, $values = NULL)
	{
		return $this->_where_in ( $key, $values, TRUE );
	}

	/**
	 * Where_not_in_or
	 *
	 * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
	 * with OR if appropriate
	 *
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @return	\Core\Activerecord
	 */
	public function or_where_not_in($key = NULL, $values = NULL)
	{
		return $this->_where_in ( $key, $values, TRUE, 'OR ' );
	}

	/**
	 * Where_in
	 *
	 * Called by where_in, where_in_or, where_not_in, where_not_in_or
	 *
	 * @param	string	The field to search
	 * @param	array	The values searched on
	 * @param	boolean	If the statement would be IN or NOT IN
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND ')
	{
		if ($key === NULL or $values === NULL)
		{
			return $this;
		}
		if (! is_array ( $values ))
		{
			$values = array ($values );
		}
		$not = ($not) ? ' NOT' : '';
		foreach ( $values as $value )
		{
			$this->ar_wherein [] = $this->escape ( $value );
		}
		$prefix = (count ( $this->ar_where ) == 0) ? '' : $type;
		$where_in = $prefix . $this->_protect_identifiers ( $key ) . $not . " IN (" . implode ( ", ", $this->ar_wherein ) . ") ";
		$this->ar_where [] = $where_in;
		$this->ar_wherein = array ();
		return $this;
	}

	/**
	 * Like
	 *
	 * Generates a %LIKE% portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param string $slide ['before', 'both', 'right']
	 * @return	\Core\Activerecord
	 */
	public function like($field, $match = '', $side = 'both')
	{
		return $this->_like ( $field, $match, 'AND ', $side );
	}

	/**
	 * Not Like
	 *
	 * Generates a NOT LIKE portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @param	mixed
	 * @param	mixed
	 * @return	\Core\Activerecord
	 */
	public function not_like($field, $match = '', $side = 'both')
	{
		return $this->_like ( $field, $match, 'AND ', $side, 'NOT' );
	}

	/**
	 * OR Like
	 *
	 * Generates a %LIKE% portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @param	mixed
	 * @param	mixed
	 * @return	\Core\Activerecord
	 */
	public function or_like($field, $match = '', $side = 'both')
	{
		return $this->_like ( $field, $match, 'OR ', $side );
	}

	/**
	 * OR Not Like
	 *
	 * Generates a NOT LIKE portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @param	mixed
	 * @param	mixed
	 * @return	\Core\Activerecord
	 */
	public function or_not_like($field, $match = '', $side = 'both')
	{
		return $this->_like ( $field, $match, 'OR ', $side, 'NOT' );
	}

	/**
	 * Like
	 *
	 * Called by like() or orlike()
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
	{
	    if (empty($field))
	    {
	        return $this;
	    }
		if (! is_array ( $field ))
		{
			$field = array ($field => $match );
		}
		foreach ( $field as $k => $v )
		{
			$k = $this->_protect_identifiers ( $k );
			$prefix = (count ( $this->ar_like ) == 0) ? '' : $type;
			$v = $this->escape_like_str ( $v );
			if ($side == 'before')
			{
				$like_statement = $prefix . " $k $not LIKE '%{$v}'";
			}
			elseif ($side == 'after')
			{
				$like_statement = $prefix . " $k $not LIKE '{$v}%'";
			}
			else
			{
				$like_statement = $prefix . " $k $not LIKE '%{$v}%'";
			}
			// some platforms require an escape sequence definition for LIKE wildcards
			if ($this->_like_escape_str != '')
			{
				$like_statement = $like_statement . sprintf ( $this->_like_escape_str, $this->_like_escape_chr );
			}
			$this->ar_like [] = $like_statement;
		}
		return $this;
	}
	
	/**
	 * Escape LIKE String
	 *
	 * Calls the individual driver for platform
	 * specific escaping for LIKE conditions
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	function escape_like_str($str)
	{
		return $this->escape_str($str, TRUE);
	}

	/**
	 * GROUP BY
	 *
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	public function group_by($by)
	{
		if (is_string ( $by ))
		{
			$by = explode ( ',', $by );
		}
		foreach ( $by as $val )
		{
			$val = trim ( $val );
			if ($val != '')
			{
				$this->ar_groupby [] = $this->_protect_identifiers ( $val );
			}
		}
		return $this;
	}

	/**
	 * Sets the HAVING value
	 *
	 * Separates multiple calls with AND
	 *
	 * @param	string
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	public function having($key, $value = '', $escape = TRUE)
	{
		return $this->_having ( $key, $value, 'AND ', $escape );
	}

	/**
	 * Sets the OR HAVING value
	 *
	 * Separates multiple calls with OR
	 *
	 * @param	string
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	public function or_having($key, $value = '', $escape = TRUE)
	{
		return $this->_having ( $key, $value, 'OR ', $escape );
	}

	/**
	 * Sets the HAVING values
	 *
	 * Called by having() or or_having()
	 *
	 * @param	string
	 * @param	string
	 * @return	\Core\Activerecord
	 */
	protected function _having($key, $value = '', $type = 'AND ', $escape = TRUE)
	{
		if (! is_array ( $key ))
		{
			$key = array ($key => $value );
		}
		foreach ( $key as $k => $v )
		{
			$prefix = (count ( $this->ar_having ) == 0) ? '' : $type;
			if ($escape === TRUE)
			{
				$k = $this->_protect_identifiers ( $k );
			}
			if (! $this->_has_operator ( $k ))
			{
				$k .= ' = ';
			}
			if ($v != '')
			{
				$v = ' ' . $this->escape ( $v );
			}
			$this->ar_having [] = $prefix . $k . $v;
		}
		return $this;
	}

	/**
	 * Sets the ORDER BY value
	 *
	 * @param	string
	 * @param	string	direction: asc or desc
	 * @return	\Core\Activerecord
	 */
	public function order_by($orderby, $direction = '')
	{
	    if (empty($orderby))
	    {
	    	return $this;
	    }
		if (strtolower ( $direction ) == 'random')
		{
		    // Random results want or don't need a field name
			$orderby = ''; 
			$direction = $this->_random_keyword;
		}
		elseif (trim ( $direction ) != '')
		{
			$direction = (in_array ( strtoupper ( trim ( $direction ) ), array ('ASC', 'DESC' ), TRUE )) ? ' ' . $direction : ' ASC';
		}
		if (strpos ( $orderby, ',' ) !== FALSE)
		{
			$temp = array ();
			foreach ( explode ( ',', $orderby ) as $part )
			{
				$part = trim ( $part );
				if (! in_array ( $part, $this->ar_aliased_tables ))
				{
					$part = $this->_protect_identifiers ( trim ( $part ) );
				}
				$temp [] = $part;
			}
			$orderby = implode ( ', ', $temp );
		}
		else if ($direction != $this->_random_keyword)
		{
			$orderby = $this->_protect_identifiers ( $orderby );
		}
		$this->ar_orderby [] = $orderby . $direction;
		return $this;
	}

	/**
	 * Sets the LIMIT value
	 *
	 * @param	integer	the limit value
	 * @param	integer	the offset value
	 * @return	\Core\Activerecord
	 */
	public function limit($limit, $offset = 0)
	{
	    if (empty($limit) && empty($offset))
	    {
	        return $this;
	    }
	    $this->ar_limit = ( int ) $limit;
	    $this->ar_offset = ( int ) $offset;
		return $this;
	}	
	
	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @access	public
	 * @param	string	the sql query string
	 * @param	integer	the number of rows to limit the query to
	 * @param	integer	the offset value
	 * @return	string
	 */
	protected function _limit($sql, $limit, $offset)
	{
	    if ($offset == 0)
	    {
	        $offset = '';
	    }
	    else
	    {
	        $offset .= ", ";
	    }
	
	    return $sql."LIMIT ".$offset.$limit;
	}

	/**
	 * Sets the OFFSET value
	 *
	 * @param	integer	the offset value
	 * @return	\Core\Activerecord
	 */
	public function offset($offset)
	{
		$this->ar_offset = $offset;
		return $this;
	}

	/**
	 * The "set" function.  Allows key/value pairs to be set for inserting or updating
	 *
	 * @param	mixed
	 * @param	string
	 * @param	boolean
	 * @return	\Core\Activerecord
	 */
	public function set($key, $value = '', $escape = TRUE)
	{
		$key = $this->_object_to_array ( $key );
		if (! is_array ( $key ))
		{
			$key = array ($key => $value );
		}
		foreach ( $key as $k => $v )
		{
			if ($escape === FALSE)
			{
				$this->ar_set [$this->_protect_identifiers ( $k )] = $v;
			}
			else
			{
				$this->ar_set [$this->_protect_identifiers ( $k, FALSE, TRUE )] = $this->escape ( $v );
			}
		}
		return $this;
	}

	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @param	string	the table
	 * @param	string	the limit clause
	 * @param	string	the offset clause
	 * @return	string  the generate sql
	 */
	public function get($table = '', $limit = null, $offset = null)
	{
		if ($table != '')
		{
			$this->_track_aliases ( $table );
			$this->from ( $table );
		}
		if (! is_null ( $limit ))
		{
			$this->limit ( $limit, $offset );
		}
		$sql = $this->_compile_select ();
		$this->_reset_select ();
		return $sql;
	}

	/**
	 * "Count All Results" query
	 *
	 * Generates a platform-specific query string that counts all records
	 * returned by an Active Record query.
	 *
	 * @param	string
	 * @return	string
	 */
	public function count_all_results($table = '')
	{
		if ($table != '')
		{
			$this->_track_aliases ( $table );
			$this->from ( $table );
		}
		$sql = $this->_compile_select ( $this->_count_string . $this->_protect_identifiers ( 'numrows' ) );
		$this->_reset_select ();
		return $sql;
	}

	/**
	 * Insert
	 *
	 * Compiles an insert string and runs the query
	 *
	 * @param	string	the table to insert data into
	 * @param	array	an associative array of insert values
	 * @return	object
	 */
	public function insert($table = '', $set = NULL)
	{
		if (! is_null ( $set ))
		{
			$this->set ( $set );
		}
		if (count ( $this->ar_set ) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error ( 'db_must_use_set' );
			}
			return FALSE;
		}
		if ($table == '')
		{
			if (! isset ( $this->ar_from [0] ))
			{
				if ($this->db_debug)
				{
					return $this->display_error ( 'db_must_set_table' );
				}
				return FALSE;
			}
			$table = $this->ar_from [0];
		}
		$sql = $this->_insert ( $this->_protect_identifiers ( $table, TRUE, NULL, FALSE ), array_keys ( $this->ar_set ), array_values ( $this->ar_set ) );
		$this->_reset_write ();
		return $sql;
	}
	
	/**
	 * 插入数据
	 * @param string $table
	 * @param array $keys
	 * @param array $values
	 */
	private function _insert($table, $keys, $values)
	{
	    return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
	}
	
	/**
	 * Insert_Batch
	 *
	 * Compiles batch insert strings and runs the queries
	 *
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of insert values
	 * @return	object
	 */
	public function insert_batch($table = '', $set = NULL)
	{
		$sql = array();
		if ( ! is_null($set))
		{
			$this->set_insert_batch($set);
		}

		if (count($this->ar_set) == 0)
		{
			if ($this->db_debug)
			{
				//No valid data array.  Folds in cases where keys and values did not match up
				return $this->display_error('db_must_use_set');
			}
			return FALSE;
		}

		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}

			$table = $this->ar_from[0];
		}

		// Batch this baby
		for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100)
		{
			$sql[] = $this->_insert_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), $this->ar_keys, array_slice($this->ar_set, $i, 100));
		}
		$this->_reset_write();
		return $sql;
	}
	
	/**
	 * Insert_batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	protected function _insert_batch($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ".implode(', ', $values);
	}

	/**
	 * The "set_insert_batch" function.  Allows key/value pairs to be set for batch inserts
	 *
	 * @param	mixed
	 * @param	string
	 * @param	boolean
	 * @return	\Core\Activerecord
	 */
	public function set_insert_batch($key, $value = '', $escape = TRUE)
	{
		$key = $this->_object_to_array_batch ( $key );
		if (! is_array ( $key ))
		{
			$key = array ($key => $value );
		}
		$keys = array_keys ( current ( $key ) );
		sort ( $keys );
		foreach ( $key as $row )
		{
			if (count ( array_diff ( $keys, array_keys ( $row ) ) ) > 0 or count ( array_diff ( array_keys ( $row ), $keys ) ) > 0)
			{
				// batch function above returns an error on an empty array
				$this->ar_set [] = array ();
				return;
			}
			ksort ( $row ); // puts $row in the same order as our keys
			if ($escape === FALSE)
			{
				$this->ar_set [] = '(' . implode ( ',', $row ) . ')';
			}
			else
			{
				$clean = array ();
				foreach ( $row as $value )
				{
					$clean [] = $this->escape ( $value );
				}
				$this->ar_set [] = '(' . implode ( ',', $clean ) . ')';
			}
		}
		foreach ( $keys as $k )
		{
			$this->ar_keys [] = $this->_protect_identifiers ( $k );
		}
		return $this;
	}

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query
	 *
	 * @param	string	the table to replace data into
	 * @param	array	an associative array of insert values
	 * @return	object
	 */
	public function replace($table = '', $set = NULL)
	{
		if (! is_null ( $set ))
		{
			$this->set ( $set );
		}
		if (count ( $this->ar_set ) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error ( 'db_must_use_set' );
			}
			return FALSE;
		}
		if ($table == '')
		{
			if (! isset ( $this->ar_from [0] ))
			{
				if ($this->db_debug)
				{
					return $this->display_error ( 'db_must_set_table' );
				}
				return FALSE;
			}
			$table = $this->ar_from [0];
		}
		$sql = $this->_replace ( $this->_protect_identifiers ( $table, TRUE, NULL, FALSE ), array_keys ( $this->ar_set ), array_values ( $this->ar_set ) );
		$this->_reset_write ();
		return $sql;
	}
	
	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	protected function _replace($table, $keys, $values)
	{
	    return "REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
	}

	/**
	 * Update
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of update values
	 * @param	mixed	the where clause
	 * @return	object
	 */
	public function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
	{
		if (! is_null ( $set ))
		{
			$this->set ( $set );
		}
		if (count ( $this->ar_set ) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error ( 'db_must_use_set' );
			}
			return FALSE;
		}
		if ($table == '')
		{
			if (! isset ( $this->ar_from [0] ))
			{
				if ($this->db_debug)
				{
					return $this->display_error ( 'db_must_set_table' );
				}
				return FALSE;
			}
			$table = $this->ar_from [0];
		}
		if ($where != NULL)
		{
			$this->where ( $where );
		}
		if ($limit != NULL)
		{
			$this->limit ( $limit );
		}
		$sql = $this->_update ( $this->_protect_identifiers ( $table, TRUE, NULL, FALSE ), $this->ar_set, $this->ar_where, $this->ar_orderby, $this->ar_limit );
		$this->_reset_write ();
		return $sql;
	}
	
	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of update values
	 * @param	string	the where key
	 * @return	object
	 */
	public function update_batch($table = '', $set = NULL, $index = NULL)
	{
		$sql = array();

		if (is_null($index))
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_must_use_index');
			}

			return FALSE;
		}

		if ( ! is_null($set))
		{
			$this->set_update_batch($set, $index);
		}

		if (count($this->ar_set) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_must_use_set');
			}

			return FALSE;
		}

		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				if ($this->db_debug)
				{
					return $this->display_error('db_must_set_table');
				}
				return FALSE;
			}

			$table = $this->ar_from[0];
		}

		// Batch this baby
		for ($i = 0, $total = count($this->ar_set); $i < $total; $i = $i + 100)
		{
			$sql[] = $this->_update_batch($this->_protect_identifiers($table, TRUE, NULL, FALSE), array_slice($this->ar_set, $i, 100), $this->_protect_identifiers($index), $this->ar_where);
		}

		$this->_reset_write();
		
		return $sql;
	}
	
	/**
	 * The "set_update_batch" function.  Allows key/value pairs to be set for batch updating
	 *
	 * @param	array
	 * @param	string
	 * @param	boolean
	 * @return	\Core\Activerecord
	 */
	public function set_update_batch($key, $index = '', $escape = TRUE)
	{
		$key = $this->_object_to_array_batch($key);
	
		if ( ! is_array($key))
		{
			return $this->display_error('set_update_batch params of $key must be an array');
		}
	
		foreach ($key as $k => $v)
		{
			$index_set = FALSE;
			$clean = array();
	
			foreach ($v as $k2 => $v2)
			{
				if ($k2 == $index)
				{
					$index_set = TRUE;
				}
	
				if ($escape === FALSE)
				{
					$clean[$this->_protect_identifiers($k2)] = $v2;
				}
				else
				{
					$clean[$this->_protect_identifiers($k2)] = $this->escape($v2);
				}
			}
	
			if ($index_set == FALSE)
			{
				return $this->display_error('db_batch_missing_index');
			}
	
			$this->ar_set[] = $clean;
		}
	
		return $this;
	}
	
	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @return	string
	 */
	function _update_batch($table, $values, $index, $where = NULL)
	{
		$ids = array();
		$where = ($where != '' AND count($where) >=1) ? implode(" ", $where).' AND ' : '';
	
		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];
	
			foreach (array_keys($val) as $field)
			{
				if ($field != $index)
				{
					$final[$field][] =  'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
				}
			}
		}
	
		$sql = "UPDATE ".$table." SET ";
		$cases = '';
	
		foreach ($final as $k => $v)
		{
			$cases .= $k.' = CASE '."\n";
			foreach ($v as $row)
			{
				$cases .= $row."\n";
			}
	
			$cases .= 'ELSE '.$k.' END, ';
		}
	
		$sql .= substr($cases, 0, -2);
	
		$sql .= ' WHERE '.$where.$index.' IN ('.implode(',', $ids).')';
	
		return $sql;
	}

	/**
	 * Empty Table
	 *
	 * Compiles a delete string and runs "DELETE FROM table"
	 *
	 * @param	string	the table to empty
	 * @return	object
	 */
	public function empty_table($table = '')
	{
		if ($table == '')
		{
			if (! isset ( $this->ar_from [0] ))
			{
				if ($this->db_debug)
				{
					return $this->display_error ( 'db_must_set_table' );
				}
				return FALSE;
			}
			$table = $this->ar_from [0];
		}
		else
		{
			$table = $this->_protect_identifiers ( $table, TRUE, NULL, FALSE );
		}
		$sql = $this->_delete ( $table );
		$this->_reset_write ();
		return $sql;
	}

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param	mixed	the table(s) to delete from. String or array
	 * @param	mixed	the where clause
	 * @param	mixed	the limit clause
	 * @param	boolean
	 * @return	object
	 */
	public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE)
	{
		if ($table == '')
		{
			if (! isset ( $this->ar_from [0] ))
			{
				if ($this->db_debug)
				{
					return $this->display_error ( 'db_must_set_table' );
				}
				return FALSE;
			}
			$table = $this->ar_from [0];
		}
		elseif (is_array ( $table ))
		{
			foreach ( $table as $single_table )
			{
				$this->delete ( $single_table, $where, $limit, FALSE );
			}
			$this->_reset_write ();
			return;
		}
		else
		{
			$table = $this->_protect_identifiers ( $table, TRUE, NULL, FALSE );
		}
		if ($where != '')
		{
			$this->where ( $where );
		}
		if ($limit != NULL)
		{
			$this->limit ( $limit );
		}
		if (count ( $this->ar_where ) == 0 && count ( $this->ar_wherein ) == 0 && count ( $this->ar_like ) == 0)
		{
			if ($this->db_debug)
			{
				return $this->display_error ( 'db_del_must_use_where' );
			}
			return FALSE;
		}
		$sql = $this->_delete ( $table, $this->ar_where, $this->ar_like, $this->ar_limit );
		if ($reset_data)
		{
			$this->_reset_write ();
		}
		return $sql;
	}

	/**
	 * DB Prefix
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @param	string	the table
	 * @return	string
	 */
	public function dbprefix($table = '')
	{
		if ($table == '')
		{
			$this->display_error ( 'db_table_name_required' );
		}
		return $this->dbprefix . $table;
	}

	/**
	 * Set DB Prefix
	 *
	 * Set's the DB Prefix to something new without needing to reconnect
	 *
	 * @param	string	the prefix
	 * @return	string
	 */
	public function set_dbprefix($prefix = '')
	{
		return $this->dbprefix = $prefix;
	}

	/**
	 * Track Aliases
	 *
	 * Used to track SQL statements written with aliased tables.
	 *
	 * @param	string	The table to inspect
	 * @return	string
	 */
	protected function _track_aliases($table)
	{
		if (is_array ( $table ))
		{
			foreach ( $table as $t )
			{
				$this->_track_aliases ( $t );
			}
			return;
		}
		// Does the string contain a comma?  If so, we need to separate
		// the string into discreet statements
		if (strpos ( $table, ',' ) !== FALSE)
		{
			return $this->_track_aliases ( explode ( ',', $table ) );
		}
		// if a table alias is used we can recognize it by a space
		if (strpos ( $table, " " ) !== FALSE)
		{
			// if the alias is written with the AS keyword, remove it
			$table = preg_replace ( '/ AS /i', ' ', $table );
			// Grab the alias
			$table = trim ( strrchr ( $table, " " ) );
			// Store the alias, if it doesn't already exist
			if (! in_array ( $table, $this->ar_aliased_tables ))
			{
				$this->ar_aliased_tables [] = $table;
			}
		}
	}

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.  The get() function calls it.
	 *
	 * @return	string
	 */
	protected function _compile_select($select_override = FALSE)
	{
		// Write the "select" portion of the query
		if ($select_override !== FALSE)
		{
			$sql = $select_override;
		}
		else
		{
			$sql = (! $this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';
			if (count ( $this->ar_select ) == 0)
			{
				$sql .= '*';
			}
			else
			{
				// Cycle through the "select" portion of the query and prep each column name.
				// The reason we protect identifiers here rather then in the select() function
				// is because until the user calls the from() function we don't know if there are aliases
				foreach ( $this->ar_select as $key => $val )
				{
					$no_escape = isset ( $this->ar_no_escape [$key] ) ? $this->ar_no_escape [$key] : NULL;
					$this->ar_select [$key] = $this->_protect_identifiers ( $val, FALSE, $no_escape );
				}
				$sql .= implode ( ', ', $this->ar_select );
			}
		}
		// Write the "FROM" portion of the query
		if (count ( $this->ar_from ) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= $this->_from_tables ( $this->ar_from );
		}
		// Write the "JOIN" portion of the query
		if (count ( $this->ar_join ) > 0)
		{
			$sql .= "\n";
			$sql .= implode ( "\n", $this->ar_join );
		}
		// Write the "WHERE" portion of the query
		if (count ( $this->ar_where ) > 0 or count ( $this->ar_like ) > 0)
		{
			$sql .= "\nWHERE ";
		}
		$sql .= implode ( "\n", $this->ar_where );
		// Write the "LIKE" portion of the query
		if (count ( $this->ar_like ) > 0)
		{
			if (count ( $this->ar_where ) > 0)
			{
				$sql .= "\nAND ";
			}
			$sql .= implode ( "\n", $this->ar_like );
		}
		// Write the "GROUP BY" portion of the query
		if (count ( $this->ar_groupby ) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode ( ', ', $this->ar_groupby );
		}
		// Write the "HAVING" portion of the query
		if (count ( $this->ar_having ) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode ( "\n", $this->ar_having );
		}
		// Write the "ORDER BY" portion of the query
		if (count ( $this->ar_orderby ) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode ( ', ', $this->ar_orderby );
			if ($this->ar_order !== FALSE)
			{
				$sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
			}
		}
		// Write the "LIMIT" portion of the query
		if (is_numeric ( $this->ar_limit ))
		{
			$sql .= "\n";
			$sql = $this->_limit ( $sql, $this->ar_limit, $this->ar_offset );
		}
		return $sql;
	}

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param	object
	 * @return	array
	 */
	public function _object_to_array($object)
	{
		if (! is_object ( $object ))
		{
			return $object;
		}
		$array = array ();
		foreach ( get_object_vars ( $object ) as $key => $val )
		{
			// There are some built in keys we need to ignore for this conversion
			if (! is_object ( $val ) && ! is_array ( $val ) && $key != '_parent_name')
			{
				$array [$key] = $val;
			}
		}
		return $array;
	}

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param	object
	 * @return	array
	 */
	public function _object_to_array_batch($object)
	{
		if (! is_object ( $object ))
		{
			return $object;
		}
		$array = array ();
		$out = get_object_vars ( $object );
		$fields = array_keys ( $out );
		foreach ( $fields as $val )
		{
			if ($val != '_parent_name')
			{
				$i = 0;
				foreach ( $out [$val] as $data )
				{
					$array [$i] [$val] = $data;
					$i ++;
				}
			}
		}
		return $array;
	}

	/**
	 * Resets the active record values.  Called by the get() function
	 *
	 * @param	array	An array of fields to reset
	 * @return	void
	 */
	protected function _reset_run($ar_reset_items)
	{
		foreach ( $ar_reset_items as $item => $default_value )
		{
			if (! in_array ( $item, $this->ar_store_array ))
			{
				$this->$item = $default_value;
			}
		}
	}

	/**
	 * Resets the active record values.  Called by the get() function
	 *
	 * @return	void
	 */
	protected function _reset_select()
	{
		$this->_reset_run ( array (
		    'ar_select'         => array (), 
		    'ar_from'           => array (), 
		    'ar_join'           => array (), 
		    'ar_where'          => array (), 
		    'ar_like'           => array (), 
		    'ar_groupby'        => array (), 
		    'ar_having'         => array (), 
		    'ar_orderby'        => array (), 
		    'ar_wherein'        => array (), 
		    'ar_aliased_tables' => array (), 
		    'ar_no_escape'      => array (), 
		    'ar_distinct'       => FALSE, 
		    'ar_limit'          => FALSE, 
		    'ar_offset'         => FALSE, 
		    'ar_order'          => FALSE 
		) );
	}

	/**
	 * Resets the active record "write" values.
	 *
	 * Called by the insert() update() insert_batch() update_batch() and delete() functions
	 *
	 * @return	void
	 */
	protected function _reset_write()
	{
		$this->_reset_run ( array (
		    'ar_set'       => array (), 
		    'ar_from'      => array (), 
		    'ar_where'     => array (), 
		    'ar_like'      => array (), 
		    'ar_orderby'   => array (), 
		    'ar_keys'      => array (), 
		    'ar_limit'     => FALSE, 
		    'ar_order'     => FALSE 
		) );
	}

	/**
	 * Protect Identifiers
	 *
	 * This function is used extensively by the Active Record class, and by
	 * a couple functions in this class.
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it.  Some logic is necessary in order to deal with
	 * column names that include the path.  Consider a query like this:
	 *
	 * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @access	private
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @param	bool
	 * @return	string
	 */
	function _protect_identifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE)
	{
		if (! is_bool ( $protect_identifiers ))
		{
			$protect_identifiers = $this->_protect_identifiers;
		}
		if (is_array ( $item ))
		{
			$escaped_array = array ();
			foreach ( $item as $k => $v )
			{
				$escaped_array [$this->_protect_identifiers ( $k )] = $this->_protect_identifiers ( $v );
			}
			return $escaped_array;
		}
		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace ( '/[\t ]+/', ' ', $item );
		// If the item has an alias declaration we remove it and set it aside.
		// Basically we remove everything to the right of the first space
		$alias = '';
		if (strpos ( $item, ' ' ) !== FALSE)
		{
			$alias = strstr ( $item, " " );
			$item = substr ( $item, 0, - strlen ( $alias ) );
		}
		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix.  There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		if (strpos ( $item, '(' ) !== FALSE)
		{
			return $item . $alias;
		}
		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos ( $item, '.' ) !== FALSE)
		{
			$parts = explode ( '.', $item );
			// Does the first segment of the exploded item match
			// one of the aliases previously identified?  If so,
			// we have nothing more to do other than escape the item
			if (in_array ( $parts [0], $this->ar_aliased_tables ))
			{
				if ($protect_identifiers === TRUE)
				{
					foreach ( $parts as $key => $val )
					{
						if (! in_array ( $val, $this->_reserved_identifiers ))
						{
							$parts [$key] = $this->_escape_identifiers ( $val );
						}
					}
					$item = implode ( '.', $parts );
				}
				return $item . $alias;
			}
			// Is there a table prefix defined in the config file?  If not, no need to do anything
			if ($this->dbprefix != '')
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if (isset ( $parts [3] ))
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif (isset ( $parts [2] ))
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}
				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
				if ($field_exists == FALSE)
				{
					$i ++;
				}
				// Verify table prefix and replace if necessary
				if ($this->swap_pre != '' && strncmp ( $parts [$i], $this->swap_pre, strlen ( $this->swap_pre ) ) === 0)
				{
					$parts [$i] = preg_replace ( "/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $parts [$i] );
				}
				// We only add the table prefix if it does not already exist
				if (substr ( $parts [$i], 0, strlen ( $this->dbprefix ) ) != $this->dbprefix)
				{
					$parts [$i] = $this->dbprefix . $parts [$i];
				}
				// Put the parts back together
				$item = implode ( '.', $parts );
			}
			if ($protect_identifiers === TRUE)
			{
				$item = $this->_escape_identifiers ( $item );
			}
			return $item . $alias;
		}
		// Is there a table prefix?  If not, no need to insert it
		if ($this->dbprefix != '')
		{
			// Verify table prefix and replace if necessary
			if ($this->swap_pre != '' && strncmp ( $item, $this->swap_pre, strlen ( $this->swap_pre ) ) === 0)
			{
				$item = preg_replace ( "/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $item );
			}
			// Do we prefix an item with no segments?
			if ($prefix_single == TRUE and substr ( $item, 0, strlen ( $this->dbprefix ) ) != $this->dbprefix)
			{
				$item = $this->dbprefix . $item;
			}
		}
		if ($protect_identifiers === TRUE and ! in_array ( $item, $this->_reserved_identifiers ))
		{
			$item = $this->_escape_identifiers ( $item );
		}
		return $item . $alias;
	}

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	protected function _escape_identifiers($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}
		foreach ( $this->_reserved_identifiers as $id )
		{
			if (strpos ( $item, '.' . $id ) !== FALSE)
			{
				$str = $this->_escape_char . str_replace ( '.', $this->_escape_char . '.', $item );
				// remove duplicates if the user already included the escape
				return preg_replace ( '/[' . $this->_escape_char . ']+/', $this->_escape_char, $str );
			}
		}
		if (strpos ( $item, '.' ) !== FALSE)
		{
			$str = $this->_escape_char . str_replace ( '.', $this->_escape_char . '.' . $this->_escape_char, $item ) . $this->_escape_char;
		}
		else
		{
			$str = $this->_escape_char . $item . $this->_escape_char;
		}
		// remove duplicates if the user already included the escape
		return preg_replace ( '/[' . $this->_escape_char . ']+/', $this->_escape_char, $str );
	}

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	public function escape($str)
	{
		if (is_string ( $str ))
		{
			$str = "'" . $this->escape_str ( $str ) . "'";
		}
		elseif (is_bool ( $str ))
		{
			$str = ($str === FALSE) ? 0 : 1;
		}
		elseif (is_null ( $str ))
		{
			$str = 'NULL';
		}
		return $str;
	}

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	protected function _has_operator($str)
	{
		$str = trim ( $str );
		if (! preg_match ( "/(\s|<|>|!|=|is null|is not null)/i", $str ))
		{
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * From Tables
	 *
	 * This function implicitly groups FROM tables so there is no confusion
	 * about operator precedence in harmony with SQL standards
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	protected function _from_tables($tables)
	{
		if (! is_array ( $tables ))
		{
			$tables = array ($tables );
		}
		return '(' . implode ( ', ', $tables ) . ')';
	}

	/**
	 * Escape String
	 *
	 * @access	public
	 * @param	string
	 * @param	bool	whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	public function escape_str($str, $like = FALSE)
	{
		if (is_array ( $str ))
		{
			foreach ( $str as $key => $val )
			{
				$str [$key] = $this->escape_str ( $val, $like );
			}
			return $str;
		}
		$str = addslashes ( $str );
		if ($like === TRUE)
		{
			$str = str_replace ( array ('%', '_' ), array ('\\%', '\\_' ), $str );
		}
		return $str;
	}

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @param	array	the orderby clause
	 * @param	array	the limit clause
	 * @return	string
	 */
	protected function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach ( $values as $key => $val )
		{
			$valstr [] = $key . ' = ' . $val;
		}
		$limit = (! $limit) ? '' : '\nLIMIT ' . $limit;
		$orderby = (count ( $orderby ) >= 1) ? '\nORDER BY ' . implode ( ", ", $orderby ) : '';
		$sql = "UPDATE " . $table . "\nSET " . implode ( ', ', $valstr );
		$sql .= ($where != '' and count ( $where ) >= 1) ? "\nWHERE " . implode ( " ", $where ) : '';
		$sql .= $orderby . $limit;
		return $sql;
	}

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the where clause
	 * @param	string	the limit clause
	 * @return	string
	 */
	protected function _delete($table, $where = array(), $like = array(), $limit = FALSE)
	{
		$conditions = '';
		if (count ( $where ) > 0 or count ( $like ) > 0)
		{
			$conditions = "\nWHERE ";
			$conditions .= implode ( "\n", $this->ar_where );
			if (count ( $where ) > 0 && count ( $like ) > 0)
			{
				$conditions .= "\nAND ";
			}
			$conditions .= implode ( "\n", $like );
		}
		$limit = (! $limit) ? '' : '\nLIMIT ' . $limit;
		return "DELETE FROM " . $table . $conditions . $limit;
	}
	
	/**
	 * 错误输出
	 * @access	public
	 * @param	string	the error message
	 */
	protected function display_error($error = '', $swap = '', $native = FALSE)
	{
		if (is_array ( $error ))
		{
			$message = implode ( ',', $error );
		}
		else
		{
			$message = $error;
		}
		throw new \Exception($message);
	}
}