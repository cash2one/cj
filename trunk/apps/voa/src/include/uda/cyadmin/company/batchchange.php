<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/14
 * Time: 下午10:03
 */

class voa_uda_cyadmin_company_batchchange extends voa_uda_cyadmin_base {

	/**
	 * 验证数据
	 * @param $in 输入数据
	 * @param $error 报错信息
	 * @return bool
	 */
	public function filter(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['ca_id'] = $in['ca_id'];
			$data['ep_ids'] = $in['ep_ids'];
			$data['edit_lead_id'] = $in['edit_lead_id'];
			$data['operator'] = $in['operator'];
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '内容不能为空');

			return false;
		}

		// 验证规则
		$fields = array(
			'ca_id' => array('ca_id', parent::VAR_INT, null, null, false),
			'ep_ids' => array('ep_ids', parent::VAR_ARR, null, null, false),
			'edit_lead_id' => array('edit_lead_id', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}
		if (empty($result['edit_lead_id'])) {
			$error = array('errcode' => '10002', 'errmsg' => '丢失负责人ID');

			return false;
		}
		if (empty($result['ep_ids'])) {
			$error = array('errcode' => '10003', 'errmsg' => '丢失企业ID');

			return false;
		}
		if (empty($result['ca_id'])) {
			$error = array('errcode' => '10004', 'errmsg' => '丢失当前操作人ID');

			return false;
		}
		if (empty($result['operator'])) {
			$error = array('errcode' => '10005', 'errmsg' => '丢失当前操作人名字');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 更新数据
	 * @param $in 输入数据
	 * @param $error
	 * @return bool
	 */
	public function update_data($in, &$error) {

		// 获取当前企业信息
		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$ep_data = $serv->get_by_conds(array('ep_id' => $in['ep_id']));
		if (empty($ep_data)) {

			return false;
		}
		// 要变更的负责人是否和当前负责人一样
		if ($ep_data['ca_id'] == $in['ca_id']) {
			return true;
		}

		// 判断当前操作人有没有权限
		if (!$this->_authority($in['ep_id'], $in['op_ca_id'])) {
			// 返回失败的企业名称
			$error = $ep_data['ep_name'];

			return false;
		};

		// 更改负责人
		$serv->update_by_conds(array('ep_id' => $in['ep_id']), array('ca_id' => $in['ca_id']) );

		// 变更操作记录
		$in['ca_id_t'] = $ep_data['ca_id']; // 变更前的负责人
		$this->_change_lead_then_record($in);

		return true;
	}

}
