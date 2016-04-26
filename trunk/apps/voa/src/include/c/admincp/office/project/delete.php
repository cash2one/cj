<?php
/**
 * voa_c_admincp_office_project_delete
 * 企业后台/微办公管理/工作台/删除项目
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_delete extends voa_c_admincp_office_project_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$p_id = $this->request->get('p_id');

		if ( $delete ) {
			$ids = rintval($delete, true);
		} elseif ($p_id) {
			$ids = rintval($p_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的项目');
		}

		$servm = &service::factory('voa_s_oa_project', array('pluginid' => $this->_module_plugin_id));
		$serv_mem = &service::factory('voa_s_oa_project_mem', array('pluginid' => $this->_module_plugin_id));
		$serv_pro = &service::factory('voa_s_oa_project_proc', array('pluginid' => $this->_module_plugin_id));
		try {
			/** 开始删除过程 */
			$servm->begin();

			/** 删除项目主表记录 */
			$servm->delete_by_ids($ids);

			/** 删除相关项目的参与人员 */
			$serv_mem->delete_by_p_ids($ids);

			/** 删除相关项目的进度信息 */
			$serv_pro->delete_by_p_ids($ids);

			/** 提交删除过程 */
			$servm->commit();

		} catch (Exception $e) {
			$servm->rollback();
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}

		$this->message('success', '指定项目信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);

	}

}
