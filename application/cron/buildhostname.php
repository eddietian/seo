<?php
/**
 */
date_default_timezone_set('PRC');
$dir = dirname(__FILE__);

define('APP_ROOT', realpath($dir . '/../../'));
require_once(APP_ROOT.'/../bfw/Main.php');
require_once(APP_ROOT."/application/config/config.inc.php");
Main::setConfig($appConfig);
Main::init();

header("Content-type: text/html; charset=gb2312");

$sitemodel = new DBModel('default');


$yesterday = date("Ymd", strtotime("-1 day"));
$sitemodel = new DBModel('default');

//选择关键字  和 域名
$host = "dejson.com";
$s_title_sql = "select id,title from sitetitle where insertymd>={$yesterday} and state = 1";
$s_title_sql = "select id,title from sitetitle  where id=362  limit 100";

$rs = $sitemodel->_db->query($s_title_sql)->fetchAll();

$logarray = array();
$titleids = array();
foreach ($rs as $id => $v) {
    $titleids[$id] = $id;
    $logarray[] = $v['title'];
}


/*$key = '里约奥运有哪些故事呢';

$words_array = parse($key);

print_r($words_array);
exit;
*/
$so =  scws_new();
$so->set_charset('gbk');

$data = array();
foreach ($logarray as $title) {
    $so->send_text($title);

    $czlist = $so->get_result();
    print_r($czlist);exit;
    $cizulist = WordService::buildcizu($czlist);

    if (!empty($cizulist)) {
        $strarr = array();
        foreach ($cizulist as $cizu) {
            $strarr[] = join("", $cizu);
        }

        $strarr = array_slice($strarr,0 ,4);

        $tmp = array(
            'title' => safe($title),
            'keywords' => join(" ", $strarr)
        );
        $data[] = $tmp;
    }
}

if (empty($data)) {
    exit("没有生成词组！");
}

$list = generateCode(count($data)+50, "");

foreach ($data as $id => $info) {
    $hostname = $list[$id].".".$host;

    $sql = "select * from sitelist where hostname='{$hostname}'";
    $hostinfo = $sitemodel->_db->query($sql)->fetch();
    if ($hostinfo) {
        continue;
    }


    $time = time();
    $insertymd = date("Ymd");
    $upsql = "insert into sitelist (hostname,host, title, keywords, inserttime, insertymd) values ('".$hostname."','".$host."', '{$info['title']}', '{$info['keywords']}', {$time}, {$insertymd})";
    $sitemodel->_db->exec($upsql);
}

echo "done:".count($data);

function generateCode( $nums, $exist_array='', $code_length = 6, $prefix = '' ) {


    $characters = "0123456789abcdefghijklmnpqrstuvwxyz";
    $promotion_codes = array();

    for($j = 0 ; $j < $nums; $j++) {

        $code = '';

        $code_length =  mt_rand(4,9);
        for ($i = 0; $i < $code_length; $i++) {

            $code .= $characters[mt_rand(0, strlen($characters)-1)];

        }

        if( !in_array($code,$promotion_codes) ) {

            if( is_array($exist_array) ) {

                if( !in_array($code,$exist_array) ) {

                    $promotion_codes[$j] = $prefix.$code;

                } else {

                    $j--;

                }

            } else {

                $promotion_codes[$j] = $prefix.$code;

            }

        } else {
            $j--;
        }
    }

    return $promotion_codes;
}


function safe($data){ //瀹??ㄨ?婊ゅ?芥??
    $data = addslashes($data);
    //??'_'杩?婊ゆ??
    $data = str_replace("_", "\_", $data);
    //??'%'杩?婊ゆ??
    $data = str_replace("%", "\%", $data);
    //??'*'杩?婊ゆ??
    $data = str_replace("*", "\*", $data);

    $data = str_replace("'", "", $data);
    $data = str_replace('"', "", $data);

    $data = str_replace('！', "", $data);
    $data = str_replace('：', "", $data);
    $data = str_replace('？', "", $data);
    $data = str_replace('，', "", $data);
    $data = str_replace('。', "", $data);
    $data = str_replace('“', "", $data);
    $data = str_replace('”', "", $data);

    $data = str_replace('‘', "", $data);
    $data = str_replace('’', "", $data);
    $data = str_replace('（', "", $data);
    $data = str_replace('）', "", $data);
    $data = str_replace('(', "", $data);
    $data = str_replace(')', "", $data);
    $data = str_replace(':', "", $data);
    $data = str_replace('【', "", $data);
    $data = str_replace('】', "", $data);
    $data = str_replace('{', "", $data);
    $data = str_replace('}', "", $data);
    $data = str_replace('-', "", $data);
    $data = str_replace('??', "", $data);
    $data = str_replace('、', "", $data);
    $data = str_replace('《', "", $data);
    $data = str_replace('》', "", $data);
    $data = str_replace('<', "", $data);
    $data = str_replace('>', "", $data);
    return $data;
}


function parse($str)
{
    $cws =  scws_new();
    $dictPath = ini_get('scws.default.fpath').'/dict.xdb';
    $cws->set_dict($dictPath);

    //自定义分词库
    $myDictPath = ini_get('scws.default.fpath').'/mydict.xdb';
    if(file_exists($myDictPath))
    {
        $cws->add_dict($myDictPath);
    }
    $cws->set_ignore(true);

    $utf8Str = iconv("UTF-8","GB2312//IGNORE",$str);
    $cws->send_text($utf8Str);
    $resArr = array();
    while($tmp = $cws->get_result())
    {
        $resArr[] = $tmp;
    }
    $cws->close();

    return $resArr;
}


