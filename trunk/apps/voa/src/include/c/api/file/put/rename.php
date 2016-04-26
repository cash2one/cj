<?php
/**
 * rename.php
 * 重命名
 * $Author$
 * $Id$
 */
class voa_c_api_file_put_rename extends voa_c_api_file_base {

	public function execute() {

		// 接受的参数
		$fields = array(
			'id' => array('type' => 'int', 'required' => true),
			'rename' => array('type' => 'string', 'required' => true),
		);

		// 基本变量检查
		$this->_check_params($fields);

		//update 
		$uda = &uda::factory('voa_uda_frontend_file_update');
		$ret = null;
		if(!$uda->rename($ret, $this->_params['id'], $this->_params['rename'], $this->_member['m_uid'])) {
			$this->_set_errcode($uda->error);
			return false;
		}
		return true;
	}

	
}
