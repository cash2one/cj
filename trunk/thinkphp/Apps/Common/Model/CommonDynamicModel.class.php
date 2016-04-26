<?php
/**
 * DynamicModel.class.php
 * $author$
 *
 * 字段值定义 1:点赞 2:评论 3:活动报名 4:发表帖子 5:收藏 6:发起投票 7:参与投票 8:进入微社区 9:浏览帖子 10:活动签到
 */

namespace Common\Model;

class CommonDynamicModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据uid获取所有动态数据
	 *
	 * @param int $uid uid
	 * @return array
	 */
	public function dynamic_by_uid($uid, $page_option, $order_option) {

		// 排序
		$orderby = '';
		if (! $this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (! $this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT id, obj_id, cp_identifier, created, dynamic FROM __TABLE__ WHERE dynamic IN(2,3,4,7,11) AND m_uid=? AND status<?" . "{$orderby}{$limit}";
		// 查询条件
		$params = array($uid, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 查询用户关联动态列表
	 *
	 * @param $uids
	 * @param $page_option
	 * @param $order_option
	 * @return array|bool
	 */
	public function dynamic_all_by_uid($uids, $page_option, $order_option) {

		// 排序
		$orderby = '';
		if (! $this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (! $this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT id, obj_id, m_uid, m_username, cp_identifier, created, dynamic FROM __TABLE__ WHERE dynamic IN(2,3,4,7,11) AND m_uid IN ($uids) AND status<?" . "{$orderby}{$limit}";
		// 查询条件
		$params = array($this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据uid获取收藏数据
	 *
	 * @param int $uid uid
	 * @return array
	 */
	public function get_by_uid($uid, $page_option, $order_option) {

		// 排序
		$orderby = '';
		if (! $this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 分页参数
		$limit = '';
		if (! $this->_limit($limit, $page_option)) {
			return false;
		}
		$sql = "SELECT id, obj_id, m_uid, cp_identifier, created FROM __TABLE__ WHERE m_uid=? AND dynamic = 5 AND status<?" . "{$orderby}{$limit}";
		// 查询条件
		$params = array($uid, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据uid获取收藏总数
	 *
	 * @param int $uid uid
	 * @return array
	 */
	public function total_by_uid($uid) {

		$sql = "SELECT id, obj_id, cp_identifier, created FROM __TABLE__ WHERE m_uid=? AND dynamic=5 AND status<?";
		// 查询条件
		$params = array($uid, $this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据uid获取动态总数
	 *
	 * @param int $uid uid
	 * @return array
	 */
	public function totals_by_uid($uid) {

		$sql = "SELECT COUNT(*) AS total FROM __TABLE__ WHERE m_uid=?  AND dynamic IN(2,3,4,7,11) AND status<?";
		// 查询条件
		$params = array($uid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 根据data获取收藏数据
	 *
	 * @param int $uid uid
	 * @return array
	 */
	public function get_by_data($data) {

		$sql = "SELECT id FROM __TABLE__ WHERE m_uid=? AND obj_id=? AND cp_identifier=? AND dynamic=? AND status<?";
		// 查询条件
		$params = array($data['m_uid'], $data['obj_id'], $data['cp_identifier'], $data['dynamic'], $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 查询当天某用户某操作的积分总数
	 *
	 * @param $uid
	 * @param $dynamic
	 * @return array
	 */
	public function get_day_data_by_uid($uid, $dynamic) {

		$sql = "select sum(score) as nums from __TABLE__ where TO_DAYS(from_unixtime(created)) = TO_DAYS(now()) AND `m_uid` = ? AND `dynamic`=? group by m_uid";
		$params = array($uid, $dynamic);

		return $this->_m->fetch_row($sql, $params);
	}

	public function get_day_view_data_by_conds($conds) {

		$params = array();
		// 条件
		$wheres = array();
		if (! $this->_parse_where($wheres, $params, $conds)) {
			return false;
		}
		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		// 执行 SQL
		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE TO_DAYS(from_unixtime(created)) = TO_DAYS(now()) AND " . implode(' AND ', $wheres), $params);
	}

	/**
	 * 查询本周的某用户某操作的积分总数
	 *
	 * @param $uid
	 * @param $dynamic
	 * @return array
	 */
	public function get_week_data_by_uid($uid, $dynamic) {

		$sql = "select sum(score) as nums from __TABLE__ where YEARWEEK(from_unixtime(created)) = YEARWEEK(now()) AND `m_uid` = ? AND `dynamic`=? group by m_uid";
		$params = array($uid, $dynamic);

		return $this->_m->fetch_row($sql, $params);
	}

	public function get_real_by_conds($conds) {

		$params = array();
		// 条件
		$wheres = array();
		if (! $this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 执行 SQL
		return $this->_m->fetch_row("SELECT score FROM __TABLE__ WHERE " . implode(' AND ', $wheres), $params);
	}

	/**
	 * 统计用户操作数
	 *
	 * @param $identifier 对象
	 * @param $dynamic 操作
	 * @return array
	 */
	public function get_active_group_uid($identifier = '', $dynamic = '') {

		$wheres = array();
		if ($identifier) {
			$wheres[] = "`cp_identifier` = ?";
			$params = array($identifier, $this->get_st_delete());
		}
		if ($dynamic) {
			$wheres[] = "`dynamic`= ?";
			$params = array($dynamic, $this->get_st_delete());
		}
		$wheres[] = "status<?";
		$sql = "SELECT COUNT(*) as count, m_uid FROM __TABLE__ WHERE " . implode(' AND ', $wheres) . " GROUP BY m_uid";

		if ($identifier && $dynamic) {
			$params = array($identifier, $dynamic, $this->get_st_delete());
		}

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 统计用户活跃度
	 *
	 * @return array
	 */
	public function get_sum_group_uid() {

		$sql = "SELECT SUM(score) as score, m_uid FROM __TABLE__ GROUP BY m_uid";
		$params = array();

		return $this->_m->fetch_array($sql, $params);
	}

}
