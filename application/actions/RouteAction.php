<?php
/**
 * 路由分发
 * 直接访问html需要将文件放入/static/tpl/default
 */
class RouteAction extends Basic{

	//路由分发
	public function doIndex() {
		global $RouteConfig;
		$control = array();
		foreach ($RouteConfig as $_config) {
			$reg = self::_strquote($_config['reg']);
			if (preg_match($reg,$_SERVER['REQUEST_URI'],$result)) {
				$control['action'] = new $_config['action']();
				$control['method'] = $_config['method'];
				self::$params = $result;
				break;
			}
		}
		if (is_object($control['action'])) {
			$control['action']->$control['method']();
		} else {
			$this->defaultHtml();
		}
	}

	//转义
	private static function _strquote($reg){
		$reg = str_replace(".","\.",$reg);
		$reg = str_replace("/","\/",$reg);
		return "/^".$reg."/";
	}

	public function defaultHtml() {
		$url = $_SERVER['REQUEST_URI'];
		$url_arr = parse_url($url);
		$path = $url_arr['path'];

		try {
			$this->smarty->display("default{$path}");
		}catch (Exception $e){
			echo "文件未找到";
		}

	}
}
