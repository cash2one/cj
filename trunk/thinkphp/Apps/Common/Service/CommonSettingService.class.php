<?php
/**
 * CommonSettingService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractSettingService;

class CommonSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/CommonSetting');
	}

	// 读取所有
	public function list_kv() {

		// 查询
		$list = $this->_d->list_all();
		// 重新整合, 改成 key-value 键值对
		$sets = array();
		foreach ($list as $_v) {
			if ($this->_d->get_st_delete() == $_v['cs_type']) {
				$sets[$_v['cs_key']] = unserialize($_v['cs_value']);
			} else {
				$sets[$_v['cs_key']] = $_v['cs_value'];
			}
		}

		return $sets;
	}

}
