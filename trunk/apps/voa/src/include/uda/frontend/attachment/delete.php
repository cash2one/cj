<?php
/**
 * voa_uda_frontend_attachment_delete
 * 统一数据访问/公共附件表/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_attachment_delete extends voa_uda_frontend_attachment_base {

	/**
	 * 临时删除指定附件id的附件（允许多个）
	 * 只标记临时删除，不标记删除数据表数据，不删除物理文件
	 * @param number|array $at_ids
	 * @return boolean
	 */
	public function delete($at_ids) {
		if (is_numeric($at_ids)) {
			$at_ids = array($at_ids);
		}

		// TODO !可能涉及将物理文件做一个转存的操作?

		$serv = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		// 标记临时删除
		$serv->update(array('at_status' => voa_d_oa_common_attachment::STATUS_REMOVE), array('at_id' => $at_ids));

		return true;
	}

	/**
	 * 实际删除指定附件id的附件（彻底真实删除，允许多个）
	 * @param number|array $at_ids
	 * @return boolean
	 */
	public function delete_real($at_ids) {
		if (is_numeric($at_ids)) {
			$at_ids = array($at_ids);
		}

		// TODO ！未来可能涉及删除文件的物理路径动作

		$serv = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		$serv->delete_by_ids($at_ids);

		return true;
	}

}
