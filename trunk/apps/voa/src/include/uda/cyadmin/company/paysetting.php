<?php

/**
 * voa_uda_cyadmin_company_paysetting
 * 付费设置接口
 * Created by zhoutao.
 * Created Time: 2015/7/31  10:24
 */
class voa_uda_cyadmin_company_paysetting extends voa_uda_cyadmin_base {

	const STANDARD = 1; // 标准产品
	const CUSTOMIZATION = 2; // 定制产品
	const PRIVATEDEP = 3; // 私有部署
	const PAID = 1; // 已付费类型

	private $__request = '';

	protected $_serv_profile = null;
	protected $_serv_paysetting = null;

	/**
	 * 确认数据的合法性和正确性,并入库
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function take_sure($in, &$out) {

		$data = '';
		$this->_serv_paysetting = &service::factory('voa_s_cyadmin_company_paysetting');
		$this->_serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');

		// 初始化RPC
		$this->_rpc_domain($in['ep_id']);

		// pay_type 为 1时 是标准产品 2 和3  为定制产品 和 私有部署  1和23两种处理方式
		if ($in['pay_type'] == self::STANDARD) {

			// 为1时 做的操作
			if (!$this->__if_pay_type_one($in, $out, $data)) {
				return false;
			};

		} else {

			if (!$this->__if_pay_type_two_or_three($in, $out, $data)) {
				return false;
			};

		}

		// 更新enterprise_profile库 (企业用户的所有金额)
		$serv_data = $this->_serv_profile->get_by_conds(array('ep_id' => $data['ep_id']));
		$ep_money = $data['ep_money'] + $serv_data['ep_money'];
		if ($data['pay_type'] == 1) {
			// 更新数据
			$this->_serv_profile->update_by_conds(array(
				'ep_id' => $data['ep_id'],
			), array(
					'pay_type' => $data['pay_type'],
					'ep_money' => $ep_money,
				));
		} else {
			$this->_serv_profile->update_by_conds(array(
				'ep_id' => $data['ep_id'],
			), array(
					'pay_type' => $data['pay_type'],
					'ep_money' => $ep_money,
				));
		}

		// 如果有代理商,那么累加代理商的金额
		if (!empty($serv_data['ep_agent'])) {
			$agent = &service::factory('voa_s_cyadmin_enterprise_account');
			$agent_data = $agent->get_by_conds(array('acid' => $serv_data['ep_agent']));
			$agent_money = $agent_data['money'] + $data['ep_money'];
			$agent->update_by_conds(array('acid' => $serv_data['ep_agent']), array('money' => (int)$agent_money));
		}

		return true;
	}

	/**
	 * 当pay_type为1时做的操作
	 * @param $in 输入数据
	 * @param $out 报错信息
	 * @return bool
	 */
	private function __if_pay_type_one($in, &$out, &$data) {

		// 查询当前是否为定制产品 或者 私有部署,不允许购买标准产品
		$buy_list = $this->_serv_paysetting->get_by_conds(array(
			'ep_id' => $in['ep_id'],
			'cpg_id' => $in['cpg_id']
		));
		if ($buy_list['pay_type'] == self::CUSTOMIZATION || $buy_list['pay_type'] == self::PRIVATEDEP) {
			$out = array(
				'errcode' => '5000',
				'errmsg' => '定制产品或者私有部署套件,不能再去购买标准产品',
			);

			return false;
		}

		// 验证数据
		if (!$this->_filter($in, $data)) {
			$out = array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg,
			);

			return false;
		}

		// 查询是否之前有购买
		$had_pay = $this->_serv_paysetting->list_by_conds(array(
			'ep_id' => $data['ep_id'],
			'cpg_id' => $data['cpg_id'],
		));

		// 如果不为空,那么就是有购买,直接延长截止日期
		if (!empty ($had_pay)) {
			foreach ($had_pay as $k => $v) {
				// 判断是否已经被停用
				if ($v['stop_status'] == 1) {
					$out = array(
						'errcode' => '80000',
						'errmsg' => '已经被停用,得先开启',
					);

					return false;
				}
				// 判断新的截止时间是否 大于之前的截止时间
				if ($data['date_end'] < $v['date_end']) {
					$out = array(
						'errcode' => '40000',
						'errmsg' => '新增截止时间不得小于之前的截止时间',
					);

					return false;
				}
				$standard_money = $v['ep_money'];
			}
			// 更新paysetting表数据,延长时间 和 增加上提交的金额
			$this->_serv_paysetting->update_by_conds(array(
				'ep_id' => $data['ep_id'],
				'cpg_id' => $data['cpg_id'],
			), array(
				'date_end' => $data['date_end'],
				'ep_money' => $data['ep_money'] + $standard_money,
				'pay_status' => self::PAID
			));

			/** 标准产品 延长期限(RPC) */
			$this->_rpc->update_cpg($data['cpg_id'], array('date_end' => $data['date_end'], 'pay_status' => self::PAID));
		} else {
			// 如果没有那么直接 入company_paysetting库(新增)
			$data['pay_status'] = self::PAID;
			$this->_serv_paysetting->insert($data);

			/** 标准产品 新增(RPC) */
			$rpc_data = array(
				'pay_type' => $data['pay_type'],
				'date_start' => $data['date_start'],
				'date_end' => $data['date_end'],
				'pay_status' => self::PAID
			);
			$this->_rpc->update_cpg($data['cpg_id'], $rpc_data);
		}

		// 存入记录表
		$standard_serv = &service::factory('voa_s_cyadmin_company_paysetting_standard');
		$standard_data = array(
			'ep_id' => $data['ep_id'],
			'cpg_id' => $data['cpg_id'],
			'pay_type' => $data['pay_type'],
			'pay_status' => (int)1,
			'ep_money' => $data['ep_money'],
			'date_start' => $data['date_start'],
			'date_end' => $data['date_end'],
		);
		$standard_serv->insert($standard_data);

		return true;
	}

	/**
	 * 为2 或者 3时的操作
	 * @param $in 输入数据
	 * @param $out 错误信息
	 * @param $data 返回数据
	 * @return bool
	 */
	private function __if_pay_type_two_or_three($in, &$out, &$data) {

		if (!$this->_filter1($in, $data)) {
			$out = array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg,
			);

			return false;
		}

		// 查询是否之前有购买
		$had_pay = $this->_serv_paysetting->list_by_conds(array(
			'ep_id' => $data['ep_id'],
			'cpg_id' => $data['cpg_id']
		));
		// 如果不为空,那么就是有购买,更新时间和累加这次提交的金额
		if (!empty ($had_pay)) {
			$update_data = array(
				'remark' => $data['remark'],
				'operator' => $data['operator'],
			);
			$money_round = array(
				'first_money',
				'second_money',
				'third_money',
				'fourth_money',
				'fifth_money',
				'ep_money',
			);
			foreach ($had_pay as $k => $v) {
				// 判断是否已经被停用
				if ($v['stop_status'] == 1) {
					$out = array(
						'errcode' => '80000',
						'errmsg' => '已经被停用,得先开启',
					);

					return false;
				}
				// 如果之前有金额,先保存之前的金额,如果提交有金额,那么加上之前的一起;
				// else 之前没金额,提交有金额,那么保存提交金额
				for ($i = 0; $i < 6; $i ++) {
					if (isset($v[$money_round[$i]]) && $v[$money_round[$i]] != 0) {
						$update_data[$money_round[$i]] = $v[$money_round[$i]];
						if (isset($data[$money_round[$i]]) && $data[$money_round[$i]] != 0) {
							$update_data[$money_round[$i]] = $data[$money_round[$i]] + $v[$money_round[$i]];
						}
					} elseif (isset($data[$money_round[$i]]) && $data[$money_round[$i]] != 0) {
						$update_data[$money_round[$i]] = $data[$money_round[$i]];
					}
				}
				break;
			}
			$this->_serv_paysetting->update_by_conds(array(
				'ep_id' => $data['ep_id'],
				'pay_type' => $data['pay_type'],
			), $update_data);

			/** 更新企业后台 (RPC) */
			// 如果提交的 type 不等于原来的 type 那么更新
			if ($data['pay_type'] != $had_pay['pay_type']) {
				$this->_rpc->update_cpg($data['cpg_id'], array('pay_type' => $data['pay_type'], 'pay_status' => self::PAID));
			}
		} else {
			// 如果没有那么直接 入company_paysetting库
			$data['pay_status'] = (int)1;
			$this->_serv_paysetting->insert($data);

			/** 更新企业后台 (RPC) */
			$this->_rpc->update_cpg($data['cpg_id'], array('pay_type' => $data['pay_type'], 'pay_status' => self::PAID));
		}

		// 存入记录表
		$special_serv = &service::factory('voa_s_cyadmin_company_paysetting_special');
		$special_data = array(
			'ep_id' => $data['ep_id'],
			'pay_type' => $data['pay_type'],
			'ep_money' => $data['ep_money'],
			'remark' => $data['remark'],
			'operator' => $data['operator'],
		);
		// 赋值分期金额
		if (isset($data['first_money']) && $data['first_money'] >= 0) {
			$special_data['first_money'] = $data['first_money'];
			if (isset($data['second_money']) && $data['second_money'] >= 0) {
				$special_data['second_money'] = $data['second_money'];
				if (isset($data['third_money']) && $data['third_money'] >= 0) {
					$special_data['third_money'] = $data['third_money'];
					if (isset($data['fourth_money']) && $data['fourth_money'] >= 0) {
						$special_data['fourth_money'] = $data['fourth_money'];
						if (isset($data['fifth_money']) && $data['fifth_money'] >= 0) {
							$special_data['fifth_money'] = $data['fifth_money'];
						}
					}
				}
			}
		}
		$special_serv->insert($special_data);

		return true;
	}

	/**
	 * 标准产品过滤数据
	 * @param $in
	 * @param $out
	 * @return bool
	 * @throws help_exception
	 */
	protected function _filter($in, &$out) {

		//获取数据
		if (!empty($in)) {
			$data['pay_type'] = $in['pay_type'];
			$data['ep_money'] = $in['ep_money'];
			$data['date_start'] = $in['date_start'];
			$data['date_end'] = $in['date_end'];
			$data['ep_id'] = $in['ep_id'];
			$data['cpg_id'] = $in['cpg_id'];
		} else {
			$this->errmsg('10000', '内容不能为空');

			return false;
		}

		$fields = array(
			'pay_type' => array('pay_type', parent::VAR_INT, null, null, false),
			'ep_money' => array('ep_money', parent::VAR_INT, null, null, false),
			'date_start' => array('date_start', parent::VAR_STR, null, null, false),
			'date_end' => array('date_end', parent::VAR_STR, null, null, false),
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
			'cpg_id' => array('cpg_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}
		if (empty($this->__request['pay_type'])) {
			$this->errmsg('20001', '付费类型不能为空');

			return false;
		}
		if ($this->__request['ep_money'] < 0) {
			$this->errmsg('20011', '付费金额不得为负');

			return false;
		}
		if (!is_numeric($this->__request['ep_money'])) {
			$this->errmsg('20012', '付费金额必须为数字');

			return false;
		}
		if (empty($this->__request['ep_money'])) {
			$this->errmsg('20013', '支付金额不能为空');

			return false;
		}
		if (empty($this->__request['date_start'])) {
			$this->errmsg('20005', '开始日期不能为空');

			return false;
		}
		if (empty($this->__request['date_end'])) {
			$this->errmsg('20006', '截止日期不能为空');

			return false;
		}
		if ($this->__request['date_end'] < $this->__request['date_start']) {
			$this->errmsg('20009', '截止时间不能小于开始时间');

			return false;
		}
		if (empty($this->__request['ep_id'])) {
			$this->errmsg('20007', '缺少必要参数');

			return false;
		}
		if (empty($this->__request['cpg_id'])) {
			$this->errmsg('20008', '缺少购买的套件');

			return false;
		}

		$this->__request['date_start'] = rstrtotime($this->__request['date_start']);
		$this->__request['date_end'] = rstrtotime($this->__request['date_end']);

		$out = $this->__request;
		unset($this->__request);

		return true;
	}

	/**
	 * 定制产品和部署产品 过滤数据
	 * @param $in
	 * @param $out
	 * @return bool
	 * @throws help_exception
	 */
	protected function _filter1($in, &$out) {

		//获取数据
		if (empty($in)) {
			$this->errmsg('10000', '内容不能为空');

			return false;
		}

		$fields = array(
			'pay_type' => array('pay_type', parent::VAR_INT, null, null, false),
			'ep_money' => array('ep_money', parent::VAR_INT, null, null, false),
			'remark' => array('remark', parent::VAR_STR, null, null, false),
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
			'cpg_id' => array('cpg_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}
		if (empty($this->__request['pay_type'])) {
			$this->errmsg('200011', '付费类型不能为空');

			return false;
		}
		unset($in['installment_payment'][0]);
		if (isset($in['installment_payment']) && is_array($in['installment_payment']) && !empty($in['installment_payment'])) {
			foreach ($in['installment_payment'] as $k => $v) {
				if ($v < 0) {
					$this->errmsg('20010', $k . '期金额不得为负');

					return false;
				}
				if (!is_numeric($v)) {
					$this->errmsg('20009', $k . '期金额必须为数字');

					return false;
				}
				if (empty($v)) {
					$this->errmsg('20008', $k . '期金额不得为空');

					return false;
				} else {
					switch ($k) {
						case 1:
							$this->__request['first_money'] = $v;
							break;
						case 2:
							$this->__request['second_money'] = $v;
							break;
						case 3:
							$this->__request['third_money'] = $v;
							break;
						case 4:
							$this->__request['fourth_money'] = $v;
							break;
						case 5:
							$this->__request['fifth_money'] = $v;
							break;
					}
				}
			}
			unset($in['installment_payment']);
		}
		if (empty($this->__request['ep_money'])) {
			$this->errmsg('20003', '支付金额不能为空');

			return false;
		}
		if (empty($this->__request['ep_id'])) {
			$this->errmsg('20007', '缺少必要参数');

			return false;
		}

		$out = $this->__request;
		unset($this->__request);

		return true;
	}
}
