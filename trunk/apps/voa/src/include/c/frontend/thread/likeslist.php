<?php

/**
 * 点赞列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_likeslist extends voa_c_frontend_thread_base
{

	public function execute()
	{
		$tid = rintval($this->request->get('tid'));

		// 读取主题
		$uda_thread = &uda::factory('voa_uda_frontend_thread_view');
		$thread = array();
		$conds = array();
		$conds['perpage'] = rintval($this->request->get('limit'));
		$conds['page'] = rintval($this->request->get('page'));
		$conds['tid'] = $tid;

		if (! $uda_thread->execute($conds, $thread)) {
			$this->_error_message($uda_thread->errmsg);
			return true;
		}

		$this->view->set('thread', $thread);
		$this->view->set('navtitle', '有' . $thread['likes'] . '个人觉得这个话题很赞');
		// 模板
		$this->_output('mobile/thread/likeslist');
	}
}
