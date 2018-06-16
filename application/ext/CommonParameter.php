<?php
class CommonParameter{
	
	/**
	 * 平台
	 */
	public static $platformDefined = array(
		1,//安卓
		2,//ios非越狱
		3,//ios越狱
	);
	
	public static $isUseRsa = true;
	
	const TYPE_NUM = 1;
	const TYPE_STR = 2;
	
	private static $isInit = false;
	private static $params = array();
	
	//是否复合请求
	private static $isComplex = false;
	//复合请求接口列表
	private static $complexInterfaceList = array();
	
	//定义http参数，自行添加
	private static $paramDefine = array(
		//参数名=>array(类型, 默认值)
		'cmd' => array(CommonParameter::TYPE_NUM, 0),//接口类型参数
		'platform' => array(CommonParameter::TYPE_NUM, 1),//平台类型，1：Android，2：ios
		'browser' => array(CommonParameter::TYPE_STR, ''),//浏览器类型
		'callback' => array(CommonParameter::TYPE_STR, ''),//前端回调函数名
		'sign' => array(CommonParameter::TYPE_STR, ''),//校验串
		'rtime' => array(CommonParameter::TYPE_NUM, 0),//request time
		
		'jsonparam' => array(CommonParameter::TYPE_STR, ''),//复合请求json参数：参数对象数组
		
		'channelid' => array(CommonParameter::TYPE_STR, ''),//
		'gameid' => array(CommonParameter::TYPE_NUM, ''),//
		'userid' => array(CommonParameter::TYPE_NUM, ''),//用户id
		'jsonlist' => array(CommonParameter::TYPE_STR, ''),
	);
	
	public static function getAllParams(){
		if(!self::$isInit){
			self::init();
		}
		
		return self::$params;
	}
	
	/**
	 * 设置或者更新
	 * @param string $name
	 * @param mixed $value
	 */
	public static function set($name, $value){
		if(!self::$isInit){
			self::init();
		}
		self::$params[$name] = $value;
		return true;
	}
	
	private static function init(){
		if(self::$isInit){
			return true;
		}
		self::$isInit = true;
		$p = array_merge($_GET, $_POST);
		foreach(self::$paramDefine as $k=>$v){
			if(isset($p[$k])){
				$pv = $p[$k];
				if($v[0] == CommonParameter::TYPE_NUM){
					self::$params[$k] = is_numeric($pv) ? (strpos($pv, '.') === false ? intval($pv) : floatval($pv)) : $v[1];
				}else{
					self::$params[$k] = trim($pv);
				}
			}else{
				self::$params[$k] = $v[1];
			}
		}
		
		
		//参数特殊处理
		//处理复合请求参数列表，格式：[{"i":1,"xxx":"xxx","ooo":"xxxx",...},{"i":2,"xxx2":"xxx2",...}]
		if(!empty(self::$params['jsonparam'])){
			$jsonParam = @json_decode(self::$params['jsonparam'], true);
			if(!empty($jsonParam) && isset($jsonParam[0]['cmd'])){
				//设置为复合请求
				self::$isComplex = true;
				
				foreach($jsonParam as $json){
					if(!isset($json['cmd'])){
						continue;
					}
					//存储接口列表
					array_push(self::$complexInterfaceList, $json['cmd']);
					unset($json['cmd']);
					foreach($json as $k=>$v){
						if(isset(self::$paramDefine[$k])){
							self::$params[$k] = (self::$paramDefine[$k][0] == CommonParameter::TYPE_NUM) ? (is_numeric($v) ? (strpos($v, '.') === false ? intval($v) : floatval($v)) : $v) : ($v);
						}else{
							self::$params[$k] = $v;
						}
					}
				}
			}
		}
		
		//appid=gameid
		if(empty(self::$params['gameid']) && !empty(self::$params['appid'])){
			self::$params['gameid'] = self::$params['appid'];
		}else{
			self::$params['appid'] = self::$params['gameid'];
		}
		
		if(self::$isUseRsa){
			$rsa = new Rsa();
			if(!empty(self::$params['password'])){
				self::$params['password'] = self::$params['platform'] == 1 ? $rsa->decrypt(self::$params['password']) : $rsa->iosdecrypt(self::$params['password']);
			}
			if(!empty(self::$params['newpassword'])){
				self::$params['newpassword'] = self::$params['platform'] == 1 ? $rsa->decrypt(self::$params['newpassword']) : $rsa->iosdecrypt(self::$params['newpassword']);
			}
		}
		return true;
	}
	
	/**
	 * 是否复合请求
	 * @return boolean
	 */
	public static function isComplex(){
		return self::$isComplex;
	}
	
	/**
	 * 获取复合请求接口列表
	 * @return array:
	 */
	public static function getInterfaceList(){
		return self::$complexInterfaceList;
	}
	
}