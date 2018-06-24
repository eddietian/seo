<?php
class WordService{

	const cx_nr = "nr";//���� [����½]
	const cx_n = "n";//���� ����ģ��
	const cx_nz = "nz";//ר�� [���籭]

	const cx_d= "d";//���� [��,��]
	const cx_vd = "vd";//������ [��Խ]

	const cx_v = "v";//����[ѡ��, ����, ��ɹ,��λ]

	const cx_z = "z";//���ݴ�[ϸ��]
	const cx_a = "a";//���ݴ�[��]

	const cx_uj = 'uj';//����[��]

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

	//������β��
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
				//����С��2������ƴ��β��
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

	//���
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