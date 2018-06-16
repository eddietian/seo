<?php
class CmsCache extends BizBasicCache{

	private $prefix = 'cmscache20180118_';

	//文章操作module
	private static $_cmsModule = null;

	public function __construct($configName = 'default'){
		parent::__construct($configName);
	}

	private function _getCmsModel(){
		if(self::$_cmsModule == null){
			self::$_cmsModule = new CmsModule();
		}
		return self::$_cmsModule;
	}

	public function getCmsClassAndIdCache($classid, $id) {
		$classid = intval($classid);
		$id = intval($id);
		$result = array();
		if (empty($classid) || empty($id)) {
			return $result;
		}

		$cachekey = $this->prefix."cmsclassandid_".$classid."_".$id;
		$rs = $this->get($cachekey);
		if (empty($rs)) {
			$result = $this->_getCmsModel()->getCms($classid, $id);
			if (!empty($result)) {
				$this->set($cachekey, $result);
			}
		} else {
			$result = json_decode($rs, true);
		}
		return $result;
	}

	public function getAppCmsListCache($appid, $classid = '', $isTop = false, $page = 0, $pagesize = 0, $fields = '*', $orderby = '') {
		$key = md5("{$this->prefix}getCmsListByClassid{$appid}|{$classid}|{$page}|{$pagesize}|{$fields}|{$orderby}");
		//读缓存
		$data = $this->getBizCache($key);
		if($data !== false){
			return $data;
		}

		$classid = intval($classid);

		$datetime = strftime('%Y-%m-%d %H:%M:%S', time());
		$condition = " appid={$appid} and isshow=1 and (showtime is null or showtime<'{$datetime}') and (showendtime is null or showendtime>'{$datetime}')";
		$condition .= empty($classid) ? '' : " and classid={$classid} ";
		$condition .= $isTop ? ' and istop=1 ' : ' and istop!=1 ';

		$model = new CmsModule();
		$dataList = $model->getCmsList($condition, $fields, 'inserttime desc', 0, $pagesize);

		if(empty($dataList)){
			$dataList = array();
		}

		//写缓存
		$this->setBizCache($key, $dataList);
		return $dataList;
	}



}




