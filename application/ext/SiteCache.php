<?php
class SiteCache extends BizBasicCache{

	private $prefix = '20180616site_';

	//操作module
	private static $_SiteModule = null;

	public function __construct($configName = 'default'){
		parent::__construct($configName);
	}

	/**
	 * 拿到站点信息
	 */
	public function getSiteCache($hostname) {
		$hostname = trim($hostname);
		if (empty($hostname)) {
			$hostname = SiteModule::DEFAULT_SITE_HOSTNAME;
		}

		$cachekey = md5($this->prefix.$hostname);
		$rs = $this->get($cachekey);
		if (empty($rs)) {
			$result = self::_getSiteModel()->getSiteByHostName($hostname);
			if (!empty($result)) {
				$this->set($cachekey, $result);
			}
		} else {
			$result = json_decode($rs,true);
		}
		return $result;
	}

	public function getSiteContextCache($host) {
		$host = trim($host);
		if (empty($host)) {
			$host = SiteModule::DEFAULT_SITE_HOST;;
		}

		$cachekey = md5($this->prefix."sitecontext".$host);
		$rs = $this->get($cachekey);
		if (empty($rs)) {
			$result = self::_getSiteModel()->getSiteTplByhost($host);
			//随机拿取一个模板
			if ($result && count($result) > 1) {
				$idx = array_rand($result, 1);
				$result = $result[$idx];
			} else {
				$result = $result[0];
			}

			if (!empty($result)) {
				$this->set($cachekey, $result);
			}
		} else {
			$result = json_decode($rs,true);
		}
		return $result;
	}

	private static function _getSiteModel(){
		if(self::$_SiteModule == null){
			self::$_SiteModule = new SiteModule();
		}
		return self::$_SiteModule;
	}



}




