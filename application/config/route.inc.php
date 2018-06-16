<?php
//简单路由配置,必要的转义在RouteAction->index中处理
$RouteConfig = array(
	array("reg" => "/","action"=> "IndexAction","method" => "index"),
	//array("reg" => "/search.html","action"=> "SearchAction","method" => "index"),
);

global $RouteConfig;
