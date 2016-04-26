<?php

/**
 * 个人工作台首页
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_index extends voa_c_frontend_thread_base
{
	// 分页查询相关
	protected $_start;

	protected $_perpage;

	protected $_page;

	public function execute()
	{
		//读同事社区配置缓存
		$p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa');

		$acs = array(
			'hot',
			'choice',
			'all',
			'mine'
		);
		$ac = (string) $this->request->get('ac');
		if (! in_array($ac, $acs)) {
			$ac = 'hot';
		}

		//根据操作类型，设置标题
		switch ($ac) {
			case 'hot':
				$this->view->set('navtitle', '热门话题');
				break;
			case 'choice':
				$this->view->set('navtitle', '精选话题');
				break;
			case 'mine':
				$this->view->set('navtitle', '我的社区');
				break;
			default:
				$this->view->set('navtitle', '所有话题');
		}

	    //我的社区，查询我的话题数
		if ($ac == 'mine') {
			$uda = &uda::factory('voa_uda_frontend_thread_list');
			$threads = array();
			$conds = array();
			if (! $uda->execute(array(
						'uid' => startup_env::get('wbs_uid')
					), $threads)) {
				$this->_error_message($uda->errmsg);
				return true;
			}
			$this->view->set('total', $uda->get_total());
			$this->view->set('uid', startup_env::get('wbs_uid'));
			$this->view->set('username', startup_env::get('wbs_username'));
		}

		$this->view->set('ac', $ac);
		/**
		 * 模板
		 */
		$tpl = 'mobile/thread/index';
		$this->_output($tpl);
	}
}
