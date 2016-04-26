<?php
/**
 * EnterpriseProfileModel.class.php
 * $author$
 */

namespace Common\Model;

class EnterpriseProfileModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'ep_';
	}

	/**
	 * 根据 corpid 列出所有企业信息
	 * @param string $corpid 企业 corpid
	 * @param array $orders 排序数组
	 * @return boolean|Ambigous <multitype:, unknown>
	 */
	public function list_by_wxcorpid($corpid, $orders = array()) {

		// 设置条件
		$orderby = '';
		if (!$this->_order_by($orderby, $orders)) {
			return false;
		}

		// 状态查询条件
		$params = array($corpid, $this->get_st_delete());
		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `ep_wxcorpid`=? AND `{$this->prefield}status`<?", $params);
	}

	/**
	 * 根据域名更新企业信息
	 * @param string $domain 域名信息
	 * @param array $enterprise 企业信息
	 */
	public function update_by_domain($domain, $enterprise) {

		// 更新时 SET 数据
		$sets = array();
		$params = array();
		if (!$this->_parse_set($sets, $params, $enterprise)) {
			return false;
		}

		$wheres = array('ep_domain=?');
		$params[] = $domain;
		return $this->_m->execsql('UPDATE __TABLE__ SET ' . implode(',', $sets) . ' WHERE ' . implode(' AND ', $wheres), $params);
	}

	/**
	 * 根据 ep_id 和 domain 更新企业信息
	 * @param int $ep_id 企业ID
	 * @param string $domain 域名
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id_notin_domain($ep_id, $domain, $enterprise) {

		// 更新时 SET 数据
		$sets = array();
		$params = array();
		if (!$this->_parse_set($sets, $params, $enterprise)) {
			return false;
		}

		$wheres = array('ep_id=? AND ep_domain!=?');
		$params[] = $ep_id;
		$params[] = $domain;
		return $this->_m->execsql('UPDATE __TABLE__ SET ' . implode(',', $sets) . ' WHERE ' . implode(' AND ', $wheres), $params);
	}

	/**
	 * 根据 corpid 读取企业信息
	 * @param string $corpid 企业 corpid
	 * @return boolean|Ambigous <multitype:, unknown>
	 */
	public function get_by_wxcorpid($corpid) {

		// 状态查询条件
		$params = array($corpid, $this->get_st_delete());
		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `ep_wxcorpid`=? AND `{$this->prefield}status`<?", $params);
	}

	/**
	 * 统计一天的新增的公司
	 * @return array
	 */
	public function count_new_company() {

		$where = array(
			'ep_status < ?',
			'ep_created > ?',
			'ep_created < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}

	/**
	 * 统计昨天负责人变更数据
	 * @return array
	 */
	public function list_by_conds_connect() {

		$where = array(
			'ep_status < ?',
			'new_time > ?',
			'new_time < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86400,
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}


	/**
	 * 统计每日公司总数
	 * @return array
	 */
	public function count_all_company() {

		$where = array(
			'ep_status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);

		return $this->_m->result("SELECT count(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 获取指定日期的新增公司
	 * @param $date string 日期
	 *  + s_time string 开始日期
	 *  + e_time string 结束日期
	 */
	public function list_by_date($date, $page_option) {

		$where = array(
			'ep_status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);

		if (!empty($date['s_time'])) {
			$where[] = 'ep_created > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}
		if (!empty($date['e_time'])) {
			$where[] = 'ep_created <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('ep_created' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);

	}

	/**
	 * 获取指定日期的新增公司数量
	 * @param $date string 日期
	 *  + s_time string 开始日期
	 *  + e_time string 结束日期
	 */
	public function count_by_date($date) {

		$where = array(
			'ep_status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);

		if (!empty($date['s_time'])) {
			$where[] = 'ep_created > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}
		if (!empty($date['e_time'])) {
			$where[] = 'ep_created <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}
		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);

	}


	/**
	 * 根据负责人id查询公司
	 * @param $ca_id int 负责人id
	 * @return array
	 */
	public function list_by_caid($ca_id) {

		$where = array(
			'ep_status < ?',
			'ca_id = ?',
		);
		$where_params = array(
			$this->get_st_delete(),
			$ca_id,
		);

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 查询公司信息
	 * @param array $ep_list 公司id
	 * @param array $date 日期
	 * @param array $page_option 分页参数
	 * @return array|bool
	 */
	public function list_pay_company_info($ep_list, $date, $page_option = array()) {

		$where = array(
			'a.ep_status < ?',
			'a.ep_id IN (?)',
		);
		$where_params = array(
			$this->get_st_delete(),
			$ep_list,
		);
		if (!empty($date['s_time'])) {
			$where[] = 'b.created > ?';
			$where_params[] = rstrtotime($date['s_time']);
		}
		if (!empty($date['e_time'])) {
			$where[] = 'b.created <= ?';
			$where_params[] = rstrtotime($date['e_time']) + 86400;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('ep_created' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array("SELECT a.*, b.created FROM __TABLE__ AS a LEFT JOIN `cy_company_paysetting` AS b ON a.ep_id=b.ep_id WHERE " . implode(' AND ', $where)." group by b.ep_id {$orderby}{$limit}", $where_params);
	}

	/**
	 * 统计负责人公司数量
	 * @param $date array 日期
	 * @param $ep_id array 公司id
	 * @return array
	 */
	public function count_add_company_epid($date, $ep_id) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";
		// 设置条件
		$where = array(
			'ep_created > ?',
			'ep_created <= ?',
			'ep_status < ?',
			'ep_id IN (?)'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']),
			$this->get_st_delete(),
			$ep_id
		);

		return $this->_m->result($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 统计负责人公司数量
	 * @param $date array 日期
	 * @param $ep_id array 公司id
	 * @return array
	 */
	public function list_add_company_epid($date, $ep_id) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'ep_created > ?',
			'ep_created <= ?',
			'ep_status < ?',
			'ep_id IN (?)'
		);

		$where_params = array(
			rstrtotime($date['s_time']),
			rstrtotime($date['e_time']),
			$this->get_st_delete(),
			$ep_id
		);

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 查询没有负责人的公司
	 * @param $ep_list
	 * @return array
	 */
	public function list_by_no_adminer($ep_list) {

		$sql = "SELECT * FROM __TABLE__";
		// 设置条件
		$where = array(
			'ep_status < ?',
			'ep_id IN (?)',
			'ca_id = ?'
		);

		$where_params = array(
			$this->get_st_delete(),
			$ep_list,
			0,
		);

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $where), $where_params);
	}

	/**
	 * 更新corpid
	 * @param string $domain 域名
	 * @param string $corpid 当前的corpid
	 * @param string $new_corpid 新corpid
	 */
	public function update_corpid_not_in_domain($domain, $corpid, $new_corpid) {

		return $this->_m->execsql('UPDATE __TABLE__ SET `ep_wxcorpid`=? WHERE `ep_wxcorpid`=? AND `ep_domain`!=? AND `ep_status`<?', array(
			$new_corpid, $corpid, $domain, $this->get_st_delete()
		));
	}

}
