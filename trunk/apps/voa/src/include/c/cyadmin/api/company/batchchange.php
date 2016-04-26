<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/14
 * Time: 下午9:43
 * 批量更改负责人
 */

class voa_c_cyadmin_api_company_batchchange extends voa_c_cyadmin_api_base {

	public function execute() {

		$post = $this->request->postx();

		$uda = &uda::factory('voa_uda_cyadmin_company_batchchange');

		// 验证数据
		$error = array();
		if (!$uda->filter($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];
			return false;
		};

		// 循环变更
		$err_name = array();
		foreach ($post['ep_ids'] as $_key => $_value) {
			$temp = array(
				'ep_id' => $_value, // 企业ID
				'op_ca_id' => $post['ca_id'], // 操作人ID
				'ca_id' => $post['edit_lead_id'], // 负责人ID
				'operator' => $post['operator'], // 操作人名称
			);
			$uda->update_data($temp, $err_name[]);
		}

		// 返回提示
		$this->_errmsg = '操作完成!';
		if (!empty($err_name)) {
			// 去除空值
			foreach ($err_name as $_key => $_val) {
				if (!isset($_val)) {
					unset($err_name[$_key]);
				}
			}
			if (empty($err_name)) return true;
			// 返回失败名
			$err_name = implode('、', $err_name);
			$this->_errmsg .= '失败: ' . $err_name;
		}

		return true;
	}




}
