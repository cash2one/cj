<?php
/**
 * voa_c_admincp_tool_secret_delete
 * 企业后台/应用宝/秘密/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_secret_delete extends voa_c_admincp_tool_secret_base {

	public function execute() {

		// 删除指定的一组主题id
		$delete = $this->request->post('delete');
		$delete = rintval($delete, true);

		// 删除单个主题id
		$st_id = $this->request->get('st_id');
		$st_id = rintval($st_id, false);

		// 删除指定一组回复id
		$stp_ids = $this->request->post('stp_ids');
		$stp_ids = rintval($stp_ids, true);

		// 删除指定单个回复id
		$stp_id = $this->request->get('stp_id');
		$stp_id = rintval($stp_id, false);

		// 删除混合型的id（包含st_ids和stp_ids）
		$mixed = $this->request->post('mixed');
		if ($mixed) {
			if (isset($mixed['st_ids']) && is_array($mixed['st_ids'])) {
				$mixed['st_ids'] = rintval($mixed['st_ids'], true);
			}
			if (isset($mixed['stp_ids']) && is_array($mixed['stp_ids'])) {
				$mixed['stp_ids'] = rintval($mixed['stp_ids'], true);
			}
		}
		if (!isset($mixed['st_ids'])) {
			$mixed['st_ids'] = array();
		}
		if (!isset($mixed['stp_ids'])) {
			$mixed['stp_ids'] = array();
		}

		/** 删除一组主题id */
		// 待删除的主题id组
		$delete_st_ids = array();
		if ($delete) {
			// 指定删除一组主题id
			$delete_st_ids = $delete;
		}
		if ($st_id) {
			// 指定删除某个主题id
			$delete_st_ids[] = $st_id;
		}
		if ($mixed['st_ids']) {
			foreach ($mixed['st_ids'] as $_st_id) {
				if (!in_array($_st_id, $delete_st_ids)) {
					$delete_st_ids[] = $_st_id;
				}
			}
		}
		unset($delete, $st_id, $mixed['st_ids']);

		/** 删除一组回复id */
		// 待删除的回复id组
		$delete_stp_ids = array();
		if ($stp_ids) {
			// 指定删除一组回复id
			$delete_stp_ids = $stp_ids;
		}
		if ($stp_id) {
			// 指定删除某个回复id
			$delete_stp_ids[] = $stp_id;
		}
		if ($mixed['stp_ids']) {
			foreach ($mixed['stp_ids'] as $_stp_id) {
				if (!in_array($_stp_id, $delete_stp_ids)) {
					$delete_stp_ids[] = $_stp_id;
				}
			}
		}
		unset($stp_ids, $stp_id, $mixed['stp_ids']);

		if (empty($delete_st_ids) && empty($delete_stp_ids)) {
			$this->message('error', '请指定要删除的数据');
		}

		// 载入统一数据接口处理
		$uda_delete = &uda::factory('voa_uda_frontend_secret_delete');
		$shard_key = array('pluginid' => $this->_module_plugin_id);

		if ($delete_stp_ids) {
			// 删除指定的回复id
			if (!$uda_delete->secret_post($delete_stp_ids, $shard_key)) {
				$this->message('error', '删除指定的 回复数据 操作失败');
			}
		}

		if ($delete_st_ids) {
			// 删除指定的主题id
			if (!$uda_delete->secret($delete_st_ids)) {
				$this->message('error', '删除指定的 主题数据 操作失败');
			}
		}


		$this->message('success', '删除数据操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
	}

}
