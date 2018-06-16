<?php
class Validator{
	
	public static function checkUsername($username){
		$username = trim($username);
		return (empty($username) || mb_strlen($username, 'utf-8') < 6 || mb_strlen($username, 'utf-8') > 20) ? false : true;
	}
	
	public static function checkPassword($password){
		$password = trim($password);
		return (empty($password) || mb_strlen($password, 'utf-8') < 6 || mb_strlen($password, 'utf-8') > 20) ? false : true;
	}
	
	public static function checkPhone($phone){
// 		$regex = '/^1((3[0-9])|(4[57])|(5[012356789])|(8[02356789]))[0-9]{8}$/';
		$regex = '/^1((3[0-9])|(4[0-9])|(5[0-9])|(7[0-9])|(8[0-9])|(9[0-9]))[0-9]{8}$/';
		return preg_match($regex, $phone) == 1 ? true : false;
	}
	
	/**
	 * 
	 * @param int $money 单位分
	 */
	public static function checkMoney($money){
		$regex = '/^[1-9]\d*00$/';
		return preg_match($regex, $money) == 1 ? true : false;
	}
	
	/**
	 *
	 * @param int $coin 单位
	 */
	public static function checkCoin($coin){
		$regex = '/^[1-9]\d*$/';
		return preg_match($regex, $coin) == 1 ? true : false;
	}

	/**
	 * 验证图片url
	 * @param string $url
	 */
	public static function checkImgUrl($url) {

	}
	
}