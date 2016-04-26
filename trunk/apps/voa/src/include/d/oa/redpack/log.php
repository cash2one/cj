<?php
/**
 * log.php
 * 红包领取日志表
 * $Author$
 * $Id$
 */

class voa_d_oa_redpack_log extends voa_d_abstruct {
	// 未发送
	const SEND_ST_NO = 0;
	// 已发送
	const SEND_ST_YES = 1;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.redpack_log';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'id';
		// 字段前缀
		$this->_prefield = '';
		parent::__construct();
	}

	/**
	 * 根据uid读取日志列表
	 * @param int $uid 用户uid
	 */
	public function list_by_uid($uid, $page_option = null, $orderby = array()) {

		try {
			$this->_condi('m_uid=?', $uid);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据redpack_id读取日志列表
	 * @param int $uid 用户uid
	 */
	public function list_by_redpackid($redpack_id, $page_option = null, $orderby = array()) {

		try {
			$this->_condi('redpack_id=?', $redpack_id);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据用户 openid 和 redpack_id 读取红包日志;
	 * @param string $openid 微信 openid
	 * @param int $redpackid 红包id
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function fetch_by_openid_redpackid($openid, $redpackid) {

		try {
			// 条件
			$this->_parse_conds(array('openid=?' => $openid, 'redpack_id=?' => $redpackid));
			$this->_condi($this->_prefield.'sendst=?', self::SEND_ST_NO);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_find_row();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 指定红包活动被领取的金额数
	 *
	 * @param number $redpack_id
	 * @return number
	 */
	public function total_money_by_redpack_id($redpack_id) {

		if (! is_numeric($redpack_id)) {
			return 0;
		}

		$sql = "SELECT SUM(`money`) FROM `{$this->_table}` WHERE `redpack_id`={$redpack_id} AND `status`<" . self::STATUS_DELETE;
		$total = $this->_getOne($sql);
		if (! is_numeric($total)) {
			$total = 0;
		}

		return $total;
	}

	/**
	 * 计算指定红包满足条件的人员领取次数
	 *
	 * @param number $redpack_id 指定红包活动ID
	 * @param number $m_uid 领取人的uid
	 * @param string $openid 领取人的openid
	 * @return number
	 */
	public function count_got_total($redpack_id, $openid) {

		$total = 0;
		// 构造真实的查询条件
		$where = array();
		$where[] = "`redpack_id`={$redpack_id}";
		if (is_scalar($openid) && raddslashes($openid) == $openid) {
			$where[] = "`openid`='{$openid}'";
		}

		$where[] = "`status`<" . self::STATUS_DELETE;
		// 数据查询统计
		$sql = "SELECT COUNT(`id`) FROM `{$this->_table}` WHERE " . implode(" AND ", $where);
		$total = $this->_getOne($sql);
		if (! is_numeric($total)) {
			return 0;
		}

		return $total;
	}

	/**
	 * 计算指定红包活动的领取总数
	 *
	 * @param number $redpack_id
	 * @return number
	 */
	public function count_by_redpack_id($redpack_id = 0) {

		$conds = array(
			'redpack_id' => $redpack_id
		);
		return (int)$this->count_by_conds($conds);
	}

}
