<?php
/**
 * voa_c_api_project_put_file
 * 【应用:任务】删除文件接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_put_file extends voa_c_api_project_base {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 待删除的文件id，在任务里的文件id，而非公共附件表内的id
			'ids' => array('type' => 'string_trim', 'required' => true)
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
			return $this->_set_errcode(voa_errcode_api_project::DELETE_FILE_NULL);
		}

		$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => 0));

		// 找到任务文件id对应的公共附件
		$attachs = $serv_pat->fetch_by_ids($ids);

		// 需要删除的公共附件id
		$at_ids = array();

		// 需要删除的任务文件id
		$pat_ids = array();

		// 检查当前操作的人可删除的文件
		foreach ($attachs as $attach) {
			if ($attach['m_uid'] != $this->_member['m_uid']) {
				continue;
			}
			$at_ids[] = $attach['at_id'];
			$pat_ids[] = $attach['pat_id'];
		}

		if (empty($pat_ids)) {
			$this->_set_errcode(voa_errcode_api_project::DELETE_FILE_NULL);
		}

		if ($serv_pat->delete($pat_ids)) {
			// 删除任务文件
			$uda = &uda::factory('voa_uda_frontend_attachment_delete');
			$uda->delete($at_ids);
		}

		$this->_result = array(
			'ids' => implode(',', $at_ids)
		);

		return true;
	}

}
