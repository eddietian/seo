<?php
/**
 */
date_default_timezone_set('PRC');
$dir = dirname(__FILE__);
//全局配置文件目录
define('APP_ROOT', realpath($dir . '/../../'));
require_once(APP_ROOT.'/../bfw/Main.php');
require_once(APP_ROOT."/application/config/config.inc.php");
Main::setConfig($appConfig);
Main::init();









