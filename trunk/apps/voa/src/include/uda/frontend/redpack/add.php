<?php
/**
 * add.php
 * 新增红包信息
 * $Author$
 * $Id$
 */
class voa_uda_frontend_redpack_add extends voa_uda_frontend_redpack_abstract {
	// 请求的参数
	private $__in = array();
	// 返回的结果集
	private $__out = array();
	// 其他扩展参数
	private $__options = array();

	/**
	 * 获取指定红包的信息
	 *
	 * @param array $in 请求的参数
	 *  + redpack_id 红包id
	 * @param array $out 返回的结果集，见redpack表字段
	 * @param array $options 其他扩展参数
	 * @return boolean
	 */
	public function doit(array $in, array &$out = array(), array $options = array()) {

		// 请求规则定义
		$fields = array(
			'cd_ids' => array('cd_ids', parent::VAR_STR, 'chk_cd_ids', null, false),
			'm_uids' => array('m_uids', parent::VAR_STR, 'chk_uids', null, false),
			'total' => array('total', parent::VAR_INT, null, null, false),
			'count' => array('count', parent::VAR_INT, null, null, false),
			'wishing' => array('wishing', parent::VAR_STR, 'chk_wishing', null, false),
			'type' => array('type', parent::VAR_INT, null, null, false),
			'remark' => array('remark', parent::VAR_STR, null, null, false),
			'uid' => array('uid', parent::VAR_STR, null, null, false),
			'username' => array('username', parent::VAR_STR, null, null, false),
			'actname' => array('actname', parent::VAR_STR, null, null, false),
			'starttime' => array('starttime', parent::VAR_STR, null, null, false),
			'endtime' => array('endtime', parent::VAR_STR, null, null, false),
			'min' => array('min', parent::VAR_INT, null, null, false),
			'max' => array('max', parent::VAR_INT, null, null, false)
		);
		if (! $this->extract_field($this->__in, $fields, $in)) {
			return false;
		}

		// 计算人数
		$count = (int)$this->__in['count'];
		if (0 >= $count) {
			$serv_m = &service::factory('voa_s_oa_member');
			if (empty($this->__in['cd_ids']) && empty($this->__in['m_uids'])) {
				$this->__in['m_uids'] = array(0);
				$count = $serv_m->count_all();
			} else {
				$count = $serv_m->count_by_cdids_uids($this->__in['cd_ids'], $this->__in['m_uids']);
			}

			if (0 >= $count) {
				return voa_h_func::throw_errmsg('400:请选择红包发送对象');
			}
		} else {
			$this->__in['m_uids'] = array(0);
		}

		// 红包类型
		$type = $this->__in['type'];
		$types = array(
			voa_d_oa_redpack::TYPE_RAND,
			voa_d_oa_redpack::TYPE_APPOINT,
			voa_d_oa_redpack::TYPE_AVERAGE,
			voa_d_oa_redpack::TYPE_FREE
		);
		if (empty($type) || !in_array($type, $types)) {
			$type = voa_d_oa_redpack::TYPE_RAND;
		}

		// 判断金额是否足够
		$total = 0;
		if (!$this->_get_total($total, $type, $count)) {
			return voa_h_func::throw_errmsg('400:红包金额错误');
		}

		// 最大最小金额
		$min = empty($this->__in['redpack_min']) ? $this->_p_sets['redpack_min'] : $this->__in['redpack_min'];
		$max = empty($this->__in['redpack_max']) ? $this->_p_sets['redpack_max'] : $this->__in['redpack_max'];

		$rule = '';
		$highest = 0;
		// 如果金额大于红包金额的最大值, 则自动转为均分红包
		if ($total > $count * $this->_p_sets['redpack_max']) {
			$total = $count * $this->_p_sets['redpack_max'];
			$type = voa_d_oa_redpack::TYPE_AVERAGE;
		} else if ($total == $count * $this->_p_sets['redpack_min']) {
			$type = voa_d_oa_redpack::TYPE_AVERAGE;
		}

		// 如果是随机红包, 则进行红包预分配
		if (voa_d_oa_redpack::TYPE_RAND == $type) {
			$rule = voa_h_redpack::get_bonus($total, $count, $max, $min);
			foreach ($rule as $_val) {
				if ($highest < $_val) {
					$highest = $_val;
				}
			}
		}

		// 红包消息入库
		$redpack = $this->_serv_rp->insert(array(
			'm_uid' => $this->__in['uid'],
			'm_username' => $this->__in['username'],
			'wishing' => empty($this->__in['wishing']) ? '恭喜发财, 大吉大利' : $this->__in['wishing'],
			'actname' => $this->__in['actname'],
			'nickname' => $this->_p_sets['default_sender_name'],
			'sendname' => $this->_p_sets['default_sender_name'],
			'total' => $total,
			'left' => 0,
			'redpacks' => $count,
			'remark' => $this->__in['remark'],
			'type' => $type,
			'min' => $min,
			'max' => $max,
			'highest' => $highest,
			'rule' => empty($rule) ? '' : serialize($rule)
		));

		// 部门权限信息入库
		$this->_insert_rp_department($redpack['id']);
		// 用户权限信息入库
		$this->_insert_rp_mem($redpack['id']);

		$out = $redpack;
		$out['_cd_ids'] = $this->__in['cd_ids'];
		$out['_m_uids'] = $this->__in['m_uids'];
		return true;
	}

	/**
	 * 红包用户权限入库
	 * @param int $redpack_id 红包id
	 * @return boolean
	 */
	protected function _insert_rp_mem($redpack_id) {

		if (empty($this->__in['m_uids'])) {
			return true;
		}

		$serv_rp_mem = &service::factory('voa_s_oa_redpack_mem');
		$rp_mems = array();
		foreach ($this->__in['m_uids'] as $_uid) {
			$rp_mems[] = array(
				'redpack_id' => $redpack_id,
				'm_uid' => $_uid
			);
		}

		$serv_rp_mem->insert_multi($rp_mems);
		return true;
	}

	/**
	 * 红包部门权限入库
	 * @param int $redpack_id 红包id
	 * @return boolean
	 */
	protected function _insert_rp_department($redpack_id) {

		if (empty($this->__in['cd_ids'])) {
			return true;
		}

		$serv_rp_dp = &service::factory('voa_s_oa_redpack_department');
		$rp_dps = array();
		foreach ($this->__in['cd_ids'] as $_id) {
			$rp_dps[] = array(
				'redpack_id' => $redpack_id,
				'cd_id' => $_id
			);
		}

		$serv_rp_dp->insert_multi($rp_dps);
		return true;
	}

	/**
	 * 获取红包总金额
	 * @param int $total 总金额
	 * @param int $type 红包类型
	 * @param int $count 总数
	 * @return boolean
	 */
	protected function _get_total(&$total, $type, $count) {

		$total = $this->__in['total'] * 100;
		// 如果不是随机红包, 则重新计算总金额
		if (voa_d_oa_redpack::TYPE_RAND != $type) {
			$total = $count * $total;
			return true;
		}

		// 如果金额小于可以发得最小金额
		if ($total < $count * $this->_p_sets['redpack_min']) {
			voa_h_func::throw_errmsg('400:金额不足, 最少需要' . number_format($count * $this->_p_sets['redpack_min'] / 100, 2) . '元');
			return false;
		}

		return true;
	}

	// 检查祝福语
	public function chk_wishing(&$wishing, $err = '') {

		if (0 > strlen($wishing)) {
			$wishing = '恭喜发财, 大吉大利';
		}

		return true;
	}

	/**
	 * 检查uid是否正确
	 * @param string $uids 用户uid
	 * @param string $err
	 * @return boolean
	 */
	public function chk_uids(&$uids, $err = '') {

		$uids = explode(',', $uids);
		$serv_m = &service::factory('voa_s_oa_member');
		$users = $serv_m->fetch_all_by_ids($uids);
		$uids = array_keys($users);
		return true;
	}

	/**
	 * 检查部门id是否正确
	 * @param string $cd_ids 部门id
	 * @param string $err
	 * @return boolean
	 */
	public function chk_cd_ids(&$cd_ids, $err = '') {

		$cd_ids = explode(',', $cd_ids);
		$dps = voa_h_cache::get_instance()->get('department', 'oa');
		$ids = array();
		foreach ($cd_ids as $_id) {
			if (isset($dps[$_id])) {
				$ids[] = $_id;
			}
		}

		$cd_ids = $ids;
		return true;
	}

}
