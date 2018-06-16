<?php
class UserCache extends BizBasicCache{

	private $prefix = 'btgo20180201_';

	//游戏操作module
	private static $_UserChannelModule = null;

	public function __construct($configName = 'default'){
		parent::__construct($configName);
	}

	/**
	 * 拿到用户绑定的渠道
	 */
	public function getUserBindChannelCache($userid, $appid) {
		$userid = intval($userid);
		$appid = intval($appid);

		if (empty($userid) || empty($appid)) {
			return array();
		}

		$cachekey = $this->prefix."userbindchannel_".$userid.$appid;

		$result = array();
		$rs = $this->get($cachekey);
		if (empty($rs)) {
			$result = $this->_getUserChannelModule()->getUserBindChannelId($userid, $appid);
			if (!empty($result)) {
				$this->set($cachekey, $result);
			}
		} else {
			$result = json_decode($rs,true);
		}

		return $result;
	}

	private function _getUserChannelModule(){
		if(self::$_UserChannelModule == null){
			self::$_UserChannelModule = new UserChannelModule();
		}
		return self::$_UserChannelModule;
	}



}




