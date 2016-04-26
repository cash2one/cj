<?php
/**
 * 附件删除
 * $Author$
 * $Id$
 */

class voa_c_frontend_attachment_delete extends voa_c_frontend_attachment_base {

	public function execute() {
		$id = (int)$this->request->get('id');
		$uda = &service::factory('voa_uda_frontend_attachment_delete', array('pluginid' => startup_env::get('pluginid')));
		if (!$uda->delete($id)) {
			$this->_json_message(array(
				"resultCode" => $uda->errno, /** 0表示成功; 大于0表示有错误, 此时describe内容会弹出提示 */
				"describe" => $uda->error,
				"id" => $id
			));
			return false;
		}

		$this->_json_message(array(
			"resultCode" => 0, /** 0表示成功; 大于0表示有错误, 此时describe内容会弹出提示 */
			"describe" => "",
			"id" => $id
		));
	}

}
