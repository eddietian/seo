<?php
class SearchService{
	
	public static $apiSearchUrl = 'http://cache.baiducontent.com/c?m=9f65cb4a8c8507ed4fece76310468a3f4a0fd6282bd7a744228b8448e435075c5323befb712d40598a96277000df1f17fdf14665377437b6eb99d515cabae23f2fff3035004dda&p=8e6ec54ad5c147e603f3f82d021488&newp=87759a46d4c50ef30be296315a4b8a231610db2151d6d11e6b82c825d7331b001c3bbfb423251406d0ce766600ac4f5cedfa3c72350923a3dda5c91d9fb4c57479c77b&user=baidu&fm=sc&qid=fc2c64ad00009266&p1=1';

	//&query=bokaidy.com.cn

	public static function regtxt($a, $s_str, $e_str) {
		$start = strpos($a, $s_str);
		$end = strpos($a, $e_str);

		$result = substr($a, $start, $end-$start+(strlen($e_str)));

		return $result;
	}

	public static function regStyleContext($a) {

		//preg_match_all('/<style>(.*?)</style>', $a, $arr);
		$s_str = '<base href';
		$e_str = '</style>';

		$start = strpos($a, $s_str);
		$end = strpos($a, $e_str);

		$result = substr($a, $start, $end-$start+(strlen($e_str)));

		return $result;
	}

	public static function regHeadContext($a, $title = "", $keyword = "", $description = "") {

		$result = array(
			"head" => "",
		);

		//拿到head
		$head = self::regtxt($a, '<head bdsfid', '</head>');

		$list = explode("\n", $head);

		if (count($list) == '1') {
			$result['head'] = $head;
		} else {
			foreach ($list as $key => $v) {
				if ($title && false !== strpos($v, '<title')) {
					$origintitle = strip_tags($v);
					$result['repace']['otitle'] = $origintitle;
					$result['repace']['title'] = $title;
					$list[$key] = '<title>'.$title.'</title>';
					continue;
				}

				if ($keyword && false !== strpos($v, '<meta name="keywords"')) {
					$list[$key] = '<meta name="keywords" content="'.$keyword.'">';
					continue;
				}

				if ($description && false !== strpos($v, '<meta name="description"')) {
					$list[$key] = '<meta name="description" content="'.$description.'">';
					continue;
				}
			}

			$head = join("\n", $list);
			$result['head'] = $head;
		}



		return $result;
	}

	public static function regBodyContext($a, $headarray = array()) {
		$s_str = '<body';

		$start = strpos($a, $s_str);
		$context = substr($a, $start);

		if (isset($headarray['repace'])) {
			//$context = str_replace($headarray['repace']['otitle'], $headarray['repace']['title'], $context);
		}
		return $context;
	}

	/**
	 * 拿到关键字列表
	 * @param $a
	 * @return array
	 */
	public static function regKeywordsList($a) {
		$s_str = '<p bdsfid=';

		$start = strpos($a, $s_str);
		$context = substr($a, $start, $s_str - 3);
		$context = strip_tags($context);
		if (empty($context)) {
			echo "error";
			exit;
		}

		$keylist = explode("\n", $context);
		foreach ($keylist as $key => $v) {
			$v = trim($v);
		}

		return array_filter($keylist);
	}

	public static function changeTitle($a) {

	}

}