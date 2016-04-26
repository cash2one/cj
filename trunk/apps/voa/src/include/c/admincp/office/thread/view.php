<?php

/**
 * voa_c_admincp_office_thread_view
 * 企业后台/同事社区/社区话题详情|删除评论
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_thread_view extends voa_c_admincp_office_thread_base
{

	public function execute()
	{
		$tid = rintval($this->request->get('tid'));
		$ac = $this->request->get('ac');

		// 删除话题评论
		if ($ac == 'delete') {
			$delete = $this->request->post('delete');
			$pid = rintval($this->request->get('pid'));
			$this->_delete($delete, $tid, $pid);
		}

		// 读取社区主题
		$uda_thread = &uda::factory('voa_uda_frontend_thread_view');
		$thread = array();
		if (! $uda_thread->execute(array(
			'tid' => $tid
		), $thread)) {
			$this->_error_message($uda_thread->errmsg);
			return true;
		}

		// 读取评论列表
		$uda_post = &uda::factory('voa_uda_frontend_thread_post_list');
		$posts = array();
		$conds = array(
			'tid' => $tid
		);
		if (! $uda_post->execute($conds, $posts)) {
			$this->_error_message($uda_post->errmsg);
			return true;
		}

		// 读取点赞列表
		$uda_likes = &uda::factory('voa_uda_frontend_thread_likes_list');
		$likes = array();
		if (! $uda_likes->execute(array(
			'tid' => $tid
		), $likes)) {
			$this->_error_message($uda_post->errmsg);
			return true;
		}
		$likes_total = count($likes); // 点赞总数
		$likes_multi = '';
		if ($likes_total > 0) {
			$perpage = $uda_post->get_perpage();
			$page = $uda_post->get_page();

			// 分页配置
			$pager_options = array(
				'total_items' => $likes_total,
				'per_page' => $perpage,
				'current_page' => $page,
				'show_total_items' => true
			);
			$likes_multi = pager::make_links($pager_options);
		}

		// 整理数据
		$uids = array();
		foreach ($posts as $_k => $_p) {
			$uids[$_p['uid']] = $_p['uid'];
			if (voa_d_oa_thread_post::FIRST_YES == $_p['first']) {
				$thread = array_merge($_p, $thread);
				unset($posts[$_k]);
				continue;
			}
		}

		$posts_total = count($posts); // 评论总数
		$posts_multi = '';
		if ($posts_total > 0) {
			$perpage = $uda_post->get_perpage();
			$page = $uda_post->get_page();

			// 分页配置
			$pager_options = array(
				'total_items' => $posts_total,
				'per_page' => $perpage,
				'current_page' => $page,
				'show_total_items' => true
			);
			$posts_multi = pager::make_links($pager_options);
		}

		//获取附件
		$imgUrl = explode(",",$thread['attach_id']);
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		$img_list = array();
		foreach ($imgUrl as $_k => $img) {
			$img_list[] = $scheme.$sets['domain'].'/attachment/read/'. $img;
		}

		$this->view->set('img_list', $img_list);//附件
		$this->view->set('thread', $thread); // 话题
		$this->view->set('posts', $posts); // 评论
		$this->view->set('postsCount', $posts_total); // 评论数
		$this->view->set('likes', $likes); // 点赞
		$this->view->set('likesCount', $likes_total); // 点赞数
		$this->view->set('posts_multi', $posts_multi);
		$this->view->set('likes_multi', $likes_multi);
		// 删除评论url
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array(
			'ac' => 'delete',
			'tid' => $tid,
			'pid' => ''
		)));
		$this->output('office/thread/view');
	}

	/**
	 * 删除话题评论
	 *
	 * @param array $delete
	 * @param int $tid
	 * @param int $pid
	 * @return boolean
	 */
	protected function _delete($delete, $tid, $pid)
	{
		$ids = 0;
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($pid) {
			$ids = rintval($pid, false);
			if (! empty($ids)) {
				$ids = array(
					$ids
				);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的' . $this->_module_plugin['cp_name']);
		}
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin();
			$uda = &uda::factory('voa_uda_frontend_thread_post_delete');
			$result = array();
			if (! $uda->execute(array(
						'pid' => $ids,
						'tid' => $tid
					), $result)) {
				$this->_error_message($uda->errmsg);
				return true;
			}

			// 提交事务
			voa_uda_frontend_transaction_abstract::s_commit();
		} catch (help_exception $e) {
			// 事务回滚
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_error_message($e->getMessage());
			return true;
		}
		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array(
			'ac' => 'view',
			'tid' => $tid
		)));
	}
}
