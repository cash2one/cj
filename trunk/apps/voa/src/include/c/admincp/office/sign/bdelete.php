<?php

/**
 * 企业后台/删除
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_bdelete extends voa_c_admincp_office_sign_base {

	public function execute() {

		// 获取操作参数
		$ids = 0;
		$delete = $this->request->post('delete');
		$sbid = $this->request->get('sbid');

		// 获取要删除的ID值
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($sbid) {
			$ids = rintval($sbid, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的数据');
		}

		//删除
		$serv = &service::factory('voa_s_oa_sign_batch');

		if ($serv->delete($ids)) {
			// 删除班次的关联表信息
			$serv_department = &service::factory('voa_s_oa_sign_department');
			$conds['sbid IN (?)'] = $ids;

			$department_list = $serv_department->list_by_conds($conds);
			$dep_list = array();
			foreach ($department_list as $val) {
				$dep_list[] = $val['sdid'];
			}

			//删除相关部门
			$serv_department->delete($dep_list);

			$this->message('success', '指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定' . $this->_module_plugin['cp_name'] . '信息删除操作失败');
		}
	}

}
