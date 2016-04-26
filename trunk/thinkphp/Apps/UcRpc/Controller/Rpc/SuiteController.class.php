<?php
/**
 * SuiteController.class.php
 * $author$
 */

namespace UcRpc\Controller\Rpc;

class SuiteController extends AbstractController {

	/**
	 * 根据 $suiteid 读取套件信息
	 * @param string $suiteid 套件ID
	 */
	public function get_by_suiteid($suiteid) {

		// 如果参数是一个非标量的值
		if (!is_scalar($suiteid)) {
			return false;
		}

		$suiteid = (string)$suiteid;
		$d = D('Common/Suite', 'Service');
		return $d->get_by_suiteid($suiteid);
	}

	/**
	 * 更新套件信息
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件ID
	 * @return multitype:
	 */
	public function update_by_suiteid($suite, $suiteid) {

		// 更新数据
		$d = D('Common/Suite', 'Service');
		return $d->update_by_suiteid($suiteid, $suite);
	}

}
