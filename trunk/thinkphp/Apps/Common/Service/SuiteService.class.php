<?php
/**
 * SuiteService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractService;

class SuiteService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/Suite');
	}

	/**
	 * 根据 $suiteid 获取套件信息
	 * @param unknown $suiteid
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_suiteid($suiteid) {

		return $this->_d->get_by_suiteid($suiteid);
	}

	/**
	 * 插入数据
	 * @param array $data 套件信息
	 */
	public function insert($data, $options = array(), $replace = false) {

		$this->_d->insert($data);
	}

	// 读取所有套件信息
	public function list_all($page_option = null, $order_option = array()) {

		$list = $this->_d->list_all();
		$rets = array();
		foreach ($list as $_v) {
			$rets[$_v['suiteid']] = $_v;
		}

		return $rets;
	}

	/**
	 * 更新套件信息
	 * @param string $suiteid 套件ID
	 * @param array $data 套件信息
	 */
	public function update_by_suiteid($suiteid, $data) {

		return $this->_d->update_by_suiteid($suiteid, $data);
	}

}
