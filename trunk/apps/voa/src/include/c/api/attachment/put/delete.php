<?php
/**
 * voa_c_api_attachment_put_delete
 * 文件删除接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_attachment_put_delete extends voa_c_api_attachment_base {

	public function execute() {

		// 请求参数
		$fields = array(
			// 待删除的附件id，多个id之间使用半角逗号“,”分隔
			'ids' => array('type' => 'string_trim', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 重新整理id数组
		$ids = array();
		foreach (explode(',', $this->_params['ids']) as $id) {
			$id = trim($id);
			if (!is_numeric($id)) {
				continue;
			}
			$id = (int)$id;
			if (!isset($ids[$id])) {
				$ids[$id] = $id;
			}
		}

		if (empty($ids)) {
			return $this->_set_errcode(voa_errcode_api_attachment::DELETE_NULL);
		}

		// 处理删除
		$uda = &service::factory('voa_uda_frontend_attachment_delete', array('pluginid' => startup_env::get('pluginid')));
		if (!$uda->delete($ids)) {
			return $this->_set_errcode(voa_errcode_api_attachment::DELETE_ERROR, $uda->error);
		}

		$this->_result = array(
			'ids' => implode(',', $ids),
		);

		return true;
	}

}
