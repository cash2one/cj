<?php
/**
 * voa_c_admincp_office_namecard_companyedit
 * 企业后台/微办公管理/微名片/公司管理/删除修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_companyedit extends voa_c_admincp_office_namecard_base {

	public function execute() {

		$delete = $this->request->post('delete');
		$delete = rintval($delete, true);
		$edit = $this->request->post('edit');
		if (!is_array($edit)) {
			$edit = array();
		}

		/** 编辑 */
		$ncc_ids = array_keys($edit);
		foreach ($this->_get_company_by_ncc_ids($this->_module_plugin_id, $ncc_ids) AS $_id => $_data) {
			if (!isset($edit[$_id]) || isset($delete[$_id])) {
				continue;
			}
			$update = array();
			if (isset($edit[$_id]['ncc_name']) && $edit[$_id]['ncc_name'] != $_data['ncc_name']) {
				$update['ncc_name'] = trim($edit[$_id]['ncc_name']);
			}
			if (empty($update)) {
				continue;
			}
			$this->_service_single('namecard_company', $this->_module_plugin_id, 'update', $update, array('ncc_id' => $_id));
		}

		/** 删除 */
		if ($delete) {

			$servm = &service::factory('voa_s_oa_namecard_company', array('pluginid' => $this->_module_plugin_id));
			$serv_ncc = &service::factory('voa_s_oa_namecard', array('pluginid' => $this->_module_plugin_id));

			try {

				/** 开始删除过程 */
				$servm->begin();

				/** 删除公司表记录 */
				$servm->delete_by_ids($delete);

				/** 设置与对应公司有关的名片的公司数据为0 */
				$serv_ncc->update(array('ncc_id' => 0), array('ncc_id' => $delete));

				/** 提交删除过程 */
				$servm->commit();

			} catch (Exception $e) {
				$servm->rollback();
				logger::error($e);
				throw new controller_exception($e->getMessage(), $e->getCode());
			}

		}

		$this->message('success', '指定公司信息更新操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'company', $this->_module_plugin_id)), false);

	}

}
