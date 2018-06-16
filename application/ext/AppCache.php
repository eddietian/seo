<?php
//游戏应用
class AppCache extends BizBasicCache{

	private $prefix = 'appcache20180118_';

	//游戏操作module
	private static $_appModule = null;
	private static $siteModel = null;

	public function __construct($configName = 'default'){
		parent::__construct($configName);
	}

	private function _getAppModel(){
		if(self::$_appModule == null){
			self::$_appModule = new AppModule();
		}
		return self::$_appModule;
	}

	/**
	 * @return WebsiteModule
	 */
	public function getWebsiteModule(){
		if(self::$siteModel == null){
			self::$siteModel = new WebsiteModule();
		}
		return self::$siteModel;
	}

	public function getAppByIdsCache($appids) {
		$result = array();
		if (!is_array($appids) || empty($appids)) {
			return $result;
		}

		$cachekey = md5($this->prefix."AppByIds".implode("|", $appids));
		$rs = $this->get($cachekey);
		if (empty($rs)) {
			$result = $this->_getAppModel()->getAppListByIds($appids);
			if (!empty($result)) {
				$this->set($cachekey, $result);
			}
		} else {
			$result = json_decode($rs, true);
		}
		return $result;
	}

	//拿到指定渠道的游戏信息
	public function GameByIdsChannelid($gameids, $channelid = "", $screenshotonmobile = "") {
		if ($channelid == "") {
			$channelid = AppConstant::CHANNEL_WEBSITE;
		}

		$data = array();
		$appCache = new AppCache();
		$data = $appCache->getAppByIdsCache($gameids);

		$data = PlatformDataHelper::selectAppColumn($data, array('channelid' => $channelid, 'screenshotonmobile' => $screenshotonmobile));

		return $data;
	}

	/**
	 * 获取游戏所有分类
	 */
	public function getAllClass($condition = null){
		static $_RET = null;
		if($_RET !== null){
			return $_RET;
		}
		$key = md5("{$this->prefix}getAllClass".serialize($condition));
		//读缓存
		$data = $this->getBizCache($key);
		if($data !== false){
			return $data;
		}

		$model = new AppModule();
		$dataList = $model->getAllClass($condition);
		if(empty($dataList)){
			$dataList = array();
		}

		//写缓存
		$this->setBizCache($key, $dataList, 30 * 60);

		$_RET = $dataList;
		return $dataList;
	}

    /**
     * @param $appid
     * @return array|mixed|string
     * @desc:指定推广游戏的专用缓存，只能在单页面推广中使用
     */
    public function getPromotedGameByIdCache($appid,$channelid) {
        $result = array();
        if (empty($appid)) {
            return $result;
        }
        $cachekey = md5($this->prefix."getPromotedGameByIdCache".$appid.$channelid);
        $result = $this->getBizCache($cachekey);

        if (empty($result)) {
            $qudaoBindAppModule =new qudaoBindAppModule();
            $result = $qudaoBindAppModule->getAppListById($appid,$channelid);
            $this->setBizCache($cachekey, $result,30*60);
        }

        return $result;
    }

    /**
     * @param $uid
     * @param $appid
     * @return Ambigous|array|bool|mixed|null|string
     * @desc:礼包领取
     */
    public function getPromotedGameGiftDetailCache($uid,$appid){
        $result = array();
        if (empty($appid)) {
            return $result;
        }
        $cachekey = md5($this->prefix."getPromotedGameGiftDetailCache".$appid);
        $result = $this->getBizCache($cachekey);

        if (empty($result)) {
            $giftModule = new GiftModule();
            $result =  $giftModule->getGift($uid, $appid);
            $this->setBizCache($cachekey, $result,30*60);
        }

        return $result;
    }

    /**
     * @param $uid
     * @param $giftid
     * @return bool
     * @desc:进入查询礼包，直接从缓存查
     */
    public function queryGift($uid, $gameid){
        $giftid = intval($gameid);
        $key = md5("{$this->prefix}queryGift{$uid}|{$giftid}");
        //读缓存
        $data = $this->getBizCache($key);
        if(!empty($data)){
            return $data;
        }
        $giftModule = new GiftModule();
        $data =  $giftModule->getUserGiftCard($uid, $giftid);
        //写缓存
        $this->setBizCache($key, $data,60);
        return $data;
    }

    /**
     * @param $giftid
     * @return bool|null
     */
    public function getSingleGiftDetailCache($gameid,$platform){
        $key = md5("{$this->prefix}getSingleGiftDetailCache{$gameid}.{$platform}");
        //读缓存
        $data = $this->getBizCache($key);
        if(!empty($data)){
            return $data;
        }
        $giftModule = new GiftModule();
        //查询礼包
        $data =  $giftModule->getSingleGiftDetail(array('appid'=>$gameid,'platform'=>$platform));
        //写缓存
        $this->setBizCache($key, $data,60);
        return  $data;
    }



}




