<?php

/**
 * 发布信息
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_newthread extends voa_c_frontend_thread_base
{

	public function execute()
	{
		if ($this->_is_post()) {
			$this->_submit();
			return false;
		}
		$thread = array();
		$this->view->set('form_action', '/frontend/thread/new?handlekey=post');
		$this->view->set('thread', $thread);
		$this->view->set('ac', $this->action_name);
		$this->view->set('navtitle', '发表话题');
		$this->_output('mobile/thread/post');
	}

	/**
	 * 发布话题
	 * @return boolean
	 */
	protected function _submit()
	{
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin();

			$uda = &uda::factory('voa_uda_frontend_thread_add');
			$params = $this->request->postx();
			$params['uid'] = startup_env::get('wbs_uid');
			$params['username'] = startup_env::get('wbs_username');
			$threads = array();

			if (! $uda->execute($params, $threads)) {
				$this->_error_message($uda->errmsg);
			}

			// 提交事务
			voa_uda_frontend_transaction_abstract::s_commit();

			$this->_success_message('操作成功', "/frontend/thread/viewthread/tid/{$threads['tid']}");
		} catch (help_exception $e) {
			// 事务回滚
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_error_message($e->getMessage());
			return false;
		}
	}
}
