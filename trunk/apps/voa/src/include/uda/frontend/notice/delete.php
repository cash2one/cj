<?php
/**
 * voa_uda_frontend_notice_delete
 * 统一数据访问/通知公告/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_notice_delete extends voa_uda_frontend_notice_base {

	public function notice($nt_ids) {
		$serv = &service::factory('voa_s_oa_notice');
		$serv_attachment = &service::factory('voa_s_oa_notice_attachment');

		// @ 待删除的公共附件表id
		$at_ids = array();

		try {

			// 开始删除过程
			$serv->begin();

			// @ 删除主表记录
			$serv->delete_by_ids($nt_ids);

			// 找到通知公告附件对应公共附件的id
			$attach_list = $serv_attachment->fetch_by_nt_id($nt_ids);
			// 存在附件
			if ($attach_list) {
				// @ 删除通知公告附件表
				$serv_attachment->delete_by_conditions(array('nt_id' => array($nt_ids)));

				// 找到公共附件表at_id
				foreach ($attach_list as $_data) {
					$at_ids[] = $_data['at_id'];
				}
				unset($attach_list);
			}

			// 提交删除过程
			$serv->commit();

		} catch (Exception $e) {
			$serv->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		if ($at_ids) {
			// 删除公共附件表
			$uda_attachment_delete = &uda::factory('voa_uda_frontend_attachment_delete');
			$uda_attachment_delete->delete($at_ids);
		}

		return true;

	}

}
