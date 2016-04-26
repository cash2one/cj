<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午4:26
 */
namespace Questionnaire\Model;

class QuestionnaireRecordModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 我的问卷
	 * @param $cond
	 * @param $page_option
	 * @param $order_option
	 */
	public function myList_by_condition($cond, $page_option, $order_option) {

		// 查询条件
		$where[] = 'qnr.uid = ?';
		$where_params[] = $cond['uid'];
		// 状态条件
		$where[] = "qnr.`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();

		// 状态条件
		$where[] = "qn.`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT qnr.qr_id, qnr.uid, qn.* FROM __TABLE__ as qnr LEFT JOIN `oa_questionnaire` as qn ON qnr.qu_id = qn.qu_id";

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 我的问卷总数
	 * @param $cond
	 * @return array
	 */
	public function myTotal_by_condition($cond) {

		// 查询条件
		$where[] = 'qnr.uid = ?';
		$where_params[] = $cond['uid'];
		// 状态条件
		$where[] = "qnr.`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();

		// 状态条件
		$where[] = "qn.`{$this->prefield}status`<?";
		$where_params[] = $this->get_st_delete();

		$sql = "SELECT count(*) FROM __TABLE__ as qnr LEFT JOIN `oa_questionnaire` as qn ON qnr.qu_id = qn.qu_id";

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 查询所有数据里的字段数据
	 * @param       $qu_id
	 * @param       $field
	 * @param       $page_option
	 * @param array $order_option
	 * @return array|bool
	 */
	public function allList_filed_by_condition($qu_id, $field = '*', $page_option, $order_option = array()) {

		// 状态条件
		$where[] = "`status`<?";
		$where_params[] = $this->get_st_delete();
		$where[] = "`qu_id`=?";
		$where_params[] = $qu_id;
		if (!empty($field) && is_array($field)) {
			$field = implode(',', $field);
		}
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT {$field} FROM __TABLE__";

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}
}