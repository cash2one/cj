<?php
/**
 * voa_uda_frontend_footprint_delete
 * 统一数据访问/销售轨迹应用/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_footprint_delete extends voa_uda_frontend_footprint_base {

	public function delete($fp_ids, $shard_key = array()) {
		if (is_numeric($fp_ids)) {
			$fp_ids = array($fp_ids);
		}

		$serv = &service::factory('voa_s_oa_footprint', $shard_key);
		$serv_attachment = &service::factory('voa_s_oa_footprint_attachment', $shard_key);
		$serv_mem = &service::factory('voa_s_oa_footprint_mem', $shard_key);

		// @ 待删除的公共附件id
		$at_ids = array();
		try {

			// 开始过程
			$serv->begin();

			// @ 删除主表
			$serv->delete_by_ids($fp_ids);

			// @ 删除分享表
			$serv_mem->delete_by_fp_ids($fp_ids);

			// 找到附件
			$attach_list = $serv_attachment->fetch_by_fp_id($fp_ids);
			// 存在附件文件，则找到公共附件id 并删除附件文件
			if ($attach_list) {
				foreach ($attach_list as $attach) {
					$at_ids[] = $attach['at_id'];
				}

				// @ 删除轨迹的附件记录
				$serv_attachment->delete_by_conditions(array('fp_id' => $fp_ids));

			}

			// 提交过程
			$serv->commit();

		} catch (Exception $e) {
			$serv->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		if ($at_ids) {
			// @ 根据公共附件表id删除附件
			$uda_attachment_delete = &uda::factory('voa_uda_frontend_attachment_delete');
			$uda_attachment_delete->delete($at_ids);
		}

		return true;

	}

}
