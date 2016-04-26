<?php
/**
 * SuiteModel.class.php
 * $author$
 */

namespace Common\Model;
use Common\Model\AbstractModel;

class SuiteModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = '';
	}

	/**
	 * 根据 $suiteid 获取套件信息
	 * @param unknown $suiteid
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_suiteid($suiteid) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `suiteid`=? AND `status`<?", array(
			$suiteid, $this->get_st_delete()
		));
	}

	/**
	 * 更新套件信息
	 * @param string $suiteid 套件ID
	 * @param array $data 套件信息
	 */
	public function update_by_suiteid($suiteid, $data) {

		// SET 占位符
		$sets = array();
		// SET 参数
		$params = array();
		// 遍历需要更新的数据
		foreach ($data as $_k => $_v) {
			$sets[] = "`{$_k}`=".(is_array($_v) ? '(?)' : '?');
			$params[] = $_v;
		}

		$params[] = $suiteid; // 套件ID
		$params[] = $this->get_st_delete(); // 状态
		return $this->_m->execsql("UPDATE __TABLE__ SET ".implode(',', $sets)." WHERE `suiteid`=? AND `status`<?", $params);
	}

}
