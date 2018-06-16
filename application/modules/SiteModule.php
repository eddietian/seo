<?php
class SiteModule extends BaseModel{
	
	public $_table_site_list = "sitelist";
	public $_table_site_tpl = "sitetpl";

	const DEFAULT_SITE_HOST = "default.com";
	const DEFAULT_SITE_HOSTNAME = "seo.default.com";

	public function __construct($db_choose="default"){
		parent::__construct($db_choose);
	}
	
	public function getSiteByHostName($hostname) {
		$hostname = trim($hostname);
		$where = array(
			"hostname" => $hostname
		);
		return $this->table($this->_table_site_list)->where($where)->find();
	}

	public function getSiteTplByHost($host) {
		$host = trim($host);
		$where = array(
			"host" => $host
		);
		return $this->table($this->_table_site_tpl)->where($where)->select();
	}
	
}