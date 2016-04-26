<?php
/**
 * Cache.class.php
 * 文件管理缓存
 * @create-time: 2015-07-01
 */
namespace File\Common;

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
	 * 获取 file_setting 的缓存信息;
	 * @return array
	 */
	public function setting() {

		// 获取文件配置表数据
		$serv = D('File/FileSetting', 'Service');
		return $serv->list_kv();
	}

	/**
	 * 获取文件类型 oa_file_type 缓存信息
	 * @return mixed
	 */
	public function filetype() {

		// 获取文件类型表数据
		$serv = D('File/FileType', 'Service');
		return $serv->list_all();
	}
}
