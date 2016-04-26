<?php

/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/20
 * Time: 下午4:28
 */
class voa_uda_cyadmin_company_stop extends voa_uda_cyadmin_base {

	const OPEN_STATUS = 0; // 开启状态
	const STOP_STATUS = 1; // 关闭状态

	/**
	 * 验证数据
	 * @param $in
	 * @param $error
	 * @return bool
	 * @throws help_exception
	 */
	public function filter(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['pay_id'] = $in['id'];
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '缺少参数');

			return false;
		}

		$fields = array(
			'pay_id' => array('pay_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 开启或者关闭应用
	 * @param $in
	 * @param $result
	 * @return bool
	 */
	public function stop_or_start ($in, $result) {

		// 当前时间戳
		$timestamp = startup_env::get('timestamp');

		// 查询数据
		$serv_paysetting = &service::factory('voa_s_cyadmin_company_paysetting');
		$pay_record = $serv_paysetting->get_by_conds(array('pay_id' => $in['pay_id']));

		// 如果为空
		if (!$pay_record) {
			$result = array('errcode' => '10000', 'errmsg' => '没有这条记录,请刷新');
			return false;
		}

		// 初始化RPC
		$this->_rpc_domain($pay_record['ep_id']);

		/** 如果开启状态 */
		// 那么 变为关闭, 并且记录所剩时间
		if ($pay_record['stop_status'] == self::OPEN_STATUS) {
			// 计算 还剩下的时间 (如果记录截止时间小于当前时间,那么就是过期的, 剩下时间为零)
			if ($pay_record['date_end'] > $timestamp) {
				$stop_time = $pay_record['date_end'] - $timestamp;
			} else {
				$stop_time = 0;
			}
			// 更新数据
			$serv_paysetting->update_by_conds(array('pay_id' => $in['pay_id']), array('stop_time' => $stop_time, 'stop_status' => self::STOP_STATUS));

			/** 更新企业后台 (RPC) */
			$this->_rpc->update_cpg($pay_record['cpg_id'], array('stop_status' => self::STOP_STATUS));
		} else {
		/** 如果关闭状态 */
			// 如果剩下的时间为零, 那么是直接更改状态
			if ($pay_record['stop_time'] != 0) {
				// 计算当前时间 加上 关闭之前剩下的时间
				$date_end = $pay_record['stop_time'] + $timestamp;
				// 剩下的时间记录清零
				$stop_time = 0;

				$serv_paysetting->update_by_conds(array('pay_id' => $in['pay_id']), array('date_end' => $date_end, 'stop_time' => $stop_time, 'stop_status' => self::OPEN_STATUS));

				/** 更新企业后台 (RPC) */
				$this->_rpc->update_cpg($pay_record['cpg_id'], array('date_end' => $date_end, 'stop_status' => self::OPEN_STATUS));
			} else {
				$serv_paysetting->update_by_conds(array('pay_id' => $in['pay_id']), array('stop_status' => self::OPEN_STATUS));

				/** 更新企业后台 (RPC) */
				$this->_rpc->update_cpg($pay_record['cpg_id'], array('stop_status' => self::OPEN_STATUS));
			}
		}

		return true;
	}


}
