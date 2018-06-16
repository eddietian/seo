<?php
/**
 * 搜索
 */
class SearchAction extends Basic{

	public function index() {
		header("Content-type: text/html; charset=gb2312");
		$params = $this->getParams();

		$data = array();

		$hostname = $_SERVER['SERVER_NAME'];


		$searchUrl = SearchService::$apiSearchUrl;
		$data['data'] =  HttpUtil::curlGet($searchUrl."&query=".$hostname);

		$a = $data['data'];

		$start = strpos($a, '<div id="bd_snap_ln"></div>');
		$data['data'] = substr($a, $start);


		$style = SearchService::regStyleContext($a);

		$list = SearchService::regKeywordsList($a);

		$headarray = SearchService::regHeadContext($a, $list[array_rand($list, 1)], $list[array_rand($list, 1)], $list[array_rand($list, 1)]);
		$head = $headarray['head'];
		$body = SearchService::regBodyContext($a, $headarray);

		$data['style'] = $style;
		$data['head'] = $head;
		$data['body'] = $body;
		$this->smarty->assign("data", $data);
		$this->smarty->display('search/index.html');
	}

}
