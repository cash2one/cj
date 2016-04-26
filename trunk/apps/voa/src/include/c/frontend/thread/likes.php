<?php

/**
 * 点赞
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_likes extends voa_c_frontend_thread_base
{

	public function execute()
	{
		$tid = rintval($this->request->get('tid'));
		// 用户是否点赞
		$uda_likes = &uda::factory('voa_uda_frontend_thread_likes_list');
		$likes = array();
		$uda_likes->execute(array(
					'tid' => $tid,
					'uid' => startup_env::get('wbs_uid')
				), $likes);
		if (empty($likes)) {

			try {
				// 事务开始
				voa_uda_frontend_transaction_abstract::s_begin();

				// 添加点赞记录
				$thread_like = array();
				$uda_add_likes = &uda::factory('voa_uda_frontend_thread_likes_add');
				if (! $uda_add_likes->execute(array(
							'tid' => $tid,
							'uid' => startup_env::get('wbs_uid'),
							'username' => startup_env::get('wbs_username')
						), $thread_like)) {
					$this->_error_message($uda_add_likes->errmsg);
				}

				if (! empty($thread_like)) {
					// 查询点赞话题
					$uda_thread = &uda::factory('voa_uda_frontend_thread_view');
					$thread = array();
					if (! $uda_thread->execute(array(
								'tid' => $tid
							), $thread)) {
						$this->_error_message($uda_thread->errmsg);
						return true;
					}

					//给官方话题点赞不用发送消息
					if ($thread['uid'] != 0) {
						// 设置点赞人
						$thread['username'] = $thread_like['username'];
						// 发送消息通知
						$uda_add_likes->send_msg($thread, 'likes', startup_env::get('wbs_uid'), $this->session);
					}
				}

				// 提交事务
				voa_uda_frontend_transaction_abstract::s_commit();
			} catch (help_exception $e) {
				// 事务回滚
				voa_uda_frontend_transaction_abstract::s_rollback();
				$this->_error_message($e->getMessage());
				return false;
			}
		}
	}
}
