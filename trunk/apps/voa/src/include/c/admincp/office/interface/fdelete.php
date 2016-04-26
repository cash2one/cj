<?php

/**
 * voa_c_admincp_office_interface_fdelete
 * 企业后台/测试应用/删除流程
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_interface_fdelete extends voa_c_admincp_office_interface_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$acid = $this->request->get('f_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($acid) {
			$ids = rintval($acid, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的数据');
		}
		//删除
		$serv = &service::factory('voa_s_oa_interface_flow');

		if ($serv->delete($ids)) {
			$this->message('success', '指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'flowlist', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定' . $this->_module_plugin['cp_name'] . '信息删除操作失败');
		}
	}

}
