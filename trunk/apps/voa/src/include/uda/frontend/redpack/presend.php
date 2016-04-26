<?php
/**
 * presend.php
 * 领取红包
 * $Author$
 * $Id$
 */

class voa_uda_frontend_redpack_presend extends voa_uda_frontend_redpack_abstract {
	// 请求的参数
	private $__in = array();
	// 返回的结果集
	private $__out = array();
	// 其他配置项
	private $__options = array();
	// 当前红包信息
	private $__redpack = array();
	// 剩余可发放的红包金额总数
	private $__remainder_money = 0;
	// 当前人员领取红包的总次数
	private $__got_count = 0;
	// 当前领取日志信息
	private $__rplog = 0;
	// 当前人员可领取的红包金额
	private $__money = 0;
	// 队列
	private $__queue = null;

	/**
	 * 领取红包
	 *
	 * @param array $in 请求的参数
	 *        + redpack_id 红包活动ID
	 *        + uid 红包接受者uid（可以为0，此时此人非企业号内部成员）
	 *        + openid 红包接受者的微信openid
	 * @param array $out 返回的结果集
	 *        + redpack_id
	 *        + uid
	 *        + openid
	 *        + ip
	 *        + redpack 红包信息
	 *        + money 当前接受者获得的金额，单位：分
	 *        + result 微信方返回的发放结果集
	 *        + mch_billno 订单号
	 *        + mch_id 商户号
	 *        + wxappid 公众账号appid
	 *        + re_openid 用户openid
	 *        + total_amount 付款金额，单位：分
	 * @param unknown $options 其他用于扩展的额外参数
	 */
	public function doit(array $in, array &$out = array(), $options = array()) {

		// 请求规则定义
		$fields = array(
			'redpack_id' => array('redpack_id', parent::VAR_INT, null, null, false),
			'uid' => array('uid', parent::VAR_INT, null, null, false),
			'username' => array('username', parent::VAR_STR, null, null, false),
			'openid' => array('openid', parent::VAR_STR, null, null, false),
			'money' => array('money', parent::VAR_INT, null, null, false)
		);
		if (! $this->extract_field($this->__in, $fields, $in)) {
			return false;
		}

		// 获取当前红包
		if (!$this->__redpack = $this->_serv_rp->get($this->__in['redpack_id'])) {
			return voa_h_func::throw_errmsg('400:该红包不存在');
		}

		// 未开始
		if (! $this->_serv_rp->is_start($this->__redpack)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_redpack::NO_START);
		}

		// 已结束
		if ($this->_serv_rp->is_end($this->__redpack)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_redpack::IS_END, rhtmlspecialchars($this->__redpack['actname']));
		}

		// 红包已派发完毕
		if (0 < $this->__redpack['redpacks'] && $this->__redpack['times'] >= $this->__redpack['redpacks']) {
			return voa_h_func::throw_errmsg('400:红包已派发完毕');
		}

		// 判断是否有权限领取红包
		if (0 < $this->__redpack['redpacks'] && !$this->has_privilege($this->__in['redpack_id'], $this->__in['uid'])) {
			return voa_h_func::throw_errmsg('400:您没有权限领取红包');
		}

		// 所有红包信息
		if (empty($this->__redpack['rule'])) {
			$rule = array();
		} else {
			$rule = unserialize($this->__redpack['rule']);
		}

		// 初始化队列
// 		$this->__queue = new voa_memcache_queue('queue');
		// 加入队列失败
// 		if (!$this->__queue->add($this->__in['uid'])) {
// 			return voa_h_func::throw_errmsg('500:服务器繁忙');
// 		}

		try {
			// 事务开始
			$this->_serv_rp->begin();
			// 利用 mysql innodb 的行锁
			$redpack_up = $this->_serv_rp->get_for_update($this->__in['redpack_id']);
			// 重新判断红包是否已派发完毕
			if (0 < $this->__redpack['redpacks'] && $redpack_up['times'] >= $redpack_up['redpacks']) {
				return voa_h_func::throw_errmsg('400:红包已派发完毕');
			}

			// 读取红包记录
			if (voa_d_oa_redpack::TYPE_FREE != $this->__redpack['type'] && 0 < $this->_serv_rplog->count_got_total($redpack_up['id'], $this->__in['openid'])) {
				return voa_h_func::throw_errmsg('400:您已领取过该红包, 不能重复领取');
			}

			// 获取红包金额
			if (voa_d_oa_redpack::TYPE_FREE == $this->__redpack['type'] && 0 < $this->__in['money']) {
				$this->__money = $this->__in['money'];
				if ($redpack_up['min'] > $this->__money || $redpack_up['max'] < $this->__money) {
					return voa_h_func::throw_errmsg('400:红包金额错误');
				}
			} else {
				if (empty($rule)) {
					if (0 < $redpack_up['total']) {
						$this->__money = $redpack_up['total'] / $redpack_up['redpacks'];
					} else {
						$this->__money = rand($redpack_up['min'], $redpack_up['max']);
					}
				} else {
					$this->__money = $rule[$redpack_up['times']];
				}
			}

			$this->__money = (int)$this->__money;
			// 更新红包信息
			$this->_serv_rp->update_left_times($redpack_up['id'], $this->__money);

			// 事务结束
			$this->_serv_rp->commit();
		} catch (help_exception $e) {
			$this->_serv_rp->rollback();
			// 退出队列
// 			$this->__queue->get(1);
			return voa_h_func::throw_errmsg($e->getCode().':'.$e->getMessage());
		} catch (Exception $e) {
			$this->_serv_rp->rollback();
			// 退出队列
// 			$this->__queue->get(1);
			return voa_h_func::throw_errmsg('500:服务器繁忙');
			return false;
		}

		// 退出队列
// 		$this->__queue->get(1);

		// 当前人员的当前状态
		$rplog = array(
			'redpack_id' => $this->__in['redpack_id'],
			'm_uid' => $this->__in['uid'],
			'm_username' => $this->__in['username'],
			'openid' => $this->__in['openid'],
			'money' => $this->__money,
			'ip' => controller_request::get_instance()->get_client_ip(),
			'appid' => $this->_p_sets['wxappid'],
			'mch_billno' => voa_h_redpack::billno($this->_sets['mchid']),
			'result' => '',
			'sendst' => voa_d_oa_redpack_log::SEND_ST_NO
		);

		// 领取日志入库
		$this->__rplog = $this->_serv_rplog->insert($rplog);
		$out = $this->__rplog;

		// 取红包日志
		$cur_year = rgmdate(startup_env::get('timestamp'), 'Y');
		$years = array(0, $cur_year);
		$serv_year = &service::factory('voa_s_oa_redpack_total');
		$redpack_total = array();
		if (!$redpack_total = $serv_year->list_by_uid_year($this->__in['uid'], $years)) {
			$redpack_total = array();
		}

		// 整理数据, 下标改成年份
		$year2rp = array();
		foreach ($redpack_total as $_rp) {
			$year2rp[$_rp['year']] = $_rp;
		}

		// 更新总数据
		$this->update_total_by_year($year2rp, 0);
		$this->update_total_by_year($year2rp, $cur_year);

		return true;
	}

	/**
	 * 根据年份更新统计数据
	 * @param array $year2rp 年份和红包对照表
	 * @param int $year 年份
	 * @return boolean
	 */
	public function update_total_by_year($year2rp, $year) {

		$serv_year = &service::factory('voa_s_oa_redpack_total');
		if (isset($year2rp[$year])) {
			$data = array(
				'`money`=`money`+?' => $this->__money,
				'`rp_count`=`rp_count`+?' => 1
			);
			if ($this->__redpack['highest'] == $this->__money) {
				$data['`highest_count`=`highest_count`+?'] = 1;
			}

			$serv_year->update($year2rp[$year]['id'], $data);
		} else {
			$data = array(
				'm_uid' => $this->__in['uid'],
				'm_username' => $this->__in['username'],
				'year' => $year,
				'money' => $this->__money,
				'rp_count' => 1
			);
			if ($this->__redpack['highest'] == $this->__money) {
				$data['highest_count'] = 1;
			}

			$serv_year->insert($data);
		}

		return true;
	}

}
