<?php
/**
 * voa_c_admincp_office_sale_delete
 * 企业后台/销售管理/删除
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sale_delete extends voa_c_admincp_office_sale_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$scid = $this->request->get('scid');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($scid) {
			$ids = rintval($scid, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的 '.$this->_module_plugin['cp_name'].' 数据');
		}
		//删除
		$serv = &service::factory('voa_s_oa_sale_coustmer');

		if ($serv->delete($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}
