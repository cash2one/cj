<?php
/**
 * Cache.class.php
 * 考试中心
 * $Author$
 */

namespace Exam\Common;

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
	 * 获取 exam_setting
	 */
	public function setting() {

		// 获取考试中心配置表数据
		$serv = D('Exam/ExamSetting', 'Service');
		return $serv->list_kv();
	}

}
