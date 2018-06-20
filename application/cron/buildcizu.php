<?php
set_time_limit(0);
/**
 */
date_default_timezone_set('PRC');
$dir = dirname(__FILE__);
//?��???�???件�??�?
define('APP_ROOT', realpath($dir . '/../../'));
require_once(APP_ROOT.'/../bfw/Main.php');
require_once(APP_ROOT."/application/config/config.inc.php");
Main::setConfig($appConfig);
Main::init();

header("Content-type: text/html; charset=gb2312");


$yesterday = date("Ymd", strtotime("-1 day"));
$sitemodel = new DBModel('default');

$s_title_sql = "select id,title from sitetitle where insertymd>={$yesterday} and state = 1";

$rs = $sitemodel->_db->query($s_title_sql)->fetchAll();

$logarray = array();
$titleids = array();
foreach ($rs as $id => $v) {
    $titleids[$id] = $id;
    $logarray[] = $v['title'];
}

//$u_score_sql = "update sitewords set score = score - 0.001 where score > 0";
//$sitemodel->_db->exec($u_score_sql);

foreach ($logarray as $_k => $a) {
    $a = trim($a);

    $a = safe($a);

    echo $a;
    if (empty($a)) {
        continue;
    }

    unset($logarray[$_k]);
    $arr = mbStrSplit($a);

    if (empty($arr)) {
        continue;
    }
    foreach ($arr as $arr_k => $arr_v) {
        $arr_v = trim($arr_v);
        if (strlen($arr_v) == 0) {
            unset($arr[$arr_k]);
        }
    }

    $wordlist = buildwordlist($arr);

    foreach ($wordlist as $word) {

        $word = trim($word);
        //?????��??�?�?
        if(preg_match("/^[a-zA-Z0-9]+$/",$word)){
            continue;
        }

        if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $word, $match)) {

        } else {
            continue;
        }


        if (mb_strlen($word) < 1) {
            continue;
        }

        if (mb_strlen($word) == 1) {
            if (json_encode($word) === 'null') {
                continue;
            }
        }


        //$rs = checkword($word, $sitemodel);

    }

    print_r($wordlist);

}

function buildwordlist($arr) {
    $wordlist = array();
    foreach ($arr as $key => $v) {
        $tmp = "";
        foreach ($arr as $kk => $vv) {
            $tmp.=$vv;
            $wordlist[] = $tmp;
        }
        unset($arr[$key]);
    }
    return $wordlist;
}

function mbStrSplit ($string, $len=1) {
    $start = 0;
    $string = trim($string);
    $strlen = mb_strlen($string);
    while ($strlen) {

        $b = mb_substr($string,$start,$len, "gb2312");
        //$b = mb_convert_encoding($b, "gb2312", "auto");
        $b = trim($b);

        if (json_encode($b) == 'null' || $b == "?") {
            //??�?�???�???
            if (preg_match("/[\x7f-\xff]/", $b)) {

            } else {
                $string= str_replace($b, "", $string);
                $strlen = mb_strlen($string);
                continue;
            }
        }

        $array[] = $b;
        $string = mb_substr($string, $len, $strlen, "gb2312");
        $strlen = mb_strlen($string);
    }

    return $array;
}

function checkword($words, $sitemodel) {
    $result  = array();
    try {
        $s_sql = "select * from sitewords where words=:words limit 1  ";
        $sitemodel->_db->prepare($s_sql);
        $sitemodel->_db->bindValue(':words', $words, PDO::PARAM_STR);
        if($sitemodel->_db->execute()){
            $result = $sitemodel->_db->fetch();
        }
    }catch (Exception $e){
    }


    return $result;
}

function safe($data){ //�??��?滤�?��??
    $data = addslashes($data);
    //??'_'�?滤�??
    $data = str_replace("_", "\_", $data);
    //??'%'�?滤�??
    $data = str_replace("%", "\%", $data);
    //??'*'�?滤�??
    $data = str_replace("*", "\*", $data);

    $data = str_replace("'", "", $data);
    $data = str_replace('"', "", $data);

    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);

    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('��', "", $data);
    $data = str_replace('(', "", $data);
    $data = str_replace(')', "", $data);

    return $data;
}




