<?php
/**
 * 对报告内容的评论
 * $Author$
 * $Id$
 */

class voa_c_frontend_dailyreport_reply extends voa_c_frontend_dailyreport_base {

	public function execute() {
		// 如果不是 post 提交
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		// 报告信息入库
		$uda = &uda::factory('voa_uda_frontend_dailyreport_insert');
		$post = array();
		$dailyreport = array();
		if (! $uda->dailyreport_reply($post, $dailyreport)) {
			$this->_error_message($uda->error);
			return false;
		}

		// 发送消息通知
		$uda->send_wxqymsg_news($this->session, $dailyreport, 'reply', startup_env::get('wbs_uid'));

		/**
		 * 提示操作成功
		 */
		$this->_success_message('评论添加成功', "/dailyreport/view/{$post['dr_id']}");
	}
}
