<?php
namespace Jobtrain\Model;

class JobtrainRightModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 检查权限
	 *
	 * @param int $aid
	 * @param int $cid
	 * @return bool
	 */
	public function list_by_aid_cid($aid, $cid) {

		$sql = "SELECT is_all, m_uid, cd_id FROM __TABLE__ WHERE (aid=? OR cid=?) AND status<?";
		$params = array($aid, $cid, $this->get_st_delete());
		$result = $this->_m->fetch_array($sql, $params);
		return $result;
	}

}