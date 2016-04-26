<?php
/**
 * voa_c_admincp_office_dailyreport_delete
 * 企业后台/微办公管理/日报/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_dailyreport_delete extends voa_c_admincp_office_dailyreport_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$dr_id = $this->request->get('dr_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($dr_id) {
			$ids = rintval($dr_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的'.$this->_module_plugin['cp_name']);
		}

		try {
			$servm = &service::factory('voa_s_oa_dailyreport', array('pluginid' => $this->_module_plugin_id));

			/** 开始删除过程 */
			$servm->begin();

			/** 删除主表记录 */
			$servm->delete_by_ids($ids);

			/** 删除参与人数据 */
			$serv_mem = &service::factory('voa_s_oa_dailyreport_mem', array('pluginid' => $this->_module_plugin_id));
			$serv_mem->delete_by_dr_ids($ids);

			/** 删除批注数据 */
			$serv_opt = &service::factory('voa_s_oa_dailyreport_post', array('pluginid' => $this->_module_plugin_id));
			$serv_opt->delete_by_dr_ids($ids);

			/** 提交删除过程 */
			$servm->commit();

		} catch (Exception $e) {
			$servm->rollback();
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}

		$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);

	}

}
