<?php
	/**
	* 框架的基本配置，下面是数据库和 memcached/ redis等的配置
	*
	*/
	$appConfig = array(
		'app' => 'AppController',//应用类名称
		'group_list'=>array('search'),//分组自动加载action和module
		//常量定义,可以覆盖框架原有的常量设定
		'define' => array(
			'APP_MODULE_ROOT'   => APP_ROOT.'/application',
			'APP_STATIC'   => APP_ROOT.'/static',
			'LOG_LEVEL'    => 2,
			'APP_DOMAIN' => 'youyo88.com',
			'APP_OFFICAL' => '',
			'LOG_PATH'  => APP_ROOT . '/application/data/logs/',
			'CHARSET_PAGE'  => 'utf-8', //PHP页面编码
			'APP_VERSION'    => '20180108',
			'SPHINX_SERVER'=>'192.168.2.161',
			'SPHINX_PORT'=> 9312,
			'JS_CSS_DATE' => 20150828,
			'LOGIN_SECURE_KEY' =>'ldxpe4.3x', //COOKIE OR SESSION KEY
			'LOGIN_SECURE_TIME' => 7200,  //COOKIE　过期时间，单位秒
			'LOGIN_SECURE_TYPE' => 1,  //1:Cookie,2:SESSION
			'LOGIN_SECURE_MD5KEY' => 'abcd12345$@!osos',
			'SYSTEM_MEMU_ACCESS_LV' => 1,     //系统菜单访问级别 1:超级管理员,2:普通管理员
            'NOLOGIN_GET_GIFT_KEY' =>'Gift_UID_Gift',//不登录礼包领取cookie设置
            'NOLOGIN_GET_GIFT_TIME' =>3600*30*12,//不登录礼包领取cookie设置时间
		),
		//应用主入口，没有任何参数时进入
		'run' => array(
			'class'=>'home',
			'function'=>'index',
		),
		'errorReportLevel' => E_ALL & ~E_NOTICE,//错误提示级别

		//需要导入的库目录，自动在对应的目录查找同名的$class.php文件
		'includePath' => array(
// 			'data_model' => APP_ROOT .'/application/modules/',
		),
		
		//应用需要加载的文件,每次请求都会加载
		'autoIncludeFile' => array(
			'AppHelper' => APP_ROOT.'/core/AppHelper.php',
		),
		
		'actions' => array(
			'*' => true,//控制除下面设定的接口权限外的所有权限
			'default' => array('*'=>1),//defaultAction的所有接口可以访问
	       	'className' => array(//类1
	           	'function1' => 1,    //function1:允许
	           	'function2' => 0,    //function1:禁止
	       	),
		),
	);

	//---------------------------------数据库配置--------------------------------------------
	$dbConfig['default'] = array(
		'DB_TYPE' => 'mysql',
		'DB_CHARSET' => 'utf8',
		'DB_PERSISTENT' => false,
		'DB_HOST'=>'127.0.0.1',
		'DB_PORT'=>3306,
		'DB_USER'=>'root',
		'DB_PASSWD'=>'',
		'DB_NAME'=>'gold_site',
	);


	$redisConfig = array(
		'default' =>array(
			'server'		=> '127.0.0.1',
			'port'			=> 6379,
			'timeout'		=> 10,
		),
		'default_slave' =>array(
			'server'		=> '127.0.0.1',
			'port'			=> 6379,
			'timeout'		=> 10,
		),
	);
	
