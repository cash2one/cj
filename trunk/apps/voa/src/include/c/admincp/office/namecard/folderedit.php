<?php
/**
 * voa_c_admincp_office_namecard_folderedit
 * 企业后台/微办公管理/微名片/群组管理/删除修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_folderedit extends voa_c_admincp_office_namecard_base {

	public function execute() {

		$delete = $this->request->post('delete');
		$delete = rintval($delete, true);
		$edit = $this->request->post('edit');
		if (!is_array($edit)) {
			$edit = array();
		}

		/** 编辑 */
		$ncf_ids = array_keys($edit);
		foreach ($this->_get_folder_by_ncf_ids($this->_module_plugin_id, $ncf_ids) AS $_id => $_data) {
			if (!isset($edit[$_id]) || isset($delete[$_id])) {
				continue;
			}
			$update = array();
			if (isset($edit[$_id]['ncf_name']) && $edit[$_id]['ncf_name'] != $_data['ncf_name']) {
				$update['ncf_name'] = trim($edit[$_id]['ncf_name']);
			}
			if (empty($update)) {
				continue;
			}
			$this->_service_single('namecard_folder', $this->_module_plugin_id, 'update', array('ncf_id' => $_id));
		}

		/** 删除 */
		if ($delete) {

			$servm = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => $this->_module_plugin_id));
			$serv_ncc = &service::factory('voa_s_oa_namecard', array('pluginid' => $this->_module_plugin_id));
			try {

				/** 开始删除过程 */
				$servm->begin();

				/** 删除群组表记录 */
				$servm->delete_by_ids($delete);

				/** 设置与对应群组有关的名片的群组数据为0 */
				$serv_ncc->update(array('ncf_id' => 0), array('ncf_id' => $delete));

				/** 提交删除过程 */
				$servm->commit();

			} catch (Exception $e) {
				$servm->rollback();
				logger::error($e);
				throw new controller_exception($e->getMessage(), $e->getCode());
			}

		}

		$this->message('success', '指定群组信息更新操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'folder', $this->_module_plugin_id)), false);

	}

}
