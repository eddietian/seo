<?php
class CoterieCache extends BizBasicCache{

	private $prefix = 'cyouappcoterie20160921_';

	//游戏操作module
	private static $_CoterieModule = null;

	public function __construct($configName = 'default'){
		parent::__construct($configName);
	}

	/**
	 * 拿到一篇圈子详细信息
	 * @author tianchao 20160922
	 * @params $issimple 是否仅获取简要信息
	 */
	public function getCoterieInfoByIdCache($coterieid) {
		$result = array();
		if (empty($coterieid)) {
			return $result;
		}

		$cachekey = $this->_getKey("coterieid").$coterieid;
		$rs = $this->get($cachekey);
		if (empty($rs)) {
			$result = $this->_getCoterieModel()->getCoterieInfoById($coterieid);
			if (!empty($result)) {
				$this->set($cachekey, $result);
			}
		} else {
			$result = json_decode($rs,true);
		}
		return $result;
	}

	private function _getKey($type = 'id') {
		$key = "";
		switch ($type) {
			case 'coterieid'://拿到一篇圈子
				$key = $this->prefix."coterie_id_";
				break;
		}
		return $key;
	}

	private function _getCoterieModel(){
		if(self::$_CoterieModule == null){
			self::$_CoterieModule = new CoterieModule();
		}
		return self::$_CoterieModule;
	}



}




