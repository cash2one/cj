<?php
/**
 * SalesRecordModel.class.php
 * $author$
 */

namespace Sales\Model;

class SalesRecordModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = '';
	}

	/**
	 * 商机状态变更记录列表查询
	 * @param array $params
	 * @param array $page_option
	 * @param $order_option
	 * @return array|bool
	 */
	public function list_business_modify_record($params, $page_option, $order_option) {

		$sql = "SELECT A.m_username, A.`m_uid`, A.`sr_content`, A.`sr_type`,
				B.`stp_name`, A.`sc_short_name`, A.`sr_updated`, A.`sr_created`, C.`m_face`
				FROM __TABLE__ A
				LEFT JOIN `oa_sales_type` B ON A.`sr_type` = B.`stp_id`
				LEFT JOIN `oa_member` C ON A.`m_uid` = C.`m_uid`";

		// 查询条件
		$where = array('sr_status<?');
		$where_params = array($this->get_st_delete());

		// 商机id
		if (!empty($params['sb_id'])) {
			$where[] = "A.sb_id = ?";
			$where_params[] = $params['sb_id'];
		}

		// 分页参数
		$limit = '';
		$this->_limit($limit, $page_option);
		// 排序
		$orderby = '';
		$this->_order_by($orderby, $order_option);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 获得结果集
	 * @param array $params 传入参数
	 * @return array 数量
	 */
	public function count_by_condition($params) {
		$sql = "SELECT COUNT(*) FROM __TABLE__ A
				LEFT JOIN `oa_sales_type` B ON A.`sr_type` = B.`stp_id`
				LEFT JOIN `oa_member` C ON A.`m_uid` = C.`m_uid`";

		// 查询条件
		$where = array("sr_status<?");
		$where_params = array($this->get_st_delete());

		// 商机id
		if (!empty($params['sb_id'])) {
			$where[] = "A.sb_id = ?";
			$where_params[] = $params['sb_id'];
		}

		return $this->_m->result($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}
}
