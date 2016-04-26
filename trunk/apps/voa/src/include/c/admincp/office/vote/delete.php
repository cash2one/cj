<?php
/**
 * voa_c_admincp_office_vote_delete
 * 企业后台/应用宝/微评选/删除投票
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vote_delete extends voa_c_admincp_tool_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$v_id = $this->request->get('v_id');

		if ( $delete ) {
			$ids = rintval($delete, true);
		} elseif ($v_id) {
			$ids = rintval($v_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的投票');
		}

		try {
			$servm = &service::factory('voa_s_oa_vote', array('pluginid' => $this->_module_plugin_id));

			/** 开始删除过程 */
			$servm->begin();

			/** 删除项目主表记录 */
			$servm->delete_by_ids($ids);

			/** 删除参与投票人数据 */
			$serv_mem = &service::factory('voa_s_oa_vote_mem', array('pluginid' => $this->_module_plugin_id));
			$serv_mem->delete_by_v_id($ids);

			/** 删除投票选项数据 */
			$serv_opt = &service::factory('voa_s_oa_vote_option', array('pluginid' => $this->_module_plugin_id));
			$serv_opt->delete_by_v_id($ids);

			/** 删除投票权限表数据 */
			$serv_vpu = &service::factory('voa_s_oa_vote_permit_user', array('pluginid' => $this->_module_plugin_id));
			$serv_vpu->delete_by_v_id($ids);

			/** 提交删除过程 */
			$servm->commit();

		} catch (Exception $e) {
			$servm->rollback();
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}

		$this->message('success', '指定投票信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);

	}

}
