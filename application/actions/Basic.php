<?php
/**
 * 复合请求分发
 *
 */
class Basic extends ActionBase{
	
	public static $params = null;
	public $smarty = null;

	public function __construct() {
		$this->smarty = AppHelper::GetSmarty();
	}

	public function getParams() {
		return self::$params;
	}
}