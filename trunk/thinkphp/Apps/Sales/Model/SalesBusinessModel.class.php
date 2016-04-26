<?php
/**
 * SalesBusinessModel.class.php
 * $author$
 */
namespace Sales\Model;

class SalesBusinessModel extends AbstractModel {

	// 签单可能性
	const TYPE_SIGN = 1;
	// 销售金额
	const TYPE_AMOUNT = 2;
	// 跟进时间
	const TYPE_DATE = 3;

	// 获取签单可能性
	public function get_type_sign() {

		return self::TYPE_SIGN;
	}

	// 获取销售金额
	public function get_type_amount() {

		return self::TYPE_AMOUNT;
	}

	// 获取跟进日期
	public function get_type_date() {

		return self::TYPE_DATE;
	}

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'sb_';
	}

	/**
	 * 商机列表查询
	 * @param array $params
	 * @param array $page_option
	 * @param $order_option
	 * @return array|bool
	 */
	public function list_business($params, $page_option, $order_option) {

		$sql = "SELECT A.sb_id, A.`sb_name`, A.`m_username`, A.`sb_type`, B.`sc_short_name`,
				C.`stp_name`, A.`sb_amount`, A.`sb_comments`, A.`sb_updated`, A.`sb_created`
				FROM __TABLE__ A
				LEFT JOIN `oa_sales_customer` B ON A.`sc_id` = B.`sc_id`
				LEFT JOIN `oa_sales_type` C ON A.`sb_type` = C.`stp_id` ";

		// 查询条件
		$where = array('sb_status<?');
		$where_params = array($this->get_st_delete());

		// 商机状态
		if (!empty($params['sb_type'])) {
			$where[] = "A.sb_type = ?";
			$where_params[] = (int)$params['sb_type'];
		}

		// 开始时间
		if (!empty($params['start_date'])) {
			$where[] = "A.sb_created >= ?";
			$where_params[] = $params['start_date'];
		}
		// 结束时间
		if (!empty($params['end_date'])) {
			$where[] = "A.sb_created < ?";
			$where_params[] = $params['end_date'];
		}

		// 负责人ID
		$sb_ids = array_filter($params['m_uids']);
		if (!empty($sb_ids)) {
			$where[] = "A.m_uid IN (?)";
			$where_params[] = $sb_ids;
		}

		// 商机名称
		if (!empty($params['sc_name'])) {
			$where[] = "B.sc_short_name like ?";
			$where_params[] = '%' . $params['sc_name'] . '%';
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

		$sql = "SELECT count(*) FROM __TABLE__ A LEFT JOIN oa_sales_customer B ON A.`sc_id`=B.`sc_id`";
		// 查询条件
		$where = array("A.sb_status<?");
		$where_params = array($this->get_st_delete());

		// 负责人ID
		if (!empty($params['uids'])) {
			$where[] = "A.m_uid IN (?)";
			$where_params[] = $params['uids'];
		}

		// 客户名称
		if (!empty($params['sc_name'])) {
			$where[] = "B.sc_name like ?";
			$where_params[] = '%' . $params['sc_name'] . '%';
		}

		// 商机状态
		if (!empty($params['sb_type'])) {
			$where[] = "A.sb_type = ?";
			$where_params[] = $params['sb_type'];
		}

		// 开始时间
		if (!empty($params['start_date'])) {
			$where[] = "A.sb_updated >= ?";
			$where_params[] = $params['start_date'];
		}

		// 结束时间
		if (!empty($params['end_date'])) {
			$where[] = "A.sb_updated < ?";
			$where_params[] = $params['end_date'];
		}

		return $this->_m->result($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 编辑商机详情
	 * @param int $sb_id 商机ID
	 * @param string $sb_name 商机名称
	 * @param decimal $sb_amount 预计销售额
	 * @param int $sb_type 客户状态
	 * @param string $sb_comments 备注
	 * @return array
	 */
	public function edit_business($sb_id, $sb_name, $sb_amount, $sb_type, $sb_comments) {

		$sql = "UPDATE __TABLE__ SET sb_name=?, sb_type=?, sb_amount=?, sb_comments=?, sb_status=?, sb_updated=? WHERE sb_id=? AND sb_status<?";

		// 条件参数
		$params = array(
			$sb_name,
			$sb_type,
			$sb_amount,
			$sb_comments,
			$this->get_st_update(),
			NOW_TIME,
			$sb_id,
			$this->get_st_delete()
		);

		return $this->_m->update($sql, $params);
	}

	/**
	 * 删除商机
	 * @param array $sb_ids 商机ID
	 */
	public function delete_business($sb_ids) {

		$sql = "UPDATE __TABLE__ SET sb_status=?, sb_deleted=? WHERE sb_id IN (?) AND sb_status<?";

		// 条件参数
		$params = array(
			$this->get_st_delete(),
			NOW_TIME,
			$sb_ids,
			$this->get_st_delete()
		);

		return $this->_m->update($sql, $params);
	}

	/**
	 * 数据管理
	 * @param array $params
	 * return array
	 */
	public function data_management($params) {

		$sql = "SELECT SUM(a.sb_amount) AS smb_amount, a.sb_type, b.stp_name FROM  __TABLE__ a LEFT JOIN oa_sales_type  b ON a.`sb_type`=b.stp_id ";

		$where = array("sb_status<?");
		$where_params = array($this->get_st_delete());

		// 年
		if (!empty($params['year'])) {
			$where[] = "sc_created_year = ?";
			$where_params[] = $params['year'];
		}

		// 开始时间
		if (!empty($params['startdate'])) {
			$where[] = "a.sb_created >= ?";
			$where_params[] = $params['startdate'];
		}

		// 结束时间
		if (!empty($params['end_date'])) {
			$where[] = "a.sb_created < ?";
			$where_params[] = $params['end_date'];
		}

		// 结束时间
		if (!empty($params['end_date'])) {
			$where[] = "a.sb_created < ?";
			$where_params[] = $params['end_date'];
		}

		//跟进人id
		if (!empty($params['m_uid'])) {
			$where[] = "a.m_uid in (?)";
			$where_params[] = $params['m_uid'];
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . ' GROUP BY sb_type', $where_params);
	}

	/**
	 * 变更商机负责人
	 * @param int $sb_id 商机id
	 * @param int $m_uid 商机负责人id
	 * @param int $m_username 商机负责人姓名 m_username
	 * @return bool
	 */
	public function change_manager($sb_id, $m_uid, $m_username) {

		// sql语句
		$sql = "UPDATE __TABLE__ SET `m_uid`=? ,`m_username`=? WHERE `sb_id`=? AND sb_status<?";

		// 条件参数
		$sc_params = array(
			$m_uid,
			$m_username,
			$sb_id,
			$this->get_st_delete()
		);

		return $this->_m->update($sql, $sc_params);
	}

	/**
	 * 商机详情
	 * @param int $sb_id 商机id
	 * @return array business_inf:商机信息  manager_inf:负责人信息
	 * $author zhubeihai
	 */
	public function business_detail($sb_id) {

		$sql = "SELECT sb_name, sc_id, m_uid, m_username, sb_amount, sb_type, sb_source, sb_comments, sb_created, sb_updated FROM __TABLE__
		WHERE sb_id=?";
		// 条件参数
		$sc_params = array (
			$sb_id
		);

		return $this->_m->fetch_row($sql, $sc_params);
	}
}
