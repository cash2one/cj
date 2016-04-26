<?php
/**
 * ExamSettingService.class.php
 * $author$
 */

namespace Exam\Service;
use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class ExamSettingService extends AbstractSettingService {

	protected $_memeber_model;
	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Exam/ExamSetting");
	}

	// 读取所有
	public function list_kvs() {

		// 查询
		$list = $this->_d->list_all();

		// 重新整合, 改成 key-value 键值对
		$sets = array();
		foreach ($list as $_v) {
			$sets[$_v['key']] = unserialize($_v['value']);
		}

		return $sets;
	}
}