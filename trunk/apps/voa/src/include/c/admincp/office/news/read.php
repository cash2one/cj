<?php

/**
 * voa_c_admincp_office_news_read
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_news_read extends voa_c_admincp_office_news_base {

	const READ = 1; // 已读
	const UNREAD = 2; //未读

	public function execute() {

		//判断新闻公告ID是否合法
		$ne_id = $this->request->get('ne_id');
		if (!$ne_id) {
			$this->message('error', '新闻公告ID不合法');

			return false;
		}
		// 取得公告
		$s_news = &service::factory('voa_s_oa_news');
		$news = $s_news->get($ne_id);
		if (!$news) {
			$this->message('error', '新闻公告不存在');

			return false;
		}
		$news['title'] = rhtmlspecialchars($news['title']);
		$news['updated'] = rgmdate($news['updated'], 'Y-m-d H:i:s');

		$uda = &uda::factory('voa_uda_frontend_news_read');
		//获取可读人员总数
		$user_total = $uda->count_user_total($ne_id);
		//获取未读人员总数
		$un_num = $uda->count_real_unusers($ne_id);
		//获取已读人员总数
		$num = $uda->count_real_read_users($ne_id);
		//获取类型
		$type = (int)$this->request->get('type');
		if (empty($type)) {
			$type = self::READ;
		}
		//默认显示已读人员页面
		if ($type == self::UNREAD) {
			$this->get_unread_list($uda, $ne_id);
		} else {
			$this->get_read_list($uda, $ne_id);
		}
		// 注入模板变量
		$this->view->set('read_url', $this->cpurl($this->_module, $this->_operation, 'read', $this->_module_plugin_id, array(
			'ne_id' => $ne_id,
			'type' => self::READ,
		)));
		$this->view->set('unread_url', $this->cpurl($this->_module, $this->_operation, 'read', $this->_module_plugin_id, array(
			'ne_id' => $ne_id,
			'type' => self::UNREAD,
		)));
		$this->view->set('type', $type);
		$this->view->set('news', $news);
		$this->view->set('num', $num);
		$this->view->set('un_num', $un_num);
		$this->view->set('user_total', $user_total);
		//输出模板
		$this->output('office/news/read');

		return true;
	}

	/**
	 * 获取已读人员列表
	 * @param string $uda_read
	 * @param int    $ne_id
	 * */
	public function get_read_list($uda_read, $ne_id) {

		//设置页码和每页显示数目
		$page = (int)$this->request->get('page');
		$page <= 0 && $page = 1;
		$limit = 10;
		// 取得已读人员列表
		$result = array();
		$uda_read->list_users(array(
			'ne_id' => $ne_id,
			'page' => $page,
			'limit' => $limit,
		), $result);
		$users = $result['users'];
		// 分页信息
		$pagerOptions = array(
			'total_items' => $result['total'],
			'per_page' => $limit,
			'current_page' => $this->request->get('page'),
			'show_total_items' => true,
		);
		$multi = pager::make_links($pagerOptions);
		pager::resolve_options($pagerOptions);
		//注入变量
		$this->view->set('users', $users);
		$this->view->set('multi', $multi);
	}

	/**
	 * 获取未读人员列表
	 * @param string $uda_read
	 * @param int    $ne_id
	 * */
	public function get_unread_list($uda_read, $ne_id) {

		//设置页码和每页显示数目
		$page = (int)$this->request->get('w_page');
		$page <= 0 && $page = 1;
		$limit = 10;
		// 取得未读人员列表
		$result = array();
		$uda_read->get_unread_users(array(
			'ne_id' => $ne_id,
			'page' => $page,
			'limit' => $limit,
		), $result);
		$users = $result['users'];
		// 分页信息
		$pagerOptions = array(
			'total_items' => $result['total'],
			'per_page' => $limit,
			'current_page' => $this->request->get('w_page'),
			'url_var' => 'w_page',
			'show_total_items' => true,
		);
		$multi = pager::make_links($pagerOptions);
		pager::resolve_options($pagerOptions);
		// 注入变量
		$this->view->set('un_users', $users);
		$this->view->set('un_multi', $multi);
	}
}
