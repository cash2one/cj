<?php
/**
 * del.php
 * 更新删除状态
 * $Author$
 * $Id$
 */
class voa_c_api_file_put_del extends voa_c_api_file_base {

	public function execute() {

		// 接受的参数
		$fields = array(
			'id' => array('type' => 'int', 'required' => true),
		);

		// 基本变量检查
		$this->_check_params($fields);
		
		//update at_deleted ,at_status 
		$ret = null;
		$uda = &uda::factory('voa_uda_frontend_file_update');
		if(!$uda->del($ret, $this->_params['id'], $this->_member['m_uid'])) {
			$this->_set_errcode($uda->error);
			return false;
		}
		return true;
	}

	
}
