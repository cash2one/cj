<?php

/**
 * extended.php
 * Created by zhoutao.
 * Created Time: 2015/8/17  20:10
 */
class voa_uda_cyadmin_company_extended extends voa_uda_cyadmin_base {

	const TRY_STATUS = 2; // 试用期类型

	protected $__request = '';

	public function extended($postx, &$error) {

		$timestamp = startup_env::get('timestamp');

		// 验证数据
		$post = array();
		if (!$this->_filter($postx, $post)) {
			$error = array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg,
			);

			return false;
		}

		// 初始化RPC
		$this->_rpc_domain($post['ep_id']);

		// 获取付费记录
		$serv_paysetting = &service::factory('voa_s_cyadmin_company_paysetting');
		$pay_data = $serv_paysetting->get_by_conds(array('pay_id' => $post['pay_id']));

		if (!empty($pay_data)) {
			// 判断是否已经被停用
			if ($pay_data['stop_status'] == 1) {
				$error = array(
					'errcode' => '80000',
					'errmsg'  => '已经被停用,得先开启',
				);

				return false;
			}

			// 计算延期的截止时间, 如果当前的使用记录还没有过期,那么是累加, 如果已经过期了, 那么就是再 试用
			if ($pay_data['date_end'] > $timestamp) {
				$end_time = $post['extended'] * 86400 + $pay_data['date_end'];
				$start_time = $pay_data['date_start'];
			} else {
				$end_time = $post['extended'] * 86400 + $timestamp;
				$start_time = $timestamp;
			}
		} else {
			$error = array(
				'errcode' => '50000',
				'errmsg' => '丢失重要参数'
			);

			return false;
		}

		// 更新应用使用情况
		$serv_paysetting->update_by_conds(array(
			'pay_id' => $post['pay_id'],
		), array(
			'date_start' => $start_time,
			'date_end' => $end_time,
		));

		/** 更新企业后台 (RPC) */
		$this->_rpc->update_cpg($post['cpg_id'], array('date_start' => $start_time, 'date_end' => $end_time, 'pay_status' => self::TRY_STATUS));

		// 试用延期记录入库
		$serv_trial = &service::factory('voa_s_cyadmin_company_trial');
		$serv_trial->insert(
			array(
				'ep_id' => $post['ep_id'],
				'cpg_id' => $post['cpg_id'],
				'start_time' => $start_time,
				'end_time' => $end_time,
				'extended' => $post['extended'],
				'operator' => $post['operator']
			)
		);

		return true;
	}

	/**
	 * 过滤数据
	 * @param $in
	 * @param $out
	 * @return bool
	 * @throws help_exception
	 */
	protected function _filter($in, &$out) {

		//获取数据
		if (!empty($in)) {
			$data['extended'] = $in['extended'];
			$data['operator'] = $in['operator'];
			$data['pay_id'] = $in['pay_id'];
			$data['cpg_id'] = $in['cpg_id'];
			$data['ep_id'] = $in['ep_id'];
		} else {
			$this->errmsg('10000', '内容不能为空');

			return false;
		}

		$fields = array(
			'extended' => array('extended', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
			'pay_id' => array('pay_id', parent::VAR_INT, null, null, false),
			'cpg_id' => array('cpg_id', parent::VAR_INT, null, null, false),
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}
		if (empty($this->__request['extended'])) {
			$this->errmsg('20001', '延长天数不能为空');

			return false;
		}
		if (empty($this->__request['operator'])) {
			$this->errmsg('20003', '丢失操作人信息');

			return false;
		}
		if ($this->__request['extended'] < 1) {
			$this->errmsg('20004', '延长天数不得小于1');

			return false;
		}
		if (empty($this->__request['ep_id'])) {
			$this->errmsg('20005', '丢失企业ID');

			return false;
		}
		if (empty($this->__request['cpg_id'])) {
			$this->errmsg('20005', '丢失套件ID');

			return false;
		}

		$out = $this->__request;
		unset($this->__request);

		return true;
	}

}
