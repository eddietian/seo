<?php
/**
* @des : redis 缓存类
*/
class RedisCache extends RedisClient{
	
	protected $cache = null;

	public function __construct($redis_choose = 'default'){
		global $redisConfig;
		try{
			if($this->cache == null || empty($this->cache)){
				$config = $redisConfig[$redis_choose];
				parent::__construct($config);
				
				//如果主缓存有问题则连接slave
				if(empty($this->redis) && isset($redisConfig[$redis_choose.'_slave'])){
					$rc = new RedisClient($redisConfig[$redis_choose.'_slave']);
					if(!empty($rc->redis)){
						$this->redis = $rc->redis;
					}
				}
				
				$this->cache = $this->redis;
			}
		}catch(Exception $e){
			//记录报错日志
			$this->cache = null;
			
			//如果主缓存有问题则连接slave
			try {
				//如果主缓存有问题则连接slave
				if(empty($this->redis) && isset($redisConfig[$redis_choose.'_slave'])){
					$rc = new RedisClient($redisConfig[$redis_choose.'_slave']);
					if(!empty($rc->redis)){
						$this->redis = $rc->redis;
						$this->cache = $this->redis;
					}
				}
			}catch(Exception $e2){
	// 			throw $e2;
			}
		}
	}
}
