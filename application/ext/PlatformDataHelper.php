<?php
/**
 * 生成游戏中心页面各种数据
 */
class PlatformDataHelper{
	
	public function __construct(){}


	/**
	 * 在游戏列表游戏所有字段中刷选页面所需字段并返回列表
	 * @param array $data
	 * @param array $option 配置项，包括：
	 * channelid	渠道id
	 */
	public static function selectAppColumn($data, $option = null){
		static $classList = null;
		$list = array();
		if(empty($data)){
			return $list;
		}
		
		if(empty($option)){
			$option = CommonParameter::getAllParams();
		}

		//单个游戏信息
		$flag = false;
		if(isset($data['appid'])){
			$flag = true;
			$data = array($data);
		}

		$appModel = new AppCache();

		//获取游戏分类
		if($classList == null){

			$classList = $appModel->getAllClass();
			$classList = ArrayUtil::arrayToMap($classList, 'classid');
		}

		//根据渠道，获取cps渠道游戏下载链接
		$channelid = isset($option['channelid']) ? floatval($option['channelid']) : 0;

		$acttype = 0;

		$qudaoAppCache = new QudaoAppCache();

		foreach($data as &$v){
			$t = array();
			$t['appid'] = (int)$v['appid'];
			$t['platform'] = (int)$v['platformid'];
			$t['apptype'] = (int)$v['apptype'];
			$t['appname'] = $v['appname'];
// 			$t['exchangeratio'] = empty($v['exchangeratio']) ? 1 : intval($v['exchangeratio']);
// 			$t['coinname'] = isset($v['coinname']) ? (string)$v['coinname'] : '';//游戏币名称，元宝、钻石等
			$t['classname'] = isset($classList[$v['classid']]) ? mb_substr($classList[$v['classid']]['classname'], 0, 2, 'utf-8') : '';//所属分类名称(如：角色扮演等)
			$t['packagename'] = $v['packagename'];//安装包包名

			$getChannelAppInfo = $qudaoAppCache->getQudaoAppInfoCache($channelid, $t['appid']);
			$t['packageaddress'] = $getChannelAppInfo['urlapk'];

			$t['packagebytes'] = intval($v['packagebytes']);//安装包字节数
			$t['filehash'] = empty($v['packagehash']) ? md5($v['appid']) : $v['packagehash'];//文件哈希值
			$t['versioncode'] = (int)$v['versioncode'];
			$t['versionname'] = empty($v['version']) ? '' : $v['version'];
			$t['oldversionname'] = isset($v['oldversionname']) ? $v['oldversionname'] : '';//旧版本号，游戏更新接口数据时有值，默认为空
			$t['tag'] = self::handleAppTag($v['tags']);//游戏标签
			$t['ratiotip'] = empty($v['exchangeratio']) ? null : self::handleAppLabel("充值1:{$v['exchangeratio']}".(empty($v['label']) ? '' : "|{$v['label']}"));
			$t['simpledesc'] = empty($v['simpledesc']) ? '' : $v['simpledesc'];
			$t['simplefuli'] = empty($v['simplefuli']) ? '' : $v['simplefuli'];
			$t['summary'] = (isset($v['summary']) ? str_replace("\n", '<br>', $v['summary']) : '');
			$t['introduce'] = (isset($v['introduce']) ? str_replace("\n", '<br>', $v['introduce']) : '');
			$t['introducefuli'] = (isset($v['introducefuli']) ? str_replace("\n", '<br>', $v['introducefuli']) : '');
			$t['download'] = empty($v['downloads']) ? 0 : (int)$v['downloads'];
			$t['appstoreaddress'] = $v['appstoreaddress'];
			$t['icon'] = $v['img85x85'];
			$t['updatetime'] = empty($v['updatetime']) ? 0 : strtotime($v['updatetime']);//距游戏将上线时间的小时数，游戏为新游时有值，默认为0
			$t['updatecontent'] = isset($v['updatecontent']) ? $v['updatecontent'] : '';//更新内容
			$t['acttype'] = strpos($t['packageaddress'], '.apk') !== false ? 0 : $acttype;//下载操作方式，默认0应用内下载，1浏览器打开包链接
			$t['serverlist'] = isset($v['serverlist']) ? $v['serverlist'] : null;
			$t['qq'] = isset($v['qq']) ? $v['qq'] : null;
			$t['qqtip'] = isset($v['qq']) ? $v['qq'] : null;
			$t['opentime'] = isset($v['opentime']) ? intval(strtotime($v['opentime'])) : 0;
			$t['imagelist'] = null;

			if ($option['screenshotonmobile'] && $v['screenshotonmobile']) {
				$t['imagelist'] = explode("|", rtrim($v['screenshotonmobile']));
			}

			array_push($list, $t);
		}
		unset($v);

		return $flag ? $list[0] : $list;
	}
	
	public static function handleAppTag($tags){
		if(empty($tags)){
			return null;
		}
		
		$json = @json_decode($tags, true);
		
		if(empty($json)){
			return null;
		}
		
		return $json;
	}
	
	public static function handleAppLabel($label){
		if(empty($label)){
			return null;
		}

		$list = explode("|", $label);
		
		$res = array();
		foreach($list as $v){
			if(!empty($v)){
				$res[] = $v;
			}
		}
		return $res;
	}
	
	public static function selectAppServerColumn($data){
		$list = array();
		if(empty($data)){
			return $list;
		}
		
		//单个
		$flag = false;
		if(isset($data['appid'])){
			$flag = true;
			$data = array($data);
		}
		
		foreach($data as &$v){
			$t = array();
			$t['servername'] = $v['serverid_our'].'服 '.$v['servername'];
			$t['servertimestring'] = empty($v['opentime']) ? '' : date("m-d H:i", strtotime($v['opentime']));
			array_push($list, $t);
		}
		unset($v);

		return $flag ? $list[0] : $list;
	}

	
	/**
	 * 资讯所需字段并返回列表
	 * @param array $data
	 */
	public static function selectCmsColumn($data){
		$list = array();
		if(empty($data)){
			return $list;
		}
	
		//单个
		$flag = false;
		if(isset($data['id'])){
			$flag = true;
			$data = array($data);
		}
	
		$time = time();
		foreach($data as &$v){
			$t = array();
			$t['id'] = intval($v['id']);
			$t['title'] = $v['title'];
			$t['intro'] = $v['intro'];
			$t['classid'] = (int)$v['classid'];
			$t['content'] = '';
			$t['author'] = (string)$v['author'];
			$t['time'] = empty($v['inserttime']) ? $time : strtotime($v['inserttime']);
			$t['isnew'] = (date('Ymd', strtotime($v['inserttime'])) == date('Ymd', time())) ? 1 : 0;
			array_push($list, $t);
		}
		unset($v);
	
		return $flag ? $list[0] : $list;
	}

	
	/**
	 * 只取出需要的字段
	 * @author tianchao 20150619
	 * @param $data array(0=>array("appid"=>111,"icon"=>"dfdfdfdfdf","othor"=>""));
	 * @param $need_field array("appid","icon")
	 */
	public static function selectColumn($data,$need_field = array()) {
		if (empty($need_field)) {
			return $data;
		}
		$result = array();
		foreach($data as $key=>$v) {
			$tmp = array();
			foreach($need_field as $needkey) {
				if(isset($v[$needkey])) {
					$tmp[$needkey] = $v[$needkey];
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}

}









