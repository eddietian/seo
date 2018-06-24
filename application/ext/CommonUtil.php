<?php
class CommonUtil{
	
	const USER_COOKIE_KEY = 'x$Xnjsk9&ik3jx241632ox[klexgfmd';
	
	public static $domainList = array('sifuba.net','sifuba.com.cn');
	
	public static function encodePassword($password){
		$p = md5($password);
		return md5(substr($p, 16, 8).substr($p, 0, 8).substr($p, 24, 8).substr($p, 8, 8));
	}
	
	/**
	 * 将分转元
	 * @param int $money 单位分
	 * @return float
	 */
	public static function moneyToYuan($money){
		$money = intval($money);
		return $money / 100;
	}
	
	/**
	 * 计算token将手机、用户一起绑定，下一步操作在验证token的时候必须提供相关信息
	 * @param int $time
	 * @param string $phone
	 * @param int $userid
	 * @return string
	 */
	public static function smsToken($time, $phone, $userid = 0){
		$userid = intval($userid);
		return md5(AppConstant::MD5_USER_SMS_KEY.$time.$phone.$userid);
	}
	
	public static function standardUrl($url, $isHttps = false){
		$url = trim($url);
		return (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) ? $url : ($isHttps ? "https://{$url}" : "http://{$url}");
	}
	
	public static function setCookie($name, $value, $timeout = null){
		$expire = time() + intval($timeout);
		foreach(self::$domainList as $domain){
			setcookie($name, $value, $expire, '/', $domain);
		}
	}
	
	public static function clearCookie($name){
		$expire = strtotime('1970-01-01');
		foreach(self::$domainList as $domain){
			setcookie($name, '', $expire, '/', $domain);
		}
	}
	
	/**
	 * 设置用户web cookie
	 * @param array $user
	 */
	public static function setWebUserCookie($user){
		$time = time();
		$expire = $time + 365 * 86400;
		
		$u = DesUtil::encrypt(serialize($user));
		$sign = md5($time.$u.CommonUtil::USER_COOKIE_KEY);
		$data = array('logintime' => $time, 'sign' => $sign, 'u' => $u);
		
		foreach(self::$domainList as $domain){
			setcookie('userid', $user['userid'], $expire, '/', $domain);
			setcookie('username', $user['username'], $expire, '/', $domain);
			setcookie('nickname', $user['nickname'], $expire, '/', $domain);
			setcookie('cyauth', base64_encode(json_encode($data)), $expire, '/', $domain);
		}
	}
	
	public static function clearWebUserCookie(){
		$time = strtotime('1970-01-01');
		foreach(self::$domainList as $domain){
			setcookie('userid', '', $time, '/', $domain);
			setcookie('username', '', $time, '/', $domain);
			setcookie('nickname', '', $time, '/', $domain);
			setcookie('cyauth', '', $time, '/', $domain);
		}
	}
	
	/**
	 * 解析cookie并返回用户信息数组
	 * @return boolean
	 */
	public static function decodeWebUserCookie(){
		static $user = null;
		if($user !== null){
			return $user;
		}
		
		$cookie = $_COOKIE;
		if(!isset($cookie['cyauth']) || empty($cookie['cyauth'])){
			return false;
		}
		
		$data = @json_decode(base64_decode($cookie['cyauth']), true);
		
		if(!isset($data['logintime']) || !isset($data['sign']) || !isset($data['u'])){
			return false;
		}
		
		if($data['sign'] != md5($data['logintime'].$data['u'].CommonUtil::USER_COOKIE_KEY)){
			return false;
		}

		$user = @unserialize(DesUtil::decrypt($data['u']));
		return empty($user) ? false : $user;
	}

	public static function calculateSign($params, $key){
		if(!is_array($params) || empty($key)){
			return false;
		}
		if(isset($params['sign'])){
			unset($params['sign']);
		}

		ksort($params);//对参数字母排序
		reset($params);

		return md5(urldecode(http_build_query($params))."&{$key}");
	}

    /**
     * 得到用户IP
     *
     */
    public static function getUserIP()
    {
        $realIP = FALSE;
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $realIP = $_SERVER['HTTP_CLIENT_IP'];
        }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach($ips as $ip) {
                $ip = trim($ip);
                if(!self::isLAN($ip)) {//非局域网
                    $realIP = $ip;
                    break;
                }
            }
        }else {
            $realIP = $_SERVER['REMOTE_ADDR'];
        }
        return $realIP ? $realIP : 'unknow IP';
    }

        /**
         *@todo: 判断是否为post
         */
        public static  function is_post()
        {
            return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])=='POST';
        }

	//多维数组排序
	public static function sortByFeild($array,$keys,$type='asc'){
		if (count($array) <= 1) {
			return $array;
		}
		if(!isset($array) || !is_array($array) || empty($array)){
			return '';
		}
		if(!isset($keys) || trim($keys)==''){
			return '';
		}
		if(!isset($type) || $type=='' || !in_array(strtolower($type),array('asc','desc'))){
			return '';
		}
		$keysvalue=array();
		foreach($array as $key=>$val){
			$val[$keys] = str_replace('-','',$val[$keys]);
			$val[$keys] = str_replace(' ','',$val[$keys]);
			$val[$keys] = str_replace(':','',$val[$keys]);
			$keysvalue[] =$val[$keys];
		}
		asort($keysvalue); //key值排序
		reset($keysvalue); //指针重新指向数组第一个
		foreach($keysvalue as $key=>$vals) {
			$keysort[] = $key;
		}

		$keysvalue = array();
		$count=count($keysort);
		if(strtolower($type) != 'asc'){
			for($i=$count-1; $i>=0; $i--) {
				$keysvalue[] = $array[$keysort[$i]];
			}
		}else{
			for($i=0; $i<$count; $i++){
				$keysvalue[] = $array[$keysort[$i]];
			}
		}
		return $keysvalue;
	}


}