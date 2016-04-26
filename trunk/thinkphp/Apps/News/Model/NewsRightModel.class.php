<?php
/**
 * 公告权限 Model.
 * User: Muzhitao
 * Date: 2015/9/16 0016
 * Time: 10:15
 */
namespace News\Model;

class NewsRightModel extends AbstractModel {
	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据分类ID查询权限列表
	 * @param $nca_id
	 * @return array
	 */
	public function list_ne_by_nca_id($nca_id) {

		$sql = "SELECT ne_id, is_all, m_uid, cd_id FROM __TABLE__ WHERE nca_id=? AND status<?";
		$params = array($nca_id, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据公告ID，查询权限列表
	 * @param $ne_id
	 * @return array
	 */
	public function list_ne_by_ne_id($ne_id) {

		$sql = "SELECT ne_id, m_uid, cd_id FROM __TABLE__ WHERE ne_id=? AND status<?";
		$params = array($ne_id, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 通过用户ID 查询所在的部门ID
	 * @param $m_uid
	 * @return array
	 */
	public function get_cd_by_uid($m_uid) {

		$sql = "SELECT cd_id FROM `oa_member_department` WHERE m_uid=? AND md_status<? LIMIT 1";
		$params = array($m_uid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}
}
