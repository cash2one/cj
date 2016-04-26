<?php
/**
 * delete.php
 * 后台API，删除指定附件
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_api_attachment_delete extends voa_c_admincp_api_attachment_base {

	public function execute() {

		$o_ids = (string)$this->request->get('id');

		// 重新整理id数组
		$ids = array();
		foreach (explode(',', $o_ids) as $_id) {
			$_id = trim($_id);
			if (!is_numeric($_id)) {
				continue;
			}
			$_id = (int)$_id;
			if (!isset($ids[$_id])) {
				$ids[$_id] = $_id;
			}
		}

		if (empty($ids)){
			return $this->_admincp_error_message(voa_errcode_api_attachment::DELETE_NULL);
		}

		// 处理删除
		$uda = &service::factory('voa_uda_frontend_attachment_delete', array('pluginid' => startup_env::get('pluginid')));
		if (!$uda->delete($ids)) {
			return $this->_admincp_error_message(voa_errcode_api_attachment::DELETE_ERROR, $uda->error);
		}

		return $this->_output_result(array(
			'ids' => implode(',', $ids),
		));

	}

}
