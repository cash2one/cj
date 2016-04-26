<?php
/**
 * voa_c_admincp_office_news_edit
 * 企业后台/微办公管理/新闻公告/编辑
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_news_edit extends voa_c_admincp_office_news_base {

	public function execute() {

		$ne_id = $this->request->get('ne_id');

		$uda = &uda::factory('voa_uda_frontend_news_update');
		$news = $uda->get_news($ne_id);
		//获取已有的阅读权限（人员和部门）
		$default_users = array();
		$default_departments = array();
		if ($news['is_all'] == 0) {
			if (!empty($news['rights'])) {
				$m_uids = array_column($news['rights'], 'm_uid');
				$cd_ids = array_column($news['rights'], 'cd_id');
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
				$depms = $serv_d->fetch_all_by_key($cd_ids);
				foreach ($news['rights'] as $right) {
					//获取部门
					if ($right['cd_id'] !=0) {
						$default_departments[] = array(
							'id' => $right['cd_id'],
							'cd_name' => $depms[$right['cd_id']]['cd_name'],
							'isChecked' => (bool)true,
						);
					}
					//获取人员
					if ($right['m_uid'] !=0) {
						$default_users[] = array(
							'm_uid' => $right['m_uid'],
							'm_username' => $users[$right['m_uid']]['m_username'],
							'selected' => (bool)true,
						);
					}
				}
			}
		}
		//审批人员
		$default_check = array();
		if ($news['is_check'] != 0) {
			if (!empty($news['check'])) {
				$options['user_id'] = $this->_user['ca_id'];
				$m_uids = array_column($news['check'], 'm_uid');
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				foreach ($news['check'] as $right) {
					//获取人员
					if ($right['m_uid'] !=0) {
						$default_check[] = array(
							'm_uid' => $right['m_uid'],
							'm_username' => $users[$right['m_uid']]['m_username'],
							'selected' => (bool)true,
						);
					}
				}
			}
		}
		//作者
		$default_author = array();
		if($news['m_uid'] != '') {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch($news['m_uid']);
			//获取人员
			if($users) {
				$default_author[] = array(
					'm_uid' => $news['m_uid'],
					'm_username' => $users['m_username'],
					'selected' => (bool)true,
				);
			}

		}
		if ($this->_is_post()) {
			$data_post = $this->request->postx();
			$data = array();
			$this->data_form($data_post, $data);
			$news = array();
			try {
				// 读取数据
				$options['user_id'] = (int)$data['author'];
				$uda->edit_news($data,  $news, $options);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
			$is_publish = $data_post['is_publish'];
			$is_push = isset($data_post['is_push']) ? $data_post['is_push'] : 0;
			if (!$is_publish) { //如果是发布，则跳转到列表页
				if($news['is_check'] == 1) {
					$this->_to_queue($news);
					$this->message('success', '审核已发送成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
					die;
				}
				$this->message('success', '保存草稿成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
			} else { //如果是保存草稿，则跳转到编辑页
				if($is_push) {
					$this->_to_queue($news);
				}
				$this->message('success', '发布新闻公告成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
			}
		}

		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name').'.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array('toolbars' => '_mobile', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => isset($news['content']) ? $news['content'] : '', 'elementPathEnabled' => false);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		// 获取分类
		$uda_cat = &uda::factory('voa_uda_frontend_news_category');
		$categories = $uda_cat->list_categories();

		// 注入模板变量
		$this->view->set('news', $news);
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('categories', $categories);
		$this->view->set('default_users', rjson_encode(array_values($default_users)));
		$this->view->set('default_departments', rjson_encode(array_values($default_departments)));
		$this->view->set('default_check',rjson_encode(array_values($default_check)));
		$this->view->set('default_author',rjson_encode(array_values($default_author)));
		// 输出模板
		$this->output('office/news/edit');

	}

	/**
	 * 数据格式化
	 * @param $request
	 * @param $result
	 */
	private function data_form($request, &$result) {
		if($request['is_publish'] == 1) {
			$request['is_check'] = 0;
			$request['check_summary'] = '';
		}
		$result = $request;
		return true;
	}

}
