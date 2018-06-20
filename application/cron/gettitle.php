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

$url = "http://news.163.com/special/0001386F/rank_lady.html";

$htmlinfo = file_get_contents($url);

$s_start = "tabContents";
$s_end = "channel-end";

$num_start = strpos($htmlinfo, $s_start);
$num_end = strpos($htmlinfo, $s_end);

if ($num_start === false) {
    exit("???��??");
}

$content = substr($htmlinfo, $num_start, $num_end - $num_start);

$pattern = '/<a href="[^"]*"[^>]*>(.*)<\/a>/';    // �????��????正�??表达�?

preg_match_all($pattern, $content, $matches);

$titlelist = $matches[1];

$sitemodel = new DBModel('default');

foreach ($titlelist as $title) {
    $title = trim($title);
    $md5key = md5($title);

    $s_sql = "select * from sitetitle where md5key='".$md5key."'";
    $titleinfo = $sitemodel->_db->query($s_sql)->fetch();
    if ($titleinfo) {
        continue;
    }

    $title = safe($title);

    $insertymd = date("Ymd");

    try {
        $i_sql = "insert into sitetitle(title, md5key, insertymd) values ('" . $title . "', '" . $md5key . "', {$insertymd})";
        $sitemodel->_db->exec($i_sql);
    } catch(Exception $a){

    }

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
