<?php
set_time_limit(0);
/**
 */
date_default_timezone_set('PRC');
$dir = dirname(__FILE__);
//?¨å???ç½???ä»¶ç??å½?
define('APP_ROOT', realpath($dir . '/../../'));
require_once(APP_ROOT.'/../bfw/Main.php');
require_once(APP_ROOT."/application/config/config.inc.php");
Main::setConfig($appConfig);
Main::init();

header("Content-type: text/html; charset=gb2312");

$sitemodel = new DBModel('default');

$file = __DIR__."/ok.txt";

$logarray = array();
$fp = fopen($file, 'r'); //ÎÄ¼þ
while (!feof($fp)) {
    //for($j=1;$j<=1000;$j++) {     //¶ÁÈ¡ÏÂÃæµÄ1000ÐÐ²¢´æ´¢µ½Êý×éÖÐ
    $logarray[] = stream_get_line($fp, 65535, "\n");
    // break;
    // }
}

//$u_score_sql = "update sitewords set score = score - 0.001 where score > 0";
//$sitemodel->_db->exec($u_score_sql);

foreach ($logarray as $_k => $a) {
    $a = trim($a);
    $a = safe($a);
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
        //?????°å??å­?æ¯?
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

        $rs = checkword($word, $sitemodel);
        if (empty($rs)) {
            try {
                $i_sql = "insert into sitewords(words, score) values ('".$word."', 0)";
                $sitemodel->_db->exec($i_sql);
            }catch (Exception $e){

            };

        } else {

            $gbklen = mb_strlen($word, "gbk");
            $len = mb_strlen($word);
            $len = $gbklen<$len ? $gbklen:$len;

            //ä¸?ä¸?å­?å°±ä?è®¡å??äº?
            if ($len <= 1) {
                continue;
            } else {

                if ($word != $rs['words']) {
                    continue;
                }

                $score = 0.001;

                if ($len >3) {
                    $score = bcadd(0.001, $len/1000);
                }
                if ($rs['score'] < 10) {
                    $u_score_sql = "update sitewords set score = score + {$score} where id={$rs['id']}";
                    $sitemodel->_db->exec($u_score_sql);
                }
            }
        }
    }

}


function buildwordlist($arr) {
    $wordlist = array();
    foreach ($arr as $key => $v) {
        if (json_encode($v) == 'null') {
            unset($arr[$key]);
        }
        $str = join("", $arr);
        for ($i = 1; $i<=count($arr); $i++) {
            $wordlist[] = mb_substr($str, 0, $i);
        }
        unset($arr[$key]);
    }
    return $wordlist;
}

function mbStrSplit ($string, $len=1) {
    $start = 0;
    $strlen = mb_strlen($string);
    while ($strlen) {
        $b = mb_substr($string,$start,$len, "gb2312");
        $b = trim($b);


        if (json_encode($b) == 'null' || $b == "?") {
            //??æ¼?ç½???ä¸???
            if (preg_match("/[\x7f-\xff]/", $b)) {

            } else {
                $string = str_replace($b, "", $string);
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

function safe($data){ //å®??¨è?æ»¤å?½æ??
    $data = addslashes($data);
    //??'_'è¿?æ»¤æ??
    $data = str_replace("_", "\_", $data);
    //??'%'è¿?æ»¤æ??
    $data = str_replace("%", "\%", $data);
    //??'*'è¿?æ»¤æ??
    $data = str_replace("*", "\*", $data);

    $data = str_replace("'", "", $data);
    $data = str_replace('"', "", $data);

    $data = str_replace('£¡', "", $data);
    $data = str_replace('£º', "", $data);
    $data = str_replace('£¿', "", $data);
    $data = str_replace('£¬', "", $data);
    $data = str_replace('¡£', "", $data);
    $data = str_replace('¡°', "", $data);
    $data = str_replace('¡±', "", $data);

    $data = str_replace('¡®', "", $data);
    $data = str_replace('¡¯', "", $data);
    $data = str_replace('£¨', "", $data);
    $data = str_replace('£©', "", $data);
    $data = str_replace('(', "", $data);
    $data = str_replace(')', "", $data);
    $data = str_replace(':', "", $data);
    $data = str_replace('¡¾', "", $data);
    $data = str_replace('¡¿', "", $data);
    $data = str_replace('{', "", $data);
    $data = str_replace('}', "", $data);
    $data = str_replace('-', "", $data);
    $data = str_replace('??', "", $data);
    $data = str_replace('¡¢', "", $data);
    $data = str_replace('¡¶', "", $data);
    $data = str_replace('¡·', "", $data);
    $data = str_replace('<', "", $data);
    $data = str_replace('>', "", $data);
    return $data;
}




