<?php
/**
 * Cache.class.php
 * 留言本缓存
 * $Author$
 */
namespace Addressbook\Common;

class Cache extends \Com\Cache {

	// 实例化
	public static function &instance() {

		static $instance;
		if (empty($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取setting 的缓存信息;
	 * @return array
	 */
	public function setting() {

		// 获取留言本配置表数据
		$serv = D('Addressbook/AddressbookSetting', 'Service');
		return $serv->list_kv();
	}

}
