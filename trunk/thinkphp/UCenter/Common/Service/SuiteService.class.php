<?php
/**
 * SuiteService.class.php
 * $author$
 */

namespace Common\Service;

class SuiteService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Suite");
	}

	/**
	 * 根据 $suiteid 获取套件信息
	 * @param string $suiteid 套件ID
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_suiteid($suiteid) {

		return $this->_d->get_by_suiteid($suiteid);
	}

	/**
	 * 根据 $suiteid 更新套件信息
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件ID
	 */
	public function update_by_suiteid($suite, $suiteid) {

		return $this->_d->update_by_suiteid($suite, $suiteid);
	}

}
