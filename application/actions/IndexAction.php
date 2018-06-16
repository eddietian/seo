<?php
/**
 *
 */
class IndexAction extends Basic{

	public function index() {
		//header("Content-type: text/html; charset=gb2312");
		$params = $this->getParams();

		$data = array();

		$hostname = $_SERVER['SERVER_NAME'];

		$sitecache = new SiteCache();
		$info = $sitecache->getSiteCache($hostname);

		$data['title'] = $info['title'];
		$data['keywords'] = $info['keywords'];
		$data['description'] = $info['description'];

		$host = $this->_getHost($info['hostname']);
		$contextinfo = $sitecache->getSiteContextCache($host);

		//为了保证有内容
		if (empty($contextinfo['context'])) {
			$contextinfo = $sitecache->getSiteContextCache(SiteModule::DEFAULT_SITE_HOST);
		}

		$data['context'] = $contextinfo['context'];


		$this->smarty->assign("data", $data);
		$this->smarty->display('index.html');
	}

	/**
	 * 拿到域名后缀
	 */
	public function _getHost($hostname) {
		$arr = explode(".", $hostname);
		$count = count($arr);

		$host = $arr[$count-2].".".$arr[$count-1];
		return $host;
	}

}
