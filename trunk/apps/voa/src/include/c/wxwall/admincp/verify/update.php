<?php
/**
 * voa_c_wxwall_admincp_verify_update
 * 微信前端/管理:内容审核
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_verify_update extends voa_c_wxwall_admincp_verify_base {

	public function execute() {

		$setStatus = $this->request->get('setstatus');
		$setStatus = rintval($setStatus, false);

		$wwp_id = $this->request->get('wwp_id');
		$wwp_id = rintval($wwp_id, false);

		$postStatus = voa_h_wxwall::$post_status;

		$setStatusId = abs($setStatus);

		if (!isset($postStatus[$setStatusId]) || !$wwp_id) {
			$this->_message('error', '设置上墙状态发生错误');
		}


		$newStatus	=	false;
		if ( $setStatus == -4 ) {
			/** 删除 */
			$newStatus	=	voa_d_oa_wxwall_post::STATUS_REMOVE;
		} elseif ( $setStatus == -2 ) {
			/** 下墙 */
			$newStatus	=	voa_d_oa_wxwall_post::STATUS_REFUSE;
		} elseif ( $setStatus == -1 ) {
			/** 上墙 */
			$newStatus	=	voa_d_oa_wxwall_post::STATUS_APPROVE;
		} elseif ( $setStatus == -3 ) {
			/** 上墙 */
			$newStatus	=	voa_d_oa_wxwall_post::STATUS_APPROVE;
		}

		if ($newStatus !== false) {
			$serv = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
			if ($newStatus == voa_d_oa_wxwall_post::STATUS_REMOVE) {
				$serv->delete_by_ids($wwp_id);
			} else {
				$serv->update(array('wwp_status' => $newStatus), array('wwp_id' => $wwp_id));
			}
		}

		if ( $this->_is_ajax ) {
			echo rjson_encode(true);
			exit;
		} else {
			$this->_message('success', '设置内容状态操作完毕', get_referer($this->wxwall_admincp_url($this->_module, 'list', array('viewstatus' => ''), $setStatusId)));
		}

	}

}
