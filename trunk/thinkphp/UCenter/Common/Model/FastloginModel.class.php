<?php
/**
 * FastloginModel.class.php
 * $author$
 */

namespace Common\Model;

class FastloginModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据企业 corpid 读取信息
	 * @param string $corpid 企业corpid
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_corpid($corpid) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `corpid`=? AND `status`<? LIMIT 1", array(
			$corpid, $this->get_st_delete()
		));
	}

}
