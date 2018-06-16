<?php
/**
 * 业务缓存基础类，封装基本操作和多级缓存
 */
class BizBasicCache extends RedisCache{
	
	/**
	 * 是否启用缓存，判断优先级最高
	 * @var bool
	 */
	public $isCache = true;
	/**
	 * 是否启用本地缓存
	 * @var bool
	 */
	public $isLocalCache = false;
	
	/**
	 * @var RedisCache
	 */
	public static $localCache = null;

	/**
	 * 默认空值
	 */
	const REDIS_EMPTY = "is empty";

	public function __construct($configName = 'default'){
		parent::__construct($configName);
	}
	
	/**
	 * 获取业务缓存数据，false值不可缓存
	 * 默认开启本地缓存
	 * @param string $key
	 */
	public function getBizCache($key, $useLocal = true){
		if(!$this->isCache){
			return false;
		}
		
		$data = false;
		if($this->isLocalCache && $useLocal){
			$localCache = self::getLocalCacheInstance();
			$data = $localCache->get($key);
			if($data !== false){
				return json_decode($data, true);
			}
		}
		
		$data = $this->get($key);
		if($data === false){
			return false;
		}
		
		$data = json_decode($data, true);
		
		if($this->isLocalCache && $useLocal){
			$leftTime = $this->redis->ttl($key);
			if(intval($leftTime) > 0){
				$localCache = self::getLocalCacheInstance();
				//随机增加过期秒数，在分布式环境中防止同时失效造成流量拥挤
				$localCache->set($key, $data, $leftTime + rand(1, 4));
			}
		}
		
		return $data;
	}
	
	/**
	 * 设置缓存
	 * @param string $key
	 * @param mixed $data
	 * @param number $timeout
	 * @param bool $useLocal
	 */
	public function setBizCache($key, $value, $timeout = 300, $useLocal = true){
		if($this->isLocalCache && $useLocal){
			$localCache = self::getLocalCacheInstance();
			$localCache->set($key, $value, $timeout);
		}
		return $this->set($key, $value, $timeout);
	}

	/**
	 * 重写set，使用setnx替代原有set
	 * @author tianchao 20160921
	 * @param string $key
	 * @param array|string $value
	 * @param int $timeout
	 * @return bool
	 */
	public function set($key, $value, $timeout = 300) {
		if(!$this->isCache){
			return false;
		}

		////如果值为空,就放置字符串
		//$value = (empty($value) && !is_numeric($value)) ? self::REDIS_EMPTY:$value;

		return $this->setex($key, $value, $timeout);
	}

	public function hmset($key, $hashdata, $timeout = 600) {
		if(!$this->isCache){
			return false;
		}

		if($this->redis){
			$data = $this->redis->hmset( $key, $hashdata );
			if ( $this->redis->ttl( $key ) < 0 ) {
				$this->redis->expire( $key, $timeout );
			}
			return $data;
		}else{
			return false;
		}
	}

	public function hmget( $key, $hashkey ) {
		if(!$this->isCache){
			return false;
		}

		if($this->redis) {
			return $this->redis->hmget($key, $hashkey);
		}else{
			return false;
		}
	}

	public function hget($listname, $key) {

		if(!$this->isCache){
			return false;
		}

		if($this->redis){
			return $this->redis->hget($listname, $key);
		}
		return false;
	}

	public function hset($listname,$key,$data) {

		if(!$this->isCache){
			return false;
		}

		if($this->redis){
			return $this->redis->hset($listname,$key, $data);
		}
		return false;
	}

	public function ttl($key) {

		if(!$this->isCache){
			return false;
		}

		if($this->redis){
			return $this->redis->ttl($key);
		}
		return false;
	}

	/**
	 * @return RedisCache
	 */
	public static function getLocalCacheInstance(){
		if(self::$localCache === null){
			self::$localCache = new RedisCache('local');
		}
		
		return self::$localCache;
	}


}