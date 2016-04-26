<?php
/**
 * 发布新秘密
 * $Author$
 * $Id$
 */

class voa_c_frontend_secret_new extends voa_c_frontend_secret_base {

	public function execute() {
		if ($this->_is_post()) {
			$subject = trim($this->request->get('subject'));
			$message = trim($this->request->get('message'));
			if (empty($subject) || empty($message)) {
				$this->_error_message('主题和内容不能为空');
			}

			$serv = &service::factory('voa_s_oa_secret', array('pluginid' => startup_env::get('pluginid')));
			$serv_p = &service::factory('voa_s_oa_secret_post', array('pluginid' => startup_env::get('pluginid')));
			/** 数据入库 */
			try {
				$serv->begin();

				/** 申请信息入库 */
				$secret = array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'st_subject' => $subject,
					'p_status' => voa_d_oa_secret::STATUS_NORMAL
				);
				$st_id = $serv->insert($secret, true);
				if (empty($st_id)) {
					throw new Exception('秘密新增失败');
				}

				/** 内容信息入库 */
				$newpost = array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'st_id' => $st_id,
					'stp_subject' => $subject,
					'stp_message' => $message,
					'stp_first' => voa_d_oa_secret_post::FIRST_YES,
				);
				$serv_p->insert($newpost);

				$serv->commit();
			} catch (Exception $e) {
				$serv->rollback();
				/** 如果 $id 值为空, 则说明入库操作失败 */
				$this->_error_message('秘密新增失败');
			}

			$this->_success_message('秘密发布成功', "/secret/view/{$p_id}");
		}

		$this->view->set('form_action', "/secret/new?handlekey=post");
		$this->view->set('ac', $this->action_name);
		$this->view->set('refer', get_referer());
		$this->view->set('secret', array());
		$this->view->set('navtitle', '新秘密');

		$this->_output('secret/post');
	}

}
