<?php
/**
 * voa_c_admincp_office_meeting_delete
 * 企业后台 - 会议通 - 删除会议
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_delete extends voa_c_admincp_office_meeting_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$mt_id = $this->request->get('mt_id');

		if ( $delete ) {
			$ids = rintval($delete, true);
		} elseif ( $mt_id ) {
			$ids = rintval($mt_id, false);
		}

		if ( empty($ids) ) {
			$this->message('error', '请指定要删除的会议');
		}

		$servm = &service::factory('voa_s_oa_meeting', array('pluginid' => $this->_module_plugin_id));
		$servmm = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => $this->_module_plugin_id));

		/** 删除会议 */
		try {

			/** 开始删除过程 */
			$servm->begin();

			/** 删除会议主表记录 */
			$servm->delete_by_ids($ids);

			/** 删除参会人员记录 */
			$servmm->delete_by_mt_id(array('mt_id'=>$ids));

			/** 提交删除过程 */
			$servm->commit();

		} catch (Exception $e) {
			$servm->rollback();
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}

		$this->message('success', '指定会议信息删除操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
	}

}
