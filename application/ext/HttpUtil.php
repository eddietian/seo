<?php
/**
 * http访问处理
 */
class HttpUtil{
	
	const DEFAULT_TIMEOUT = 5;
	
	/**
	 * 使用curl进行GET请求
	 * 支持https
	 */
	public static function curlGet($url, $timeout = HttpUtil::DEFAULT_TIMEOUT, $header = array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($header)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		//是否https请求
		$https = substr($url, 0, 8) == "https://" ? true : false;
		if($https){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
		}
		
		$res = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($res !== false && $status >= 400){
			$res = false;
		}
		curl_close($ch);
		
		return $res;
	}
	
	/**
	 * 使用curl进行POST请求
	 * 支持https
	 */
	public static function curlPost($url, $data = array(), $timeout = HttpUtil::DEFAULT_TIMEOUT, $header = array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		if(!empty($header)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		//是否https请求
		$https = substr($url, 0, 8) == "https://" ? true : false;
		if($https){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
		}
		
		$res = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($res !== false && $status >= 400){
			$res = false;
		}
		curl_close($ch);
		
		return $res;
	}
	
	/**
	 * 使用curl进行GET请求，并且将json结果还原php类型
	 */
	public static function curlGetJson($url, $assoc = true, $timeout = HttpUtil::DEFAULT_TIMEOUT){
		$res = self::curlGet($url, $timeout);
		if($res !== false){
			$obj = json_decode($res, $assoc);
			return $obj;
		}else{
			return false;
		}
	}
	
	/**
	 * 使用curl进行POST请求，并且将json结果还原php类型
	 */
	public static function curlPostJson($url, $data = array(), $assoc = true, $timeout = HttpUtil::DEFAULT_TIMEOUT){
		$res = self::curlPost($url, $data, $timeout);
		if($res !== false){
			$obj = json_decode($res, $assoc);
			return $obj;
		}else{
			return false;
		}
	}
}

