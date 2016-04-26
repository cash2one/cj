<?php
/**
 * voa_c_admincp_office_namecard_delete
 * 企业后台/微办公管理/微名片/名片删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_delete extends voa_c_admincp_office_namecard_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$nc_id = $this->request->get('nc_id');

		if ( $delete ) {
			$ids = rintval($delete, true);
		} elseif ( $nc_id ) {
			$ids = rintval($nc_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的名片');
		}

		/** 统计职务、群组、公司需要更新的数量 */
		$ncj_ids = array();
		$ncf_ids = array();
		$ncc_ids = array();
		foreach ($this->_service_single('namecard', $this->_module_plugin_id, 'fetch_by_ids', $ids) AS $_nc) {
			if (!isset($ncj_ids[$_nc['ncj_id']])) {
				$ncj_ids[$_nc['ncj_id']] = 1;
			} else {
				$ncj_ids[$_nc['ncj_id']]++;
			}
			if (!isset($ncf_ids[$_nc['ncf_id']])) {
				$ncf_ids[$_nc['ncf_id']] = 1;
			} else {
				$ncf_ids[$_nc['ncf_id']]++;
			}
			if (!isset($ncc_ids[$_nc['ncc_id']])) {
				$ncc_ids[$_nc['ncc_id']] = 1;
			} else {
				$ncc_ids[$_nc['ncc_id']]++;
			}
		}

		/** 聚合相同数量的相关id，减少写表频次 */
		$ncj_update = array();
		foreach ($ncj_ids AS $_ncj_id => $_count) {
			$ncj_update[$_count][] = $_ncj_id;
		}
		unset($_ncj_id, $_count);
		$ncf_update = array();
		foreach ($ncf_ids AS $_ncf_id => $_count) {
			$ncf_update[$_count][] = $_ncf_id;
		}
		unset($_ncf_id, $_count);
		$ncc_update = array();
		foreach ($ncc_ids AS $_ncc_id => $_count) {
			$ncc_update[$_count][] = $_ncc_id;
		}
		unset($ncj_ids, $ncf_ids, $ncc_ids, $_ncc_id, $_count);

		$servm = &service::factory('voa_s_oa_namecard', array('pluginid' => $this->_module_plugin_id));
		$serv_ncj = &service::factory('voa_s_oa_namecard_job', array('pluginid' => $this->_module_plugin_id));
		$serv_ncf = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => $this->_module_plugin_id));
		$serv_ncc = &service::factory('voa_s_oa_namecard_company', array('pluginid' => $this->_module_plugin_id));
		try {

			/** 开始删除过程 */
			$servm->begin();

			/** 删除名片主表记录 */
			$servm->delete_by_ids($ids);

			/** 减少对应职务的记录数 */
			foreach ($ncj_update AS $_count => $_ncj_ids) {
				$serv_ncj->update_num($_ncj_ids, '-', $_count);
			}

			/** 减少对应群组的记录数 */
			foreach ($ncf_update AS $_count => $_ncf_ids) {
				$serv_ncf->update_num($_ncf_ids, '-', $_count);
			}

			/** 减少对应公司的记录数 */
			foreach ($ncc_update AS $_count => $_ncc_ids) {
				$serv_ncc->update_num($_ncc_ids, '-', $_count);
			}

			/** 提交删除过程 */
			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}

		$this->message('success', '指定名片信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);

	}

}
