<?php

/**
 * voa_c_admincp_office_askfor_template
 * 企业后台 - 审批流 - 列表
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_edittemplate extends voa_c_admincp_office_askfor_base {

	public function execute() {

		// 获取模板ID
		$aft_id = (int)$this->request->get('aft_id');
		$serv_temp = &service::factory('voa_s_oa_askfor_template');
		$aft_data = $serv_temp->fetch_by_id($aft_id);
		if (empty($aft_data)) {
			$this->_error_message('没有这个模板数据');
		}

		$temp = array();
		// 审批人数据
		$approver_data = array();
		$level = array();
		if (!empty($aft_data['approvers'])) {
			$approver_data = unserialize($aft_data['approvers']);
			// 按等级遍历
			foreach ($approver_data as $_level => $_user_data) {
				// 按人员遍历
				foreach ($_user_data as $_data) {
					$_data['selelcted'] = (bool)true;
					// 按每个等级划分
					$temp[] = $_data;
				}
				$level[] = array_values($temp);
				unset($temp);
			}
		}
		$approver_data = json_encode(array_values($level));

		// 自定义字段数据
		$custom = array();
		$temp = array();
		if (!empty($aft_data['custom'])) {
			$custom = unserialize($aft_data['custom']);
		}

		// 抄送人默认数据
		$copy = array(); // 留空防止报错
		if (!empty($aft_data['copy'])) {
			$copy = unserialize($aft_data['copy']);
			// 拼凑数据
			foreach ($copy as $_key => $_val) {
				if ($_key == 'selected') {
					$temp['selected'] = (bool)true;
				} else {
					$temp[$_key] = $_val;
				}
			}
		}

		// 适应前端组件格式
		if (!empty($temp)) {
			$copy = json_encode(array_values($temp));
		} else {
			$copy = json_encode($temp);
		}
		unset($temp);

		// 获取默认适用部门数据
		$sbu_id = array();
		if (!empty($aft_data['sbu_id'])) {
			$sbu_id = unserialize($aft_data['sbu_id']);
			foreach ($sbu_id as $_key => $_val) {
				if ($_key == 'isChecked') {
					$temp['isChecked'] = (bool)true;
				} else {
					$temp[$_key] = $_val;
				}
			}
		}
		$sbu_id = json_encode(array_values($sbu_id));
		unset($temp);

		unset($aft_data['approvers']);
		unset($aft_data['copy']);

		$this->view->set('aft_id', $aft_id); // 模板ID

		$this->view->set('create_id', $this->_user['ca_id']); // 创建人cd_id
		$this->view->set('create_username', $this->_user['ca_username']); // 创建人名称
		$this->view->set('act', 'edit'); // 操作
		$this->view->set('temp_default_data', $aft_data); // 名称等
		$this->view->set('templist_url', $this->cpurl($this->_module, $this->_operation, 'template', $this->_module_plugin_id));

		$this->view->set('copy', $copy); // 抄送人
		$this->view->set('dep_arr', $sbu_id); // 适用部门
		$this->view->set('approver_default_data', $approver_data); // 审批人数据
		$this->view->set('custom', $custom); // 自定义字段数据

		$this->output('office/askfor/add_form');
	}

}
