<?php
/**
 * 对备忘内容的评论(暂时无用)
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_reply extends voa_c_frontend_vnote_base {

	public function execute() {
		/** 如果不是 post 提交 */
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		/** 备忘信息入库 */
		$uda = &uda::factory('voa_uda_frontend_vnote_insert');
		$post = array();
		if (!$uda->vnote_reply($post)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 提示操作成功 */
		$this->_success_message('批注操作成功', "/vnote/view/{$post['vn_id']}");
	}
}
