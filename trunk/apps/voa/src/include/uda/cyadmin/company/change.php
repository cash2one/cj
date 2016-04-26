<?php

/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/19
 * Time: 下午2:30
 */
class voa_uda_cyadmin_company_change extends voa_uda_cyadmin_base {

	/**
	 * 验证数据
	 * @param $in 接收数据
	 * @param $error 错误信息
	 * @return bool
	 * @throws help_exception
	 */
	public function filter(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['ep_id'] = $in['ep_id'];
			$data['remark'] = $in['remark'];
			$data['operator'] = $in['operator'];
			$data['change_customer_status'] = $in['change_customer_status'];
			$data['op_ca_id'] = $in['op_ca_id'];
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '内容不能为空');

			return false;
		}

		$fields = array(
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
			'remark' => array('remark', parent::VAR_STR, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
			'change_customer_status' => array('change_customer_status', parent::VAR_INT, null, null, false),
			'op_ca_id' => array('op_ca_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}

		if (empty($result['ep_id'])) {
			$error = array('errcode' => '10002', 'errmsg' => '缺少公司ID');

			return false;
		}
		if (empty($result['remark'])) {
			$error = array('errcode' => '10003', 'errmsg' => '汇报不得为空');

			return false;
		}
		if (mb_strlen($result['remark']) > 255) {
			$error = array('errcode' => '10004', 'errmsg' => '汇报不得超过255字符');

			return false;
		}
		if (empty($result['operator'])) {
			$error = array('errcode' => '10005', 'errmsg' => '丢失操作人数据');

			return false;
		}
		if (empty($result['op_ca_id'])) {
			$error = array('errcode' => '10006', 'errmsg' => '丢失操作人ID');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 更新数据
	 * @param $post
	 * @return bool
	 */
	public function update_data($post, &$error) {

		// 权限判断
		if (!$this->_authority($post['ep_id'], $post['op_ca_id'])) {
			$error = array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg
			);

			return false;
		}
		unset($post['op_ca_id']);

		// 更新数据
		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		if (!$serv_profile->update_by_conds(array('ep_id' => $post['ep_id']), array('customer_status' => $post['change_customer_status']))) {
			$error = array(
				'errcode' => '20000',
				'errmsg' => '更新失败',
			);

			return false;
		};

		// 添加操作记录
		$record_data = array(
			'ep_id' => $post['ep_id'],
			'operator' => $post['operator'],
			'remark' => $post['remark'],
			'customer_status' => $post['change_customer_status']
		);
		if (!$this->_change_customer_status_record($record_data, $error)) {
			return false;
		}

		return true;
	}

}
