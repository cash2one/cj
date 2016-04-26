<?php
/**
 * voa_c_cyadmin_manage_adminer_delete
 * 主站后台/后台管理/管理员管理/删除管理员
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminer_delete extends voa_c_cyadmin_manage_adminer_base {

	public function execute() {

		$ca_id = $this->request->get('ca_id');
		$ca_id = rintval($ca_id, false);
		$this->_adminer_delete($ca_id);
		voa_h_cache::get_instance()->get('adminer', 'cyadmin', true);
		$this->message('success', '指定管理员删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list'), false);
	}

}
