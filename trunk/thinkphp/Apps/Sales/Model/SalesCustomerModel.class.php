<?php
/**
 * SalesCustomerModel.class.php
 * $author$ zhubeihai
 */
namespace Sales\Model;

class SalesCustomerModel extends AbstractModel {

	// 按照客户名称排序
	const ORDER_CUSTOMER_NAME = 1;
	// 根据创建时间倒序
	const ORDER_CREATEDATE_DESC = 2;

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'sc_';
	}

	// 获取客户名称排序
	public function get_order_customer_name() {

		return self::ORDER_CUSTOMER_NAME;
	}

	// 获取创建时间倒序
	public function get_order_createdate_desc() {

		return self::ORDER_CREATEDATE_DESC;
	}

	/**
	 * 编辑客户
	 * @param int $sc_id 客户id
	 * @param int $customerInfo 编辑的用户信息
	 * @return multitype:
	 */
	public function edit_customer($sc_id, $customerInfo) {

		//修改sql语句
		$sql = "UPDATE __TABLE__ SET `sc_name`=?,`sc_short_name`=? ,`sc_source`=? ,`sc_contacter`=? ,`sc_phone`=? , `sc_address`=? , `sc_updated`=? WHERE `sc_id`=? AND `sc_status`<?";
		// 条件参数
		$sc_params = array (
			$customerInfo['sc_name'],
			$customerInfo['sc_short_name'],
			$customerInfo['sc_source'],
			$customerInfo['sc_contacter'],
			$customerInfo['sc_phone'],
			$customerInfo['sc_address'],
			NOW_TIME,
			$sc_id,
			$this->get_st_delete()
		);

		return $this->_m->update($sql, $sc_params);
	}

	/**
	 * 删除
	 * @param int $scid 客户id
	 * @return bool
	 * $author: husendong@vchangyi.com
	 */
	public function delete_customer($sc_ids) {

		// 删除sql语句
		$sql = "UPDATE __TABLE__ SET `sc_status`=? ,`sc_deleted`=? WHERE `sc_id` IN (?) AND `sc_status`<?";
		// 删除条件参数
		$sc_params = array (
			$this->get_st_delete(),
			NOW_TIME,
			$sc_ids,
			$this->get_st_delete()
		);

		return $this->_m->update($sql, $sc_params);
	}

	/**
	 * 客户列表查询
	 * @param array $params 查询条件
	 * @param array $page_option 分页参数
	 * return array 客户列表
	 */
	public function list_customer($params, $page_option) {

		//查询sql
		$sql = "SELECT a.* FROM __TABLE__ a";

		// 条件
		$wheres = array(
			'a.sc_source= ?',
			'a.sc_type= ?',
			'a.sc_status< ?'
		);

		$where_params = array(
			(int)$params["sc_source"],
			(int)$params["sc_type"],
			$this->get_st_delete()
		);

		// 如果有客户名称就模糊匹配
		if (!empty($params["sc_name"])) {
			$wheres[] = 'a.sc_name LIKE ?';
			$where_params[] = '%' . $params["sc_name"] . '%';
		}

		// 如果有客户简称就模糊匹配
		if (!empty($params["sc_short_name"])) {
			$wheres[] = 'a.sc_short_name LIKE ?';
			$where_params[] = '%' . $params["sc_short_name"] . '%';
		}

		// 如果有跟进人就模糊匹配
		if (!empty($params["sc_m_username"])) {
			$wheres[] = 'a.sc_m_username LIKE ?';
			$where_params[] = '%' . $params["sc_m_username"] . '%';
		}

		// 如果有创建开始日期就模糊匹配
		if (!empty($params["s_created"])) {
			$wheres[] = 'sc_created >= ?';
			$where_params[] = $params["s_created"];
		}

		// 如果有创建结束日期就模糊匹配
		if (!empty($params["e_created"])) {
			$wheres[] = 'sc_created <= ?';
			$where_params[] = $params["e_created"];
		}

		// 如果有更新开始日期就模糊匹配
		if (!empty($params["s_updated"])) {
			$wheres[] = 'sc_updated >= ?';
			$where_params[] = $params["s_updated"];
		}

		// 如果有更新结束日期就模糊匹配
		if (!empty($params["e_updated"])) {
			$wheres[] = 'sc_updated <= ?';
			$where_params[] = $params["e_updated"];
		}

		// 如果有销售人员m_uid就模糊匹配
		if (!empty($params["m_uid"])) {
			$wheres[] = 'm_uid in (?)';
			$where_params[] = $params["m_uid"];
		}

		// 排序
		$order_option = array('sc_created' => "DESC");
		if ((int)$params["sort_type"] = $this->get_order_customer_name()) {
			$order_option = array('sc_name' => 'ASC');
		}

		if ((int)$params["sort_type"] = $this->get_order_createdate_desc()) {
			$order_option = array('sc_created' => 'DESC');
		}

		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 根据条件获取客户列表总数
	 * @param array $params 查询条件
	 * return int count 总数
	 */
	public function count_by_condition($params) {

		//查询sql
		$sql = "SELECT COUNT(*) FROM __TABLE__ a";

		// 条件
		$wheres = array(
			'sc_source= ?',
			'sc_type=?',
			'sc_status<?'
		);

		$where_params = array(
			(int)$params["sc_source"],
			(int)$params["sc_type"],
			$this->get_st_delete()
		);

		// 如果有客户名称就模糊匹配
		if (!empty($params["sc_name"])) {
			$wheres[] = 'a.sc_name LIKE ?';
			$where_params[] = '%' . $params["sc_name"] . '%';
		}

		// 如果有客户简称就模糊匹配
		if (!empty($params["sc_short_name"])) {
			$wheres[] = 'a.sc_short_name LIKE ?';
			$where_params[] = '%' . $params["sc_short_name"] . '%';
		}

		// 如果有跟进人就模糊匹配
		if (!empty($params["sc_m_username"])) {
			$wheres[] = 'a.sc_m_username LIKE ?';
			$where_params[] = '%' . $params["sc_m_username"] . '%';
		}

		// 如果有创建开始日期就模糊匹配
		if (!empty($params["s_created"])) {
			$wheres[] = 'sc_created >= ?';
			$where_params[] = $params["s_created"];
		}

		// 如果有创建结束日期就模糊匹配
		if (!empty($params["e_created"])) {
			$wheres[] = 'sc_created <= ?';
			$where_params[] = $params["e_created"];
		}

		// 如果有更新开始日期就模糊匹配
		if (!empty($params["s_updated"])) {
			$wheres[] = 'sc_updated >= ?';
			$where_params[] = $params["s_updated"];
		}

		// 如果有更新结束日期就模糊匹配
		if (!empty($params["e_updated"])) {
			$wheres[] = 'sc_updated <= ?';
			$where_params[] = $params["e_updated"];
		}

		// 如果有销售人员m_uid就模糊匹配
		if (!empty($params["m_uid"])) {
			$wheres[] = 'm_uid IN (?)';
			$where_params[] = $params["m_uid"];
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $wheres), $where_params);
	}
}
