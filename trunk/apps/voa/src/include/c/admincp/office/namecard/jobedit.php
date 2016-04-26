<?php
/**
 * voa_c_admincp_office_namecard_jobedit
 * 企业后台/微办公管理/微名片/职务管理/删除修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_jobedit extends voa_c_admincp_office_namecard_base {

	public function execute() {

		$delete = $this->request->post('delete');
		$delete = rintval($delete, true);
		$edit = $this->request->post('edit');
		if (!is_array($edit)) {
			$edit = array();
		}

		/** 编辑 */
		$ncj_ids = array_keys($edit);
		foreach ($this->_get_job_by_ncj_ids($this->_module_plugin_id, $ncj_ids) AS $_id => $_data) {
			if (!isset($edit[$_id]) || isset($delete[$_id])) {
				continue;
			}
			$update = array();
			if (isset($edit[$_id]['ncj_name']) && $edit[$_id]['ncj_name'] != $_data['ncj_name']) {
				$update['ncj_name'] = trim($edit[$_id]['ncj_name']);
			}
			if (empty($update)) {
				continue;
			}
			$this->_service_single('namecard_job', $this->_module_plugin_id, 'update', $update, array('ncj_id' => $_id));
		}

		/** 删除 */
		if ($delete) {

			try {
				$servm = &service::factory('voa_s_oa_namecard_job', array('pluginid' => $this->_module_plugin_id));
				$serv_ncc = &service::factory('voa_s_oa_namecard', array('pluginid' => $this->_module_plugin_id));
				/** 开始删除过程 */
				$servm->begin();

				/** 删除职务表记录 */
				$servm->delete_by_ids($delete);

				/** 设置与对应职务有关的名片的职务数据为0 */
				$serv_ncc->update(array('ncj_id' => 0), array('ncj_id' => $delete));

				/** 提交删除过程 */
				$servm->commit();

			} catch (Exception $e) {
				$servm->rollback();
				logger::error($e);
				throw new controller_exception($e->getMessage(), $e->getCode());
			}

		}

		$this->message('success', '指定职务信息更新操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'job', $this->_module_plugin_id)), false);

	}

}
