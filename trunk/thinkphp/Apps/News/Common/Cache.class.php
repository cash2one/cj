<?php
/**
 * Cache.class.php
 * 新闻公告
 * $Author$
 */

namespace News\Common;

class Cache extends \Com\Cache {

	// 实例化
	public static function &instance() {

		static $instance;
		if(empty($instance)) {
			$instance	= new self();
		}

		return $instance;
	}

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取 news_setting
	 */
	public function setting() {

		// 获取留言本配置表数据
		$serv = D('News/NewsSetting', 'Service');
		return $serv->list_kv();
	}

}
