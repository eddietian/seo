<?php
/**
 * @name 基础模型 支持TP连贯操作 WHERE条件支持TP数组传参方式
 * @package BaseModel
 */
class BaseModel extends DBModel{
	//当前数据库类型
	protected $dbType='MYSQL';
	// 数据库名称,代替构造函数传参
	protected $dbName =   '';
	//表名
	protected $table='';	
	//真实表名
	protected $trueTableName='';	
	// 主键名称
    protected $pk='id';	
    
    // 最后插入ID
    protected $lastInsID  = null;
    
    // 返回或者影响记录数
    protected $numRows    = 0;
    
    // 最近错误信息
    protected $error            =   '';
	//默认分页大小
	public $pageSize = 20;
	
	// 查询表达式参数
	protected $options          =   array();
	// 链操作方法列表
	protected $methods          =   array('table','order','alias','having','group','lock','distinct','auto','filter','validate','result','bind','token');
	// 数据库表达式
	protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','like'=>'LIKE','notlike'=>'NOT LIKE','not like'=>'NOT LIKE','in'=>'IN','notin'=>'NOT IN','not in'=>'NOT IN','between'=>'BETWEEN','notbetween'=>'NOT BETWEEN','not between'=>'NOT BETWEEN');
	// 查询表达式
	protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';
	// 参数绑定
	protected $bind       = array();
	// 自动绑定参数
	protected $autoBind = false;
	//乐观锁表字段
	protected $optimLock        =   'lock_version';
	//开启乐观锁
	protected $beoplock = false;
	
	
	/**
	 * 构造函数
	 *
	 * @param string $db_choose
	 */
	public function __construct($dbName = null){
		if (!empty($dbName)){
			$this->dbName=$dbName;
		}
		parent::__construct($this->dbName);
	
		$this->table = $this->getTableName();
	}
	
	/**
	 * 利用__call方法实现一些特殊的Model方法
	 * @access public
	 * @param string $method 方法名称
	 * @param array $args 调用参数
	 * @return mixed
	 */
	public function __call($method,$args) {
		if(in_array(strtolower($method),$this->methods,true)) {
			// 连贯操作的实现
			$this->options[strtolower($method)] =   $args[0];
			return $this;
		}
	}
	
	public function beginTrans(){
		$this->_db->beginTrans();
	}
	
	public function commit(){
		$this->_db->commit();
		$this->_db->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
	}
	
	public function rollback(){
		$this->_db->rollback();
	}
	
	/**
	 * 指定查询条件 支持安全过滤
	 * @access public
	 * @param mixed $where 条件表达式
	 * @return Model
	 */
	public function where($where){
		if(is_object($where)){
			$where  =   get_object_vars($where);
		}
		if(is_string($where) && '' != $where){
			$map    =   array();
			$map['_string']   =   $where;
			$where  =   $map;
		}
		if(isset($this->options['where'])){
			$this->options['where'] =   array_merge($this->options['where'],$where);
		}else{
			$this->options['where'] =   $where;
		}
	
		return $this;
	}	
	
	/**
	 * 参数绑定
	 * @access public
	 * @param string $key  参数名
	 * @param mixed $value  绑定的变量及绑定参数
	 * @return Model
	 */
	public function bind($key,$value=false) {
		if(is_array($key)){
			$this->options['bind'] =    $key;
			if ($value===true){
				$this->autoBind=true;
			}
		}elseif (is_bool($key)&&$key===true){
			$this->autoBind=true;
		}else{
			$num =  func_num_args();
			if($num>2){
				$params =   func_get_args();
				array_shift($params);
				$this->options['bind'][$key] =  $params;
			}else{
				$this->options['bind'][$key] =  $value;
			}
		}
		return $this;
	}
	
	/**
	 * 乐观锁
	 * @param boolean $lock
	 */
	public function oplock($lock=true){
		if ($lock){
			$this->beoplock=true;
		}
		return $this;
	}
	
	/**
	 * 记录乐观锁(请设置表字段默认为0)
	 * @access protected
	 * @param array $data 数据对象
	 * @return array
	 */
	/* protected function recordLockVersion($data) {
		// 记录乐观锁
		if($this->optimLock && !isset($data[$this->optimLock]) ) {
			if(in_array($this->optimLock,$this->fields,true)) {
				$data[$this->optimLock]  =   0;
			}
		}
		return $data;
	} */
	
	/**
	 * 缓存乐观锁
	 * @access protected
	 * @param array $data 数据对象
	 * @return void
	 */
	protected function cacheLockVersion($data) {
		if($this->optimLock) {
			if(isset($data[$this->optimLock]) && isset($data[$this->pk])) {
				// 只有当存在乐观锁字段和主键有值的时候才记录乐观锁
				$_SESSION[$this->trueTableName.'_'.$data[$this->pk].'_lock_version']    =   $data[$this->optimLock];
			}
		}
	}
	
	/**
	 * 检查乐观锁
	 * @access protected
	 * @param inteter $id  当前主键
	 * @param array $data  当前数据
	 * @return mixed
	 */
	protected function checkLockVersion($id,&$data) {
		// 检查乐观锁
		$identify   = $this->trueTableName.'_'.$id.'_lock_version';
		if($this->optimLock && isset($_SESSION[$identify])) {
			//print_r($_SESSION);exit;
			$lock_version = $_SESSION[$identify];
			$this->options['where'][$this->optimLock]=$lock_version;
				
			// 更新乐观锁
			if( empty($data[$this->optimLock])||$data[$this->optimLock] != $lock_version+1) {
				$data[$this->optimLock]  =   $lock_version+1;
			}
			
			//$_SESSION[$identify]     =   $lock_version+1;

			return $identify;
			
			/* $vo   =  $this->field($this->optimLock)->find($id);
			$_SESSION[$identify]     =   $lock_version;
			$curr_version = $vo[$this->optimLock];
			if(isset($curr_version)) {
				if($curr_version>0 && $lock_version != $curr_version) {
					// 记录已经更新
					$this->error = '记录已经更新';
					return false;
				}else{
					// 更新乐观锁
					$save_version = $data[$this->optimLock];
					if($save_version != $lock_version+1) {
						$data[$this->optimLock]  =   $lock_version+1;
					}
					$_SESSION[$identify]     =   $lock_version+1;
				}
			} */
		}
		return false;
	}
	
	/**
	 * 查询SQL组装 join
	 * @access public
	 * @param mixed $join
	 * @return Model
	 */
	public function join($join) {
		if(is_array($join)) {
			$this->options['join']      =   $join;
		}elseif(!empty($join)) {
			$this->options['join'][]    =   $join;
		}
		return $this;
	}
	

	/**
	 * 指定查询字段 支持字段排除
	 * @access public
	 * @param mixed $field
	 * @param boolean $except 是否排除
	 * @return Model
	 */
	public function field($field){
		if(true === $field||empty($field)) {// 获取全部字段
			$field      =  '*';
		}
		$this->options['field']   =   $field;
		return $this;
	}
	
	/**
	 * 指定查询数量
	 * @access public
	 * @param mixed $offset 起始位置
	 * @param mixed $length 查询数量
	 * @return Model
	 */
	public function limit($offset,$length=null){
		$this->options['limit'] =   is_null($length)?$offset:$offset.','.$length;
		return $this;
	}
	
	/**
	 * 指定分页
	 * @access public
	 * @param mixed $page 页数
	 * @param mixed $listRows 每页数量
	 * @return Model
	 */
	public function page($page,$listRows=null){
		$this->options['page'] =   is_null($listRows)?$page:$page.','.$listRows;
		return $this;
	}
	
	/**
	 * 分析表达式
	 * @access protected
	 * @param array $options 表达式参数
	 * @return array
	 */
	protected function _parseOptions() {
		$options = $this->options;
		//if(is_array($options))
			//$options =  array_merge($this->options,$options);
		// 查询过后清空sql表达式组装 避免影响下次查询
		$this->options  =   array();
		if(empty($options['table'])){
			// 自动获取表名
			$options['table']   =   $this->getTableName();
		}
		if(empty($options['join'])){
			$options['join']   = 	'';
		}else{
			$options['join'] = $this->parseJoin(!empty($options['join'])?$options['join']:'');
		}
		if(empty($options['where'])){
			$options['where']   = 	'';
		}else{
			$options['where'] = $this->parseWhere(!empty($options['where'])?$options['where']:'');
		}
		
		if(empty($options['field'])){
			$options['field']   = 	'*';
		}
				
		if(empty($options['order'])){
			$options['order']   = 	false;
		}else{
			$options['order']  = $this->parseOrder(!empty($options['order'])?$options['order']:'');
		}
		
		if(empty($options['limit'])){
			$options['limit']   = 	false;
		}

		$options['bind']   = $this->parseBind(!empty($options['bind'])?$options['bind']:array());
		
		return $options;
	}
	
	/**
	 * 得到完整的数据表名
	 * @access public
	 * @return string
	 */
	public function getTableName() {
		if(empty($this->trueTableName)) {
			$name = str_replace('Module', '', get_class($this));
			if(version_compare(PHP_VERSION,'5.5.0')){
                $tableName=  preg_replace_callback("/^([A-Z])/", function($matches) { return strtolower($matches[0]);}, $name);
                $tableName=  preg_replace_callback("/([A-Z])/", function($matches) { return "_".strtolower($matches[0]);},  $tableName);
            }else{
                $tableName = preg_replace('/^([A-Z])/e', 'strtolower("\\1")', $name);
                $tableName = preg_replace('/([A-Z])/e', '"_".strtolower("\\1")', $tableName);
            }
			$this->trueTableName    =   $tableName;
		}
		return $this->trueTableName;
	}
	
	/**
	 * 修改数据
	 * 
	 * @param array|string $where
	 * @param array $data
	 * @return boolean
	 */
	public function save($data){
		if (isset($data[$this->pk])){
			$this->options['where'][$this->pk]=$data[$this->pk];
			unset($data[$this->pk]);
		}
		//检查添加乐观锁条件
		$lock_identify=null;
		if($this->beoplock&&isset($this->options['where'][$this->pk])){
			//添加乐观锁需是唯一记录
			$id     =   $this->options['where'][$this->pk];
			$lock_identify=$this->checkLockVersion($id,$data);
		}
		
		$set_data = $this->parseSet($data);
		$options = $this->_parseOptions();
		
		$where_str = !empty($options['where'])?' WHERE '.$options['where'].' ':' ';
		
		$sql   = 'UPDATE '
				.$this->parseTable($options['table'])
				.$set_data
				.$where_str;
		
		//var_dump($sql);
		//var_dump($options);exit;
		
		try {
			$this->_db->prepare ( $sql );
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			$result = $this->_db->execute();
			if ( false === $result) {
				$this->error='更新失败';
				return false;
			} else {
				$rowCount = $this->_db->rowCount();
				if (!empty($lock_identify)){
					if ($rowCount>0){
						// 更新乐观锁缓存
						$_SESSION[$lock_identify]=$data[$this->optimLock];
					}else{
						//影响行数为0则证明更新记录失败
						$this->error='数据已被别人更新,请重新操作';
						return false;
					}
				}
				return $rowCount;
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
	}

	
	/**
	 * 添加数据
	 *
	 * @param array $data
	 * @return boolean|int
	 */
	public function add($data,$bind=false){
		if ($bind===true){
			$this->autoBind=true;
		}elseif (is_array($bind)){
			$this->bind=$bind;
		}
		
		$values  =  $fields    = array();
		foreach ($data as $key=>$val){			
			if($this->autoBind===true){
				$name       =   md5($key);
				//$name       =   $key;
				$values[]   =   ':'.$name;
				$this->bindParam($name,$val);
			}else{
				$values[]   =  $this->parseValue($val);
			}
			$fields[]   =  $this->parseKey($key);
		}
		
		// 合并绑定参数
		$options = $this->_parseOptions();
		
		$sql   =  'INSERT INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
		//$sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
		//$sql   .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');		
		//echo $sql;exit;
		
		try {
			$this->_db->prepare ( $sql );
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			$result = $this->_db->execute();
			if ( false === $result) {
				return false;
			} else {
				return $this->_db->lastInsertId();
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
	}
	
	
	
	
	/**
	 * 删除数据
	 * 
	 * @param  array|string $where
	 * @return boolean
	 */
	public function delete($pk = ''){
		if (!empty($pk)){
			$this->options['where'][$this->pk]=$pk;
		}
		$options = $this->_parseOptions();
		$where_str = !empty($options['where'])?' WHERE '.$options['where'].' ':' ';
		
		$sql   = 'DELETE FROM '
				.$this->parseTable($options['table'])
				.$where_str;
		
		try {
			$this->_db->prepare ( $sql );
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			$result = $this->_db->execute();
			if ( false === $result) {
				return false;
			} else {
				return $this->_db->rowCount();
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
	}

	/**
	 * 获取多条记录
	 * 
	 * @param array  $condition
	 * @param string $field
	 * @param string $orderby
	 * @param int    $limit
	 * @param string $where
	 * @return array
	 */
	public function select($table='', $where='',$field = '', $order = '', $limit = '', $where_type = false, $fetch_style = PDO::FETCH_ASSOC){
		if (!empty($table)){
			$this->trueTableName = $table;
		}
		if (!empty($where)){
			$this->options['where']=$where;
		}
		if (!empty($field)){
			$this->options['field']=$field;
		}
		if (!empty($order)){
			$this->options['order']=$order;
		}
		if (!empty($limit)){
			$this->options['limit']=$limit;
		}
		$options = $this->_parseOptions();
		
		$sql = $this->parseSql($this->selectSql,$options);
				
		try {
			$this->_db->prepare ( $sql );
			
			$this->bindPdoParam($options['bind']);
			
			if ($this->_db->execute ()) {
				
				return $this->_db->fetchAll ( PDO::FETCH_ASSOC );
			}
			return array();
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}		
	}
	
	/**
	 * 获取单条记录
	 *
	 * @param string $field
	 * @return array
	 */
	public function find($pk=''){
		if (!empty($pk)){
			$this->options['where'][$this->pk]=$pk;
		}
		if ($this->beoplock){
			if (!empty($this->options['field'])&&$this->options['field']!='*'){
				//自动添加查询乐观锁字段
				if(strrpos($this->options['field'], $this->optimLock)===false){
					$this->options['field'].=','.$this->optimLock;
				}
			}
		}
		$this->limit(1);// 总是查找一条记录
		$options = $this->_parseOptions();
		
		$sql = $this->parseSql($this->selectSql,$options);		
		//var_dump($sql);
		
		try {
			$this->_db->prepare ( $sql );
			
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			if ($this->_db->execute ()) {
				$records = $this->_db->fetch ( PDO::FETCH_ASSOC );
				if ($records && is_array($records)) {
					// 缓存乐观锁
					if ($this->beoplock){
						$this->cacheLockVersion($records);
					}
					return $records;
				}
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
		
		return array();
	}
	
	/**
	 * SQL查询 (返回查询结果)
	 * @param string $sql
	 * @return multitype:
	 */
	public function query($sql,$bind=array()){
		if (!empty($bind)){
			$this->bind=$bind;
		}
		$options = $this->_parseOptions();
		
		try {
			$this->_db->prepare ( $sql );
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			$result = $this->_db->execute();
			if ( false === $result) {
				return false;
			}else{
				return $this->_db->fetchAll($fetch_style = PDO::FETCH_ASSOC);
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
		
	}
	
	/**
	 * 执行SQL语句 (INSERT UPDATE 等只需执行的操作)
	 * @access public
	 * @param string $sql  SQL指令
	 * @param mixed $parse  是否需要解析SQL
	 * @return false | integer
	 */
	public function execute($sql,$bind=array()) {			
		if (!empty($bind)){
			$this->bind=$bind;
		}
		$options = $this->_parseOptions();
		
		try {
			$this->_db->prepare ( $sql );
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			$result = $this->_db->execute();
			if ( false === $result) {
				return false;
			} else {
				return $this->_db->rowCount();
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
		
	}

	public function prepare($sql){
		$this->_db->prepare($sql);
	}
	

	/**
	 * 参数绑定
	 * @access protected
	 * @return void
	 */
	protected function bindPdoParam($bind){
		// 参数绑定
		if (!empty($bind)){
			foreach($bind as $key=>$val){
				$this->bindValue($key, $val);
				//$this->_db->bindParam($key, $val);
			}
		}
	}
	
	/**
	 * 绑定SQL参数
	 * @param string $name
	 * @param string|int $value
	 * @param string $bind_type
	 */
	public function bindValue($name,$value,$bind_type=''){
		if (empty($bind_type)){
			$bind_type = PDO::PARAM_STR;
			if (is_int($value)){
				$bind_type = PDO::PARAM_INT;
			}elseif (is_bool($value)){
				$bind_type=PDO::PARAM_BOOL;
			}elseif (is_null($value)){
				$bind_type=PDO::PARAM_NULL;
			}
		}
		
		$this->_db->bindValue($name, $value,$bind_type);
	}
	

	/**
	 * 参数绑定
	 * @access protected
	 * @param string $name 绑定参数名
	 * @param mixed $value 绑定值
	 * @return void
	 */
	protected function bindParam($name,$value){
		$this->bind[':'.$name]  =   $value;
	}
	
	/**
	 * 返回最后执行的sql语句
	 * @access public
	 * @return string
	 */
	public function getLastSql() {
		return $this->_db->getLastSql();
	}
	
	/**
	 * 获取一条记录的某个字段值
	 * @param string $field
	 */
	public function getField($field){
		$info = $this->field($field)->find();
		if (!empty($info)){
			return $info[$field];
		}
		return false;
	}
	
	/**
	 * 设置记录的某个字段值
	 * 支持使用数据库字段和方法
	 * @access public
	 * @param string|array $field  字段名
	 * @param string $value  字段值
	 * @return boolean
	 */
	public function setField($field,$value='') {
		if(is_array($field)) {
			$data           =   $field;
		}else{
			$data[$field]   =   $value;
		}
		return $this->save($data);
	}
	
	/**
	 * 统计数量
	 * 
	 */
	public function count($distinct='*'){
		if (empty($distinct)){
			$distinct='*';
		}
				
		$options = $this->_parseOptions();
		if (!empty($options['where'])){
			$options['where'] =' WHERE '.$options['where'];
		}
		
		$sql = 'SELECT count('.$distinct.') FROM '.
			$this->parseTable($options['table']).
			' '.$options['join'].
			' '.$options['where'];
		
		$options = $this->_parseOptions();
		
		try {
			$stmt = $this->_db->prepare ( $sql );
			// 参数绑定
			$this->bindPdoParam($options['bind']);
			
			$result = $this->_db->execute();
			if ( false === $result) {
				return 0;
			}else{
				$count =  (int)$this->_db->fetchColumn();
				//关闭游标，以便下次执行
				$stmt->closeCursor();
				return $count;
			}
		}catch (Exception $e){
			$this->error='异常错误：'.$e->getMessage();
			return false;
		}
	}
	
	/**
	 * 参数绑定分析
	 * @access protected
	 * @param array $bind
	 * @return array
	 */
	protected function parseBind($bind){
		if (!empty($this->bind)&&!empty($bind)){
			$bind           =   array_merge($this->bind,$bind);
		}elseif (!empty($this->bind)){
			$bind = $this->bind;
		}
		$this->bind     =   array();
		return $bind;
	}

	
	/**
	 * limit分析
	 * @access protected
	 * @param mixed $lmit
	 * @return string
	 */
	protected function parseLimit($limit) {
		return !empty($limit)?   ' LIMIT '.$limit.' ':'';
	}
	
	/**
	 * join分析
	 * @access protected
	 * @param mixed $join
	 * @return string
	 */
	protected function parseJoin($join) {
		$joinStr = '';
		if(!empty($join)) {
			if(is_array($join)) {
				foreach ($join as $key=>$_join){
					if(false !== stripos($_join,'JOIN'))
						$joinStr .= ' '.$_join;
					else
						$joinStr .= ' LEFT JOIN ' .$_join;
				}
			}else{
				$joinStr .= ' LEFT JOIN ' .$join;
			}
		}
		//将__TABLE_NAME__这样的字符串替换成正规的表名,并且带上前缀和后缀
		//$joinStr = preg_replace("/__([A-Z_-]+)__/esU",C("DB_PREFIX").".strtolower('$1')",$joinStr);
		return $joinStr;
	}
	
	/**
	 * where分析
	 * @access protected
	 * @param mixed $where
	 * @return string
	 */
	protected function parseWhere($where) {		
		$whereStr = '';
		if(is_string($where)) {
			// 直接使用字符串条件
			$whereStr = $where;
		}else{ // 使用数组表达式
			$operate  = isset($where['_logic'])?strtoupper($where['_logic']):'';
			if(in_array($operate,array('AND','OR','XOR'))){
				// 定义逻辑运算规则 例如 OR XOR AND NOT
				$operate    =   ' '.$operate.' ';
				unset($where['_logic']);
			}else{
				// 默认进行 AND 运算
				$operate    =   ' AND ';
			}
			foreach ($where as $key=>$val){
				$whereStr .= '( ';
				if(is_numeric($key)){
					$key  = '_complex';
				}
				if(0===strpos($key,'_')) {
					// 解析特殊条件表达式
					$whereStr   .= $this->parseThinkWhere($key,$val);
				}else{
					// 查询字段的安全过滤
					if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
						//E(L('_EXPRESS_ERROR_').':'.$key);
						exit('异常错误:'.$key);
					}
					// 多条件支持
					$multi  = is_array($val) &&  isset($val['_multi']);
					$key    = trim($key);
					if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
						$array =  explode('|',$key);
						$str   =  array();
						foreach ($array as $m=>$k){
							$v =  $multi?$val[$m]:$val;
							$str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
						}
						$whereStr .= implode(' OR ',$str);
					}elseif(strpos($key,'&')){
						$array =  explode('&',$key);
						$str   =  array();
						foreach ($array as $m=>$k){
							$v =  $multi?$val[$m]:$val;
							$str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
						}
						$whereStr .= implode(' AND ',$str);
					}else{
						$whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
					}
				}
				$whereStr .= ' )'.$operate;
			}
			$whereStr = substr($whereStr,0,-strlen($operate));
		}
		return empty($whereStr)?'':$whereStr;
	}
	
	// where子单元分析
	protected function parseWhereItem($key,$val) {
		$whereStr = '';
		if(is_array($val)) {
			if(is_string($val[0])) {
				$exp	=	strtolower($val[0]);
				if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // 比较运算
					$whereStr .= $key.' '.$this->comparison[$exp].' '.$this->parseValue($val[1]);
				}elseif(preg_match('/^(NOTLIKE|NOT LIKE|LIKE)$/i',$val[0])){// 模糊查找
					if(is_array($val[1])) {
						$likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
						if(in_array($likeLogic,array('AND','OR','XOR'))){
							$likeStr    =   $this->comparison[$exp];
							$like       =   array();
							foreach ($val[1] as $item){
								$like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
							}
							$whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';
						}
					}else{
						$whereStr .= $key.' '.$this->comparison[$exp].' '.$this->parseValue($val[1]);
					}
				}elseif('exp'==strtolower($val[0])){ // 使用表达式
					$whereStr .= ' ('.$key.' '.$val[1].') ';
				}elseif(preg_match('/^(NOTIN|NOT IN|IN)$/i',$val[0])){ // IN 运算
					if(isset($val[2]) && 'exp'==$val[2]) {
						$whereStr .= $key.' '.$this->comparison[$exp].' '.$val[1];
					}else{
						if(is_string($val[1])) {
							$val[1] =  explode(',',$val[1]);
						}
						$zone      =   implode(',',$this->parseValue($val[1]));
						$whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
					}
				}elseif(preg_match('/^(NOTBETWEEN|NOT BETWEEN|BETWEEN)$/i',$val[0])){ // BETWEEN运算
					$data = is_string($val[1])? explode(',',$val[1]):$val[1];
					$whereStr .=  ' ('.$key.' '.$this->comparison[$exp].' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
				}else{
					//E(L('_EXPRESS_ERROR_').':'.$val[0]);
					exit('异常错误:'.$val[0]);
				}
			}else {
				$count = count($val);
				$rule  = isset($val[$count-1]) ? (is_array($val[$count-1]) ? strtoupper($val[$count-1][0]) : strtoupper($val[$count-1]) ) : '' ;
				if(in_array($rule,array('AND','OR','XOR'))) {
					$count  = $count -1;
				}else{
					$rule   = 'AND';
				}
				for($i=0;$i<$count;$i++) {
					$data = is_array($val[$i])?$val[$i][1]:$val[$i];
					if('exp'==strtolower($val[$i][0])) {
						$whereStr .= '('.$key.' '.$data.') '.$rule.' ';
					}else{
						$whereStr .= '('.$this->parseWhereItem($key,$val[$i]).') '.$rule.' ';
					}
				}
				$whereStr = substr($whereStr,0,-4);
			}
		}else {
			//对字符串类型字段采用模糊匹配
			//if(C('DB_LIKE_FIELDS') && preg_match('/('.C('DB_LIKE_FIELDS').')/i',$key)) {
				//$val  =  '%'.$val.'%';
				//$whereStr .= $key.' LIKE '.$this->parseValue($val);
			//}else {
				//$whereStr .= $key.' = '.$this->parseValue($val);
			//}
			
			if($this->autoBind===true){
				$name   =   md5($key);
				//$name   =   $key;
				$whereStr .= $key.'=:'.$name;
				$this->bindParam($name,$val);
			}else{
				$whereStr .= $key.' = '.$this->parseValue($val);
			}
		}
		return $whereStr;
	}
	
	/**
	 * 特殊条件分析
	 * @access protected
	 * @param string $key
	 * @param mixed $val
	 * @return string
	 */
	protected function parseThinkWhere($key,$val) {
		$whereStr   = '';
		switch($key) {
			case '_string':
				// 字符串模式查询条件
				$whereStr = $val;
				break;
			case '_complex':
				// 复合查询条件
				$whereStr   =   is_string($val)? $val : substr($this->parseWhere($val),6);
				break;
			case '_query':
				// 字符串模式查询条件
				parse_str($val,$where);
				if(isset($where['_logic'])) {
					$op   =  ' '.strtoupper($where['_logic']).' ';
					unset($where['_logic']);
				}else{
					$op   =  ' AND ';
				}
				$array   =  array();
				foreach ($where as $field=>$data)
					$array[] = $this->parseKey($field).' = '.$this->parseValue($data);
				$whereStr   = implode($op,$array);
				break;
		}
		return $whereStr;
	}
	
	/**
	 * order分析
	 * @access protected
	 * @param mixed $order
	 * @return string
	 */
	protected function parseOrder($order) {
		if(is_array($order)) {
			$array   =  array();
			foreach ($order as $key=>$val){
				if(is_numeric($key)) {
					$array[] =  $this->parseKey($val);
				}else{
					$array[] =  $this->parseKey($key).' '.$val;
				}
			}
			$order   =  implode(',',$array);
		}
		return !empty($order)?  $order:'';
	}
	
	protected function parseValue($value) {
		if(is_string($value)) {
			$value =  strpos($value,':') === 0 ? $this->escapeString($value) : '\''.$this->escapeString($value).'\'';
		}elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
			$value =  $this->escapeString($value[1]);
		}elseif(is_array($value)) {
			$value =  array_map(array($this, 'parseValue'),$value);
		}elseif(is_bool($value)){
			$value =  $value ? '1' : '0';
		}elseif(is_null($value)){
			$value =  'null';
		}
		return $value;
	}
	
	/**
	 * table分析
	 * @access protected
	 * @param mixed $table
	 * @return string
	 */
	protected function parseTable($tables) {
		if(is_array($tables)) {// 支持别名定义
			$array   =  array();
			foreach ($tables as $table=>$alias){
				if(!is_numeric($table))
					$array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
				else
					$array[] =  $this->parseKey($table);
			}
			$tables  =  $array;
		}elseif(is_string($tables)){
			$tables  =  explode(',',$tables);
			array_walk($tables, array(&$this, 'parseKey'));
		}
		return implode(',',$tables);
	}
	
	/**
	 * distinct分析
	 * @access protected
	 * @param mixed $distinct
	 * @return string
	 */
	protected function parseDistinct($distinct) {
		return !empty($distinct)?   ' DISTINCT ' :'';
	}

	
	/**
	 * 替换SQL语句中表达式
	 * @access public
	 * @param array $options 表达式
	 * @return string
	 */
	protected function parseSql($sql,$options=array()){		
		$sql   = str_replace(
				array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
				array(
						$this->parseTable($options['table']),
						isset($options['distinct'])?' '.$options['distinct'].' ':' ',
						!empty($options['field'])?' '.$options['field'].' ':'*',
						!empty($options['join'])?' '.$options['join'].' ':' ',
						!empty($options['where'])?' WHERE '.$options['where'].' ':' ',
						!empty($options['group'])?' GROUP BY '.$options['group'].' ':' ',
						!empty($options['having'])?' HAVING '.$options['having'].' ':' ',
						!empty($options['order'])?' ORDER BY '.$options['order'].' ':' ',
						!empty($options['limit'])?' LIMIT '.$options['limit'].' ':'',
						!empty($options['union'])?' '.$options['union'].' ':'',
						!empty($options['comment'])?' /* '.$options['comment'].' */':' '
				),$sql);
		return $sql;
	}
		
	/**
	 * SQL指令安全过滤
	 * @access public
	 * @param string $str  SQL指令
	 * @return string
	 */
	public function escapeString($str) {
		switch($this->dbType) {
			case 'MSSQL':
			case 'SQLSRV':
			case 'MYSQL':
				return addslashes($str);
			case 'PGSQL':
			case 'IBASE':
			case 'SQLITE':
			case 'ORACLE':
			case 'OCI':
				return str_ireplace("'", "''", $str);
		}
	}
	
	/**
	 * 字段名分析
	 * @access protected
	 * @param string $key
	 * @return string
	 */
	protected function parseKey(&$key) {
		if($this->dbType=='MYSQL'){
			$key   =  trim($key);
			if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
				$key = '`'.$key.'`';
			}
			return $key;
		}else{
			return $key;
		}	
	}
	


	/**
	 * set分析
	 * @access protected
	 * @param array $data
	 * @return string
	 */
	protected function parseSet($data) {
		foreach ($data as $key=>$val){
			if(is_array($val) && 'exp' == $val[0]){
				$set[]  =   $this->parseKey($key).'='.$val[1];
			}elseif(is_scalar($val) || is_null($val)) { // 过滤非标量数据
				if($this->autoBind===true){
					$name   =   md5($key);
					//$name   =   $key;
					$set[]  =   $this->parseKey($key).'=:'.$name;
					$this->bindParam($name,$val);
				}else{
					$set[]  =   $this->parseKey($key).'='.$this->parseValue($val);
				}
			}
		}
		return ' SET '.implode(',',$set);
	}
	

	/**
	 * 数据类型检测
	 * @access protected
	 * @param mixed $data 数据
	 * @param string $key 字段名
	 * @return void
	 */
	protected function _parseType(&$data,$key) {
		if(empty($this->options['bind'][':'.$key]) && isset($this->fields['_type'][$key])){
			$fieldType = strtolower($this->fields['_type'][$key]);
			if(false !== strpos($fieldType,'enum')){
				// 支持ENUM类型优先检测
			}elseif(false === strpos($fieldType,'bigint') && false !== strpos($fieldType,'int')) {
				$data[$key]   =  intval($data[$key]);
			}elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
				$data[$key]   =  floatval($data[$key]);
			}elseif(false !== strpos($fieldType,'bool')){
				$data[$key]   =  (bool)$data[$key];
			}
		}
	}
	

	/**
	 * 获取错误信息
	 * @return string
	 */
	public function getError(){
		return $this->error;
	}

}