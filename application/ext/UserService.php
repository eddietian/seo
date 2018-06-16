<?php
class UserService{
	
	public static $apiUserInnerUrl = 'http://api.user.inner.sifuba.net/index.php';
	
	private static $apiUserBizid = 'userbiz10010001';
	private static $apiUserKey = '97a#d1Ma14%0dIxWd63*6a4()4704fb0c';
	private static $defaultHeadUrl = 'http://img.static.sifuba.net/res/images/9c/14/9c14f5a48af16f767dccea17a668a372.png';
	
	private static function httpPost($params){
		if(!isset($params['r'])){
			$params['r'] = 'dispatch/index';
		}
		if(!isset($params['bizid'])){
			$params['bizid'] = self::$apiUserBizid;
		}
		if(!isset($params['time'])){
			$params['rtime'] = time();
		}
		$params['sign'] = CommonUtil::calculateSign($params, self::$apiUserKey);
		
		$res = HttpUtil::curlPostJson(self::$apiUserInnerUrl, $params);

		if(empty($res) || !isset($res[0]['code']) || $res[0]['code'] != Status::SUCCESS){
			return false;
		}
		
		return $res[0]['data'];
	}
	
	public static function loginByPassword($username, $password){
		static $userList = array();
		$key = md5(serialize($username).serialize($password));
		
		if(isset($userList[$key])){
			return $userList[$key];
		}
		
		$params['cmd'] = '50101';
		$params['username'] = $username;
		$params['password'] = $password;
		
		$user = self::httpPost($params);
		$userList[$key] = empty($user) ? null : $user;
		return $userList[$key];
	}

	/**
	 * 批量拿到用户信息
	 * @author tianchao 20160921
	 * @params $userids = array(111,222,333,444)
	 * @return array
	 */
	public static function getUserInfoByUserIds($userids, $filter = array('password','isauto','phone')) {
		$params['cmd'] = '50113';

		$redisCache = new BizBasicCache();

		$result = array();

		if (empty($userids)) {
			return $result;
		}

		$searchids = array();//用来存储需要去查询的id

		$keycache = "hmcacheuserinfo";

		if ($redisCache->exists($keycache)) {
			$cachedata = $redisCache->hmget($keycache, $userids);
			foreach ($cachedata as $uid => $value) {
				if (!empty($value)) {
					$result[$uid] = json_decode($value,true);
				} else {
					$searchids[$uid] = $uid;
				}
			}
		} else {
			$searchids = $userids;
		}

		if (!empty($searchids)) {
			$params['userid'] = implode(",",$searchids);
			$searchdata = self::httpPost($params);
			if (empty($searchdata)) {
				$searchdata = array();
			}
			$redishmSetData = array();
			foreach ($searchdata as $key => $v) {
				if (empty($v['headurl'])) {
					$v['headurl'] = self::$defaultHeadUrl;
				}
				$v['nickname'] = $v['nickname'] ? $v['nickname']:$v['username'];//优先使用昵称
				$redishmSetData[$v['userid']] = json_encode($v);
				$result[$v['userid']] = $v;
			}
			$redisCache->hmset($keycache, $redishmSetData);
		}

		//过滤不需要的字段
		foreach ($result as &$v) {
			foreach ($filter as $vv) {
				unset($v[$vv]);
			}
		}

		return $result;
	}
	
}