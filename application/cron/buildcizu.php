<?php
//����scws�ʿ�
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

$file = __DIR__."/a.txt";

$wordlist = array();
$fp = fopen($file, 'r'); //�ļ�
while (!feof($fp)) {
    //for($j=1;$j<=1000;$j++) {     //��ȡ�����1000�в��洢��������
    $wordlist[] = stream_get_line($fp, 65535, "\n");
    // break;
    // }
}

foreach ($wordlist as $v) {
    $line = sprintf("%s\t%.2f\t%.2f\t%.2s\n", trim($v), 10.00, 10.00, "n");

    file_put_contents($file."_done.txt", $line, FILE_APPEND);
}