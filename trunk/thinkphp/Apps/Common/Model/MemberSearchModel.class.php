<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/22
 * Time: 下午3:19
 */

namespace Common\Model;

use Common\Model\AbstractModel;

class MemberSearchModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'ms_';
	}

	/**
	 * 根据cdid和关键字搜索数据计算总数
	 * @param $cd_id int 部门id
	 * @param $keyword string 关键字
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页条件
	 * @return bool|void
	 */
	public function count_by_keyword_status($keyword, $status) {

		$wheres = array();
		$params = array();

		if (!empty($status)) {
			$wheres[] = "`a`.`m_qywxstatus` IN (?)";
			$params[] = (array)$status;
		}

		// keyword，member关联
		if (!empty($keyword)) {
			$wheres[] = "`b`.`ms_message` LIKE ?";
			$params[] = '%' . $keyword . '%';
			$wheres[] = "`b`.`ms_status`<?";
			$params[] = $this->get_st_delete();
			$wheres[] = "a.m_status<?";
			$params[] = $this->get_st_delete();
		}

		return $this->_m->result("SELECT COUNT(*) FROM `oa_member` AS a LEFT JOIN __TABLE__ AS b ON a.m_uid=b.m_uid WHERE " . implode(" AND ", $wheres), $params);
	}

	/**
	 * 根据cdid和关键字搜索数据
	 * @param $cd_id int 部门id
	 * @param $keyword string 关键字
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页条件
	 * @return bool|void
	 */
	public function list_by_keyword_status($keyword, $status, $page_option, $order_option = array('m_index' => 'ASC')) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$wheres = array();
		$params = array();

		if (!empty($status)) {
			$wheres[] = "`a`.`m_qywxstatus` IN (?)";
			$params[] = (array)$status;
		}
		// keyword，member关联
		if (!empty($keyword)) {
			$wheres[] = "`b`.`ms_message` LIKE ?";
			$params[] = '%' . $keyword . '%';
			$wheres[] = "`b`.`ms_status`<?";
			$params[] = \Common\Model\MemberSearchModel::ST_DELETE;
			$wheres[] = "a.m_status<?";
			$params[] = $this->get_st_delete();
		}

		return $this->_m->fetch_array("SELECT b.* FROM `oa_member` AS a
				LEFT JOIN __TABLE__ AS b ON a.m_uid=b.m_uid
				WHERE " . implode(" AND ", $wheres) . $orderby . $limit, $params);
	}

	/**
	 * 根据关键字和部门id搜索
	 * @param string $keyword 关键字
	 * @param int|array $cdids 部门ID
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_keyword_cdids($keyword, $cdids, $page_option, $order_option = array('m_index' => 'ASC')) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$wheres = array();
		$params = array();
		$this->_params_by_keyword_cdids($keyword, $cdids, $wheres, $params);
		$wheres[] = "m_status<?";
		$params[] = \Common\Model\MemberModel::ST_DELETE;

		return $this->_m->fetch_array("SELECT DISTINCT `b`.m_uid FROM oa_member_department AS `a`
				LEFT JOIN __TABLE__ AS `b` ON `a`.`m_uid`=`b`.`m_uid`
				LEFT JOIN oa_member AS `c` ON `b`.`m_uid`=`c`.`m_uid`
				WHERE " . implode(' AND ', $wheres) . $orderby . $limit, $params);
	}

	public function count_by_keyword_cdids($keyword, $cdids) {

		$wheres = array();
		$params = array();
		$this->_params_by_keyword_cdids($keyword, $cdids, $wheres, $params);

		return $this->_m->fetch_array("SELECT COUNT(DISTINCT `b`.m_uid) FROM oa_member_department AS `a`
				LEFT JOIN __TABLE__ AS `b` ON `a`.`m_uid`=`b`.`m_uid`
				WHERE " . implode(' AND ', $wheres), $params);
	}

	protected function _params_by_keyword_cdids($keyword, $cdids, &$wheres, &$params) {

		$wheres[] = '`b`.`ms_message` LIKE ?';
		$params[] = '%' . $keyword . '%';

		$wheres[] = '`a`.`cd_id` IN (?)';
		$params[] = $cdids;

		// 各表的数据状态
		$wheres[] = '`md_status`<? AND `ms_status`<?';
		$params[] = \Common\Model\MemberDepartmentModel::ST_DELETE;
		$params[] = $this->get_st_delete();
		return true;
	}

	/**
	 * 根据关键字搜索
	 * @param string $keyword 关键字
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_keyword($keyword, $page_option = array(), $order_option = array('m_index' => 'ASC')) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$wheres = array();
		$params = array();
		$this->_params_by_keyword($keyword, $wheres, $params);

		return $this->_m->fetch_array("SELECT `b`.* FROM oa_member AS `a`
				LEFT JOIN __TABLE__ AS `b` ON `a`.`m_uid`=`b`.`m_uid`
				WHERE " . implode(' AND ', $wheres) . $orderby . $limit, $params);
	}

	public function count_by_keyword($keyword) {

		$wheres = array();
		$params = array();
		$this->_params_by_keyword($keyword, $wheres, $params);
		return $this->_m->fetch_array("SELECT COUNT(*) FROM oa_member AS `a`
				LEFT JOIN __TABLE__ AS `b` ON `a`.`m_uid`=`b`.`m_uid`
				WHERE " . implode(' AND ', $wheres), $params);
	}

	public function _params_by_keyword($keyword, &$wheres, &$params) {

		$wheres[] = '`b`.`ms_message` LIKE ?';
		$params[] = '%' . $keyword . '%';

		// 各表的数据状态
		$wheres[] = '`ms_status`<? AND `m_status`<?';
		$params[] = $this->get_st_delete();
		$params[] = \Common\Model\MemberModel::ST_DELETE;
		return true;
	}

}
