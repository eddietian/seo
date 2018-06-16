<?php
class OperateMonitor{
	
	public static $cache = null;
	
	public function __construct($redis_choose = 'default'){
		parent::__construct($redis_choose);
	}
	
	/**
	 * @return RedisCache
	 */
	public static function getCache($redis_choose = 'default'){
		if(!self::$cache){
			self::$cache = new RedisCache($redis_choose);
		}
		return self::$cache;
	}
	
	/**
	 * 
	 * @param string $optKey 动作key
	 * @param int $limitTimes 上限
	 * @param int $time 时间段内 秒
	 * @return boolean
	 */
	public static function canExecute($key, $limitTimes, $time, $isBan = false, $banTime = 0){
		$cache = self::getCache();
		if(!$cache->exists($key)){
			$cache->set($key, 1, $time);
			return true;
		}
		$times = intval($cache->get($key));
		if($times >= $limitTimes){
			if($isBan){
				$cache->setTimeout($key, $banTime);
			}
			return false;
		}
		$cache->increment($key);
		return true;
	}
	
}