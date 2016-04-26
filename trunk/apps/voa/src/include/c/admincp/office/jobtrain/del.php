<?php
/**
* voa_c_admincp_office_jobtrain_add
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_del extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_frontend_jobtrain_article');
		$id = $this->request->get('id');
		$data = $this->request->postx();
		$ids = $data['ids'];
		if(empty($ids) && !empty($id)){
			$ids = array($id);
		}
		
		if (empty($ids)) {
			$this->message('error', '请指定要删除的信息');
		}
		if ($uda->del_article($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}