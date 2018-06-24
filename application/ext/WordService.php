<?php
class WordService{

	const cx_nr = "nr";//人名 [王大陆]
	const cx_n = "n";//名词 【男模】
	const cx_nz = "nz";//专词 [世界杯]

	const cx_d= "d";//副词 [已,不]
	const cx_vd = "vd";//副动词 [超越]

	const cx_v = "v";//动词[选中, 护肤, 防晒,就位]

	const cx_z = "z";//形容词[细长]
	const cx_a = "a";//形容词[丑]

	const cx_uj = 'uj';//助词[的]

	public static function buildcizu($wordlist) {
		$result = array();
		$wordlist1 = self::formatwordlist($wordlist, array("nr","uj","n"));
		$wordlist2 = self::formatwordlist($wordlist, array("nr", "v", "n"));
		$wordlist3 = self::formatwordlist($wordlist, array("n", "z"));
		$wordlist3 = self::formatwordlist($wordlist, array("n", "nz", "a"));

		$result = array_merge($wordlist1, $wordlist2, $wordlist3);

		return $result;
	}

	private static function formatwordlist($wordlist, $wordcx = array("nr","uj","n")) {
		$result = array();
		foreach ($wordlist as $v) {
			$result[$v['attr']][] = $v;
		}

		foreach ($result as &$v) {
			$v = CommonUtil::sortByFeild($v, "idf", "desc");
		}

		$result = self::buildlongWords($wordcx, $result);

		return $result;
	}

	//构建长尾词
	private static function buildlongWords($idxarr, $wordlist) {
		$result = array();
		foreach ($idxarr as $v) {
			if (!isset($wordlist[$v])) {
				return $result;
			}
		}

		$tmp = array();
		foreach ($idxarr as $key => $v) {
			foreach ($wordlist[$v] as $vv) {
				//评分小于2的名词拼接尾词
				if (($v == 'n' || $v == "nr") && $vv['idf'] < 2) {
					continue;
				}
				$tmp[$key][] = $vv['word'];
			}

		}

		if ($tmp) {
			$tmp = array_values($tmp);
			$result = self::getArrSet($tmp);
		}
		return $result;
	}

	//组盒
	public static function getArrSet($arrs, $_current_index= -1 )
	{
		static $_total_arr;
		static $_total_arr_index;
		static $_total_count;
		static $_temp_arr;

		if($_current_index<0)
		{
			$_total_arr=array();
			$_total_arr_index=0;
			$_temp_arr=array();
			$_total_count=count($arrs)-1;
			self::getArrSet($arrs,0);
		}
		else
		{

			foreach($arrs[$_current_index] as $v)
			{
				if($_current_index<$_total_count)
				{
					$_temp_arr[$_current_index]=$v;
					self::getArrSet($arrs,$_current_index+1);
				}
				else if($_current_index==$_total_count)
				{
					$_temp_arr[$_current_index]=$v;
					$_total_arr[$_total_arr_index]=$_temp_arr;
					$_total_arr_index++;
				}

			}
		}

		return $_total_arr;
	}


}