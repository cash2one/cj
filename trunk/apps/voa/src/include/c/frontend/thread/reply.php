<?php

/**
 * 对评论信息的回复操作
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_reply extends voa_c_frontend_thread_base
{

	public function execute()
	{
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin();

			$params = $this->request->getx();
			$post = array();

			$uda = &uda::factory('voa_uda_frontend_thread_post_add');
			if (! $uda->execute($params, $post)) {
				$this->_error_message($uda->errmsg);
				return true;
			}

			if (! empty($post)) {

				$thread = array();
				$p_uid = $params['p_uid'];
				// 查询话题
				$uda_thread = &uda::factory('voa_uda_frontend_thread_view');
				if (! $uda_thread->execute(array(
					'tid' => $params['tid']
				), $thread)) {
					$this->_error_message($uda_thread->errmsg);
					return true;
				}


				$send_msg = false;
				if (!empty($p_uid)) {
					$thread['uid'] = $params['p_uid']; // 接收人uid
					$thread['username'] = $post['username']; // 设置发送人姓名
					$send_msg = true;
				} elseif ($thread['uid'] != 0) {
					$thread['username'] = $post['username']; // 设置发送人姓名
					$send_msg = true;
				}


				if ($send_msg) {
					// 发送消息通知
					$uda_thread->send_msg($thread, 'reply', startup_env::get('wbs_uid'), $this->session);
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

		// 提示入库成功
		$this->_success_message('回复操作成功', '/frontend/thread/viewthread/tid/' . $post['tid']);
	}
}

