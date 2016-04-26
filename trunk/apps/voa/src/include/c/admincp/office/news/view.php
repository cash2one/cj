<?php
/**
 * voa_c_admincp_office_news_view
* 企业后台/微办公管理/新闻公告/查看
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_news_view extends voa_c_admincp_office_news_base {

	public function execute() {

		$ne_id = $this->request->get('ne_id');
		try {
			// 读取数据
			$news = array();
			$uda = &uda::factory('voa_uda_frontend_news_view');
			$uda->get_view(array('ne_id' => $ne_id, 'm_uid' => 0), $news);

		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		//获取已有的阅读权限（人员和部门）
		$default_users = '';
		$default_departments = '';
		if ($news['is_all'] == 0) {
			if (!empty($news['rights'])) {
					$m_uids = array_column($news['rights'], 'm_uid');
					$cd_ids = array_column($news['rights'], 'cd_id');
					$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
					$users = $serv_m->fetch_all_by_ids($m_uids);
					$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
					$depms = $serv_d->fetch_all_by_key($cd_ids);
					$default_users = implode(',', array_column($users, 'm_username'));
					$default_departments = implode(',', array_column($depms, 'cd_name'));
			}
		}

		//  点赞数据的获取和处理
		$uda_like = &uda::factory("voa_uda_frontend_news_like_list");
		// 获取点赞列表数据集
		if (! $uda_like->execute(array('ne_id' => $ne_id), $likes)) {
			$this->_error_message($uda_like->errmsg);
			return true;
		}

		$like_lists = count($likes);
		$total_like = $news['num_like'];// 点赞总数
		// 分页参数设置
		$likes_multi = '';
		if ($likes > 0) {
			$perpage = $uda_like->get_perpage();
			$page = $uda_like->get_page();

			// 分页配置
			$pager_options = array(
				'total_items' => $like_lists,
				'per_page' => $perpage,
				'current_page' => $page,
				'show_total_items' => true
			);
			$likes_multi = pager::make_links($pager_options);
		}
		// 数据整理
		//var_dump($likes);die;
		foreach ($likes as $key => &$val) {
			$users = voa_h_user::get($val['m_uid']);
			$val['m_username'] = $users['m_username'];// 姓名
			if($val['description'] == 2) $val['description'] ="点赞 +1";
			elseif($val['description'] == 1) $val['description'] = "点赞 -1";
		}
		//点赞部分 进行注入模板数据
		$this->view->set('likes', $likes);
		$this->view->set('likes_multi', $likes_multi);
		$this->view->set('like_lists', $like_lists); //总数
		// -----

		// 注入模板变量
		$this->view->set('news', $news);
		$this->view->set('categories', $this->_categories);
		$this->view->set('default_users', $default_users);
		$this->view->set('default_departments', $default_departments);
		$this->view->set('edit_url', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('ne_id' => $ne_id)));
		$this->view->set('delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('ne_id' => $ne_id)));
		// 输出模板
		$this->output('office/news/view');

	}

}
