<?php

/**
 * voa_c_admincp_office_activity_delete
 * 企业后台/企业文化/活动报名/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_activity_delete extends voa_c_admincp_office_activity_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$acid = $this->request->get('acid');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($acid) {
			$ids = rintval($acid, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的 ' . $this->_module_plugin['cp_name'] . ' 数据');
		}
		//删除
		$serv = &service::factory('voa_s_oa_activity');

		if ($serv->delete($ids)) {
			$this->message('success', '指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定' . $this->_module_plugin['cp_name'] . '信息删除操作失败');
		}
	}

}
