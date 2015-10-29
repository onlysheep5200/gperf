<?php
/**
 * @package utility 
 */

/**
 * 数据库连接类
 *
 * 继承mysqli并综合部分mysqli_result功能。
 * @uses mysqli
 * @package utility
 */
include_once(dirname(__FILE__).'/config.php');
class DB extends \mysqli {
	public static $instance = NULL;
	private static $_enablePersistent = TRUE;
	private static $isInitialed = FALSE;
	private static $charset = 'utf8';
	private static $host;
	private static $user;
	private static $pwd;
	private static $database;
	/**
	 * 查询结果 
	 *
	 * 通过{@link __invoke()}查询返回的结果。
	 * 
	 * @var mysqli_result|bool
	 * @access public
	 */
	public $result;
	
	/**
	 * SQL语句 
	 * 
	 * 所查询的SQL字符串。
	 * @var string
	 * @access public
	 */
	public $sql;

	public static function get() {
		if(!self::$isInitialed)
			self::initialize();
  		if (self::$instance === NULL || !self::$_enablePersistent) {
			$db = new DB;
			$db->connect(self::$host,self::$user,self::$pwd,self::$database,self::$charset);
			if (self::$_enablePersistent) {
				self::$instance = $db;
			}
			return $db;
		} else {
			return self::$instance;
		}
	}

	public static function initialize()
	{
		self::$host = DB_HOST;
		self::$user = DB_USER;
		self::$pwd = DB_PWD;
		self::$database = DB_NAME;
		self::$isInitialed = TRUE;
	}

	public static function getSingle() {
		if(!self::$isInitialed)
			self::initialize();
		$instance = new DB;
		$instance->connect(self::$host,self::$user,self::$pwd,self::$database,self::$charset);
		return $instance;
	}

	public static function enablePersistent($enable=TRUE) {
		self::$_enablePersistent = $enable;
	}

	public static function insertSQL($arr, $keys=NULL, $enableNULL=TRUE) {
		if ($keys === NULL) {
			$keys = array_keys($arr);
		}
		$ks = array();
		$vs = array();
		foreach ($keys as $k) {
			$ks[] = "`$k`";
			$vs[] = !$enableNULL || $arr[$k] !== NULL ? "'{$arr[$k]}'" : 'NULL';
		}
		return array(implode(', ', $ks), implode(', ', $vs));
	}

	public static function updateSQL($arr, $keys=NULL, $enableNULL=TRUE) {
		if ($keys === NULL) {
			$keys = array_keys($arr);
		}
		$sqls = array();
		foreach ($keys as $k) {
			if ($enableNULL && $arr[$k] === NULL) {
				$sqls[] = "`$k` = NULL";
			} else {
				$sqls[] = "`$k` = '{$arr[$k]}'";
			}
		}
		return implode(', ', $sqls);
	}

	/**
	 * 构造函数 
	 *
	 * 初始化并连接数据库。
	 * 使用持久连接。
	 * @access public
	 * @return void
	 */
	public function __construct($result = NULL) {
		if ($result !== NULL) {
			$this->result = $result;
		}
	}

	public function connect($host, $username, $password, $database, $charset) {
		//$this->mysqli('p:' . DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		$this->mysqli($host, $username, $password, $database);
		$this->set_charset($charset);
	}

	/**
	 * 查询或获取结果行
	 *
	 * 查询SQL语句，或以关联数组的形式返回结果集的一行。
	 * 当提供$sql参数时，返回查询的结果。
	 * 无$sql参数时，返回一行查询结果。
	 * @param string $sql SQL语句
	 * @access public
	 * @return mixed 查询结果
	 */
	public function __invoke($sql = null) {
		if (is_null($sql)) {
			return $this->result->fetch_assoc();
		} else {
			$this->result = $this->query($sql);
			//if (!$this->result && strtolower(substr($sql, 0, 6)) == 'select') {
			if (!$this->result) {
				throw new E($sql.'<br /><span style="color:#F77">'.$this->error.'</span><br />');
			}
			$this->sql = $sql;
			return $this->result;
		}
	}

	/**
	 * 获取数组结果行
	 *
	 * 以数组的形式返回结果集的一行
	 * @access public
	 * @return array 数组形式结果行
	 */
	public function row() {
		return $this->result->fetch_row();
	}

	/**
	 * 获取关联数组结果行
	 *
	 * 以数组和关联数组的形式返回结果集的一行
	 * @access public
	 * @return array 数组和关联数组形式结果行
	 */
	public function arr() {
		return $this->result->fetch_array();
	}

	/**
	 * 结果个数
	 *
	 * 返回结果集行数
	 * @access public
	 * @return int 结果个数
	 */
	public function num_rows() {
		return $this->result->num_rows;
	}

	/**
	 * 结果判空
	 *
	 * 判断结果集是否为空
	 * @access public
	 * @return bool 是否为空
	 */
	public function is_empty() {
		return ($this->result->num_rows == 0);
	}

	/**
	 * 一次性获取所有结果
	 *
	 * 返回每行由关联数组构成的整个结果集
	 * @access public
	 * @return array 所有结果
	 */
	public function all() {
		$res = array();
		while ($row = $this->result->fetch_assoc()) {
			$res[] = $row;
		}
		return $res;
	}

	public function allele() {
		$res = array();
		while ($r = $this->result->fetch_row()) {
			$res[] = $r[0];
		}
		return $res;
	}

	/**
	 * 获取单行若干结果元素 
	 * 
	 * 获取由可变string键值参数指定的当前结果行的元素值。
	 * 若无参数，则返回第一个元素；
	 * 若仅有一个参数，则返回此键值对应的元素值；
	 * 若有多个参数，则返回对应元素值组成的数组，
	 * 结果可由list()赋值
	 * @access public
	 * @return mixed 元素值
	 */
	public function ele() {
		$params = func_get_args();
		$parnum = count($params);
		$row = $this->result->fetch_array();
		if (is_null($row)) return null;
		if ($parnum == 0) {
			if (isset($row[1])) {
				return $row;
			} else {
				return $row[0];
			}
		} else if ($parnum == 1) {
			return $row[$params[0]];
		} else {
			$res = array();
			foreach ($params as $param) {
				$res[] = $row[$param];
			}
			return $res;
		}
	}

	public function insert($table, $p, $enableNULL=TRUE) {
		list($key, $value) = self::insertSQL($p, NULL, $enableNULL);
		try {
			return $this("INSERT INTO `$table` ($key) VALUES ($value)");	
		} catch (Exception $e) {
			var_dump($e);
			return FALSE;
		}
	}
	public function replace($table, $p, $enableNULL=TRUE) {
		list($key, $value) = self::insertSQL($p, NULL, $enableNULL);
		try {
			return $this("REPLACE INTO `$table` ($key) VALUES ($value)");	
		} catch (Exception $e) {
			return FALSE;
		}
	}

	public function update($table, $p, $where, $enableNULL=TRUE) {
		$sql = self::updateSQL($p, NULL, $enableNULL);
		return $this("UPDATE `$table` SET $sql WHERE $where");	
	}
	
	public function delete($table, $where) {
		return $this("DELETE FROM `$table` WHERE $where");
	}

	public function each($func) {
		$all = $this->all();
		foreach ($all as &$r) {
			$func($r);
		}
		return $all;
	}


	public static function available()
	{
		self::initialize();
		echo self::$host;
	}
}
?>
