<?php

/**
 * 话题详情|用户点赞
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_viewthread extends voa_c_frontend_thread_base
{

	public function execute()
	{
		try {
		    //同事社区配置信息
			$p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa');

			$tid = rintval($this->request->get('tid'));
			// 读取主题
			$uda_thread = &uda::factory('voa_uda_frontend_thread_view');
			$thread = array();
			if (! $uda_thread->execute(array(
						'tid' => $tid
					), $thread)) {
				$this->_error_message($uda_thread->errmsg);
				return true;
			}

			// 读取评论
			$uda_post = &uda::factory('voa_uda_frontend_thread_post_list');
			$posts = array();
			$conds = array(
				'tid' => $tid,
				'ppid' => 0,
				'first' => 1
			);
			if (! $uda_post->execute($conds, $posts)) {
				$this->_error_message($uda_post->errmsg);
				return true;
			}

			// 读取点赞
			$uda_likes = &uda::factory('voa_uda_frontend_thread_likes_list');
			$likes = array();
			if (! $uda_likes->execute(array(
						'tid' => $tid,
						'page' => 1,
						'perpage' => 5
					), $likes)) {
				$this->_error_message($uda_post->errmsg);
				return true;
			}

			// 查看用户是否点赞
			$uda_islike = &uda::factory('voa_uda_frontend_thread_likes_list');
			$islike = array();
			if (! $uda_islike->execute(array(
						'tid' => $tid,
						'uid' => startup_env::get('wbs_uid')
					), $islike)) {
				$this->_error_message($uda_post->errmsg);
				return true;
			}
		} catch (help_exception $e) {
			$this->_error_message($e->getMessage());
			return true;
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

		// 读取用户信息
		$serv_m = &service::factory('voa_s_oa_member');
		$users = $serv_m->fetch_all_by_ids($uids);
		voa_h_user::push($users);

		// 附件
		$ids = explode(",", $thread['attach_id']);
		$attach = array();
		foreach ($ids as $_v) {
			if (! empty($_v)) {
				$attach[]['aid'] = $_v;
			}
		}

		$this->view->set('attachs', $attach); // 附件
		$this->view->set('thread', $thread);
		$this->view->set('likes', $likes); // 点赞
		$this->view->set('islike', $islike); // 用户是否点赞
		$this->view->set('navtitle', $thread['subject']);
		$this->view->set('$p_sets',$p_sets);

		// 模板
		$this->_output('mobile/thread/view');
	}
}

