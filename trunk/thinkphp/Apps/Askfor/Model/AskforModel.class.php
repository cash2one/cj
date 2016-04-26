<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/10
 * Time: 下午3:43
 */
namespace Askfor\Model;

class AskforModel extends AbstractModel {

	const ASKING = 1; // 审批中
	const ASKPASS = 2; // 审核通过
	const TURNASK = 3; // 转审批
	const ASKFAIL = 4; // 审批不通过
	const DRAFT = 5; // 草稿
	const PRESSASK = 6; // 已催办
	const CENCEL = 7; // 已撤销

	const SUBJECT_LENGTH = 15; // 审批主题长度

	const FIXED = 1; // 固定流程
	const FREE = 0; // 自由流程
	const FIXED_ALL_AFTID = -1; // 固定流程不限流程ID

	// 构造方法
	public function __construct() {

		$this->prefield = 'af_';
		parent::__construct();
	}

	/**
	 * 后台列表条件查询
	 * @param array $params 接收参数
	 * @param array $page_option 分页参数
	 * @return array 列表
	 */
	public function cp_list_by_conds($params, $page_option) {

		$sql = "SELECT * FROM __TABLE__";
		$where = array(
			'af_status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($params['s_begin'])) {
			$where[] = 'af_created > ?';
			$where_params[] = rstrtotime($params['s_begin']);
		}
		if (!empty($params['s_end'])) {
			$where[] = 'af_created < ?';
			$where_params[] = rstrtotime($params['s_end']) + 86400;
		}
		if (isset($params['id_aft_type'])) {
			switch ($params['id_aft_type']) {
				case self::FIXED:
					if (!empty($params['id_aft_id']) && $params['id_aft_id'] != self::FIXED_ALL_AFTID) {
						$where[] = 'aft_id = ?';
						$where_params[] = $params['id_aft_id'];
					} else {
						$where[] = 'aft_id != ?';
						$where_params[] = 0;
					}
					break;
				case self::FREE:
					if (!empty($params['id_aft_id']) && $params['id_aft_id'] != self::FIXED_ALL_AFTID) {
						$where[] = 'aft_id = ?';
						$where_params[] = $params['id_aft_id'];
					} else {
						$where[] = 'aft_id = ?';
						$where_params[] = 0;
					}
					break;
				default:
					if (!empty($params['id_aft_id']) && $params['id_aft_id'] != self::FIXED_ALL_AFTID) {
						$where[] = 'aft_id = ?';
						$where_params[] = $params['id_aft_id'];
					}
					break;
			}
		}
//		if (!empty($params['id_aft_id']) && $params['id_aft_id'] != -1) {
//			$where[] = 'aft_id = ?';
//			$where_params[] = $params['id_aft_id'];
//		}
		if (isset($params['ulist'])) {
			$where[] = 'm_uid IN (?)';
			$where_params[] = $params['ulist'];
		}
		if (!empty($params['id_af_status']) && $params['id_af_status'] > 0) {
			$where[] = 'af_condition = ?';
			$where_params[] = $params['id_af_status'];
		}
		if (!empty($params['s_m_username'])) {
			$where[] = 'm_username LIKE ?';
			$where_params[] = '%' . $params['s_m_username'] . '%';
		}
		if (!empty($params['id_af_subject'])) {
			$where[] = 'af_message LIKE ?';
			$where_params[] = '%' . $params['id_af_subject'] . '%';
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('af_created' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);

	}

	/**
	 * 后台列表条件统计
	 * @param array $params 接收参数
	 * @return array 列表
	 */
	public function cp_count_by_conds($params) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";
		$where = array(
			'af_status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($params['s_begin'])) {
			$where[] = 'af_created > ?';
			$where_params[] = rstrtotime($params['s_begin']);
		}
		if (!empty($params['s_end'])) {
			$where[] = 'af_created < ?';
			$where_params[] = rstrtotime($params['s_end']) + 86400;
		}
		if (isset($params['id_aft_type'])) {
			switch ($params['id_aft_type']) {
				case self::FIXED:
					if (!empty($params['id_aft_id']) && $params['id_aft_id'] != self::FIXED_ALL_AFTID) {
						$where[] = 'aft_id = ?';
						$where_params[] = $params['id_aft_id'];
					} else {
						$where[] = 'aft_id != ?';
						$where_params[] = 0;
					}
					break;
				case self::FREE:
					if (!empty($params['id_aft_id']) && $params['id_aft_id'] != self::FIXED_ALL_AFTID) {
						$where[] = 'aft_id = ?';
						$where_params[] = $params['id_aft_id'];
					} else {
						$where[] = 'aft_id = ?';
						$where_params[] = 0;
					}
					break;
				default:
					if (!empty($params['id_aft_id']) && $params['id_aft_id'] != self::FIXED_ALL_AFTID) {
						$where[] = 'aft_id = ?';
						$where_params[] = $params['id_aft_id'];
					}
					break;
			}
		}
		if (isset($params['ulist'])) {
			$where[] = 'm_uid IN (?)';
			$where_params[] = $params['ulist'];
		}
		if (!empty($params['id_af_status']) && $params['id_af_status'] > 0) {
			$where[] = 'af_condition = ?';
			$where_params[] = $params['id_af_status'];
		}
		if (!empty($params['s_m_username'])) {
			$where[] = 'm_username LIKE ?';
			$where_params[] = '%' . $params['s_m_username'] . '%';
		}
		if (!empty($params['id_af_subject'])) {
			$where[] = 'af_message LIKE ?';
			$where_params[] = '%' . $params['id_af_subject'] . '%';
		}

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 连proc表查询 获取 我收到的 审批列表
	 * @param int $m_uid 操作人
	 * @param string $afp_condition 状态
	 * @param int $is_active 是否当前人操作
	 * @param array $page_option 分页参数
	 * @param string $askfor_condition 审批主状态
	 * @return mixed
	 */
	public function left_join_proc($m_uid, $afp_condition, $is_active, $page_option, $askfor_condition) {

		$sql = "SELECT a.* FROM __TABLE__ AS a LEFT JOIN oa_askfor_proc AS b ON a.af_id = b.af_id WHERE b.m_uid = " . $m_uid . " AND b.afp_condition IN (" . $afp_condition . ") AND a.af_condition IN (" . $askfor_condition . ")";

		if (!empty($is_active)) {
			$sql .= " AND b.is_active = " . $is_active;
			$sql .= " AND a.af_status < " . $this->get_st_delete();
		}

		$sql .= " ORDER BY a.af_id DESC LIMIT " . $page_option['start'] . "," . $page_option['limit'];

		return $this->_m->fetch_array($sql);
	}

	/**
	 * 连proc表查询 获取 我收到的 审批列表     获取总数
	 * @param $m_uid
	 * @param $afp_condition
	 * @param $is_active
	 * @param $askfor_condition
	 * @return array
	 */
	public function count_left_join_proc($m_uid, $afp_condition, $is_active, $askfor_condition) {

		$sql = "SELECT COUNT(*) FROM __TABLE__ AS a LEFT JOIN oa_askfor_proc AS b ON a.af_id = b.af_id WHERE b.m_uid = " . $m_uid . " AND b.afp_condition IN (" . $afp_condition . ") AND a.af_condition IN (" . $askfor_condition . ")";

		if (!empty($is_active)) {
			$sql .= " AND b.is_active = " . $is_active;
			$sql .= " AND a.af_status < " . $this->get_st_delete();
		}

		return $this->_m->result($sql);
	}
}
