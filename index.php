<?php
/**
 * 系统入口
 * 	1 ) 读取配置
 * 	2 ) 设定调试级别
 * 	3 ) 负责调用App
 */

//全局配置文件目录
define('APP_ROOT',dirname(__FILE__));

$_GET['r'] = $_REQUEST['r'] = isset($_REQUEST['r']) && $_REQUEST['r'] ? $_REQUEST['r'] : null;

require_once(APP_ROOT.'/application/config/config.inc.php');
require_once(APP_ROOT.'/application/config/route.inc.php');
require_once(APP_ROOT.'/../bfw/Main.php');
//接口系统不需要开启
// if (! isset ( $_SESSION )) {
// 	session_start ();
// }
Main::setConfig($appConfig);
Main::run();