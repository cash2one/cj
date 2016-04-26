<?php
/**
 * SuiteModel.class.php
 * $author$
 */

namespace Common\Model;

class SuiteModel extends \Common\Model\AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'su_';
	}

	/**
	 * 根据 $suiteid 获取套件信息
	 * @param string $suiteid 套件ID
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_suiteid($suiteid) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `{$this->prefield}suite_id`=? AND `{$this->prefield}status`<?", array(
			$suiteid, $this->get_st_delete()
		));
	}

	/**
	 * 根据 $suiteid 更新套件信息
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件ID
	 */
	public function update_by_suiteid($suite, $suiteid) {

		$sets = array();
		$params = array();
		// 拼凑更新 SQL
		foreach ($suite as $_k => $_v) {
			$sets[] = "`$_k`=?";
			$params[] = is_array($_v) ? serialize($_v) : $_v;
		}

		$params[] = $suiteid;
		$params[] = $this->get_st_delete();

		return $this->_m->execsql("UPDATE __TABLE__ SET ".implode(",", $sets)." WHERE `{$this->prefield}suite_id`=? AND `{$this->prefield}status`<?", $params);
	}

}
