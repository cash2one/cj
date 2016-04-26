<?php
/**
 * voa_c_admincp_office_train_base
 * 企业后台/微办公管理/培训/基本控制器
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_train_base extends voa_c_admincp_office_base {

	protected $_p_sets = array();
	protected $_uda_base = null;
	protected  $url;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		//FIXME ！！！涉及指定应用更新问题
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.train.setting', 'oa');

		$this->url = 'http://'.$_SERVER['HTTP_HOST'];

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}


	/**
	 * 添加/编辑文章
	 * @param number $ta_id 文章id
	 * @param string $is_new 新增=true，编辑=false
	 */
	protected function _article_edit($ta_id = 0, $is_new = false) {

		$ta_id = rintval($ta_id);
		if (!$is_new && $ta_id <= 0) {
			// 如果是编辑，但ta_id为空
			$this->message('error', '指定文章不存在');
		}
		$uda = &uda::factory('voa_uda_frontend_train_action_articleedit');
		$uda_view = &uda::factory('voa_uda_frontend_train_action_articleview');
		$article = array();
		$article_former = array();
		$tc_id = 0;
		$existed_rights = array();  //已有权限的部门和人
		$existed_range_limit = array();  //选择的目录权限

		if ($is_new) {
			// 新增
			$ta_id = 0;
		} else {
			// 编辑
			if ($ta_id <= 0) {
				$this->message('error', '指定文章不存在');
			}

			$article_former = $uda_view->view($ta_id);
			if (empty($article_former)) {
				// 如果是编辑，但文章内容不存在
				$this->message('error', '指定文章不存在或已被删除');
			}
			$tc_id = $article_former['tc_id'];
			$article_former['created'] = rgmdate($article_former['created'],'Y-m-d H:i:s');
			$article_former['updated'] = rgmdate($article_former['updated'],'Y-m-d H:i:s');

			$existed_rights = $this->__get_rights_info($article_former['contacts'], $article_former['deps']);
			$existed_range_limit = $this->__get_right_limit($article_former['tc_id']);
		}

		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'content';

		$article['title'] = trim($this->request->get('title'));
		$article['author'] = trim($this->request->get('author'));
		$article['content'] = trim($this->request->get('content'));
		$article['tc_id'] = trim($this->request->get('tc_id'));
		$article['contacts'] = $this->request->get('contacts');
		$article['deps'] = $this->request->get('deps');

		if ($this->_is_post()) {
			$uda_validator = &uda::factory('voa_uda_frontend_train_validator');
			if (!$uda_validator->article_title($article['title'])) {  //检查标题长度是否合法
				$this->message('error', $uda_validator->error);
			}
			if (!$uda_validator->article_author($article['author'])) {  //检查作者名字长度是否合法
				$this->message('error', $uda_validator->error);
			}
			$new_article = array();

			if (!$uda->edit($ta_id, $article )) {
				$this->message('error', $uda->error);
			}
			if ($this->request->get('send')) { //发送消息提醒
				$article_new = $uda_view->view($article['ta_id']);
				$uda_notice = &uda::factory('voa_uda_frontend_train_wxqynotice');
				$uda_notice->send_wxqy_notice($article_new, $this->session);
			}
			if ($is_new) {
				$this->message('success', '新增文章成功', $this->cpurl($this->_module, $this->_operation, 'atlist', $this->_module_plugin_id), false);
			} else {
				$this->message('success', '编辑文章成功', $this->cpurl($this->_module, $this->_operation, 'atedit', $this->_module_plugin_id, array('ta_id' => $ta_id)), false);
			}
		}

		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name').'.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array('toolbars' => '_all', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => isset($article_former['content']) ? $article_former['content'] : '', 'elementPathEnabled' => false);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		// 取得目录列表
		$uda_categorylist = &uda::factory('voa_uda_frontend_train_action_categorylist');
		$categories = $uda_categorylist->get_all_categories();  //所有目录
		$categories_is_all = $uda_categorylist->list_categories_by_is_all(); //所有人可查看的目录ID
		$existed_rights_json = rjson_encode($existed_rights);
		$existed_range_limit_json = rjson_encode($existed_range_limit);

		if (empty($article_former['deps'])) {
			$article_former['deps'] = array();
		}
		if (empty($article_former['contacts'])) {
			$article_former['contacts'] = array();
		}
		$this->view->set('cd_ids', rjson_encode(array_values($article_former['deps'])));
		$this->view->set('m_uids', rjson_encode(array_values($article_former['contacts'])));
		$this->view->set('article', $article_former);
		$this->view->set('ta_id', $ta_id);
		$this->view->set('tc_id', $tc_id);
		$this->view->set('categories_is_all', rjson_encode($categories_is_all));
		$this->view->set('existed_rights', $existed_rights_json);
		$this->view->set('existed_range_limit_json', $existed_range_limit_json);
		$this->view->set('URL', $this->url);
		$this->view->set('categories', $categories);
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $is_new ? 'atadd' : 'atedit', $this->_module_plugin_id, array('ta_id' => $ta_id)));
		$this->view->set('addCategoryUrl', $this->cpurl($this->_module, $this->_operation, 'cgadd', $this->_module_plugin_id));

		$this->output('office/train/article_form');

		return;
	}


	/**
	 * 添加/编辑目录
	 * @param number $tc_id 目录id
	 * @param string $is_new 新增=true，编辑=false
	 */
	protected function _category_edit($tc_id = 0, $is_new = false) {

		$tc_id = rintval($tc_id);
		if (!$is_new && $tc_id <= 0) {
			// 如果是编辑，但ta_id为空
			$this->message('error', '指定目录不存在');
		}
		$uda = &uda::factory('voa_uda_frontend_train_action_categoryedit');

		$category = array();
		$category_former = array();
		$category['title'] = trim($this->request->get('title'));
		$category['contacts'] = $this->request->get('contacts');
		$category['deps'] = $this->request->get('deps');
		if(empty($category['contacts'])) {
			$category['contacts'] = array();
		}
		if (empty($category['deps'])) {
			$category['deps'] = array();
		}

		if ($is_new) {// 新增
			$tc_id = 0;
		} else {// 编辑
			if ($tc_id <= 0) {
				$this->message('error', '指定目录不存在');
			}
			$category_former = $uda->get_category_by_pk($tc_id);
			if (empty($category_former)) {// 如果是编辑，但目录内容不存在
				$this->message('error', '指定目录不存在 或 已被删除');
			}
			$this->__get_rights_info($category_former['contacts'], $category_former['deps']);
		}

		if ($this->_is_post()) {
			$uda_validator = &uda::factory('voa_uda_frontend_train_validator');
			if (!$uda_validator->category_title($category['title'])) {  //检查标题长度是否合法
				$this->message('error', $uda_validator->error);
			}
			if (!$uda->edit($tc_id, $category)) {
				$this->message('error', $uda->error);
			}
			if ($is_new) {
				$this->message('success', '新增目录成功', $this->cpurl($this->_module, $this->_operation, 'cglist', $this->_module_plugin_id), false);
			} else {
				$this->message('success', '编辑目录成功', $this->cpurl($this->_module, $this->_operation, 'cgedit', $this->_module_plugin_id, array('tc_id' => $tc_id)), false);
			}
		}

		$this->view->set('category', $category_former);
		$this->view->set('tc_id', $tc_id);
		if (empty($category_former['contacts'])) {
			$category_former['contacts'] = array();
		}
		if (empty($category_former['deps'])) {
			$category_former['deps'] = array();
		}
		$this->view->set('m_uids', json_encode(array_values($category_former['contacts'])));
		$this->view->set('cd_ids', json_encode(array_values($category_former['deps'])));
		$this->view->set('URL', $this->url);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $is_new ? 'cgadd' : 'cgedit', $this->_module_plugin_id, array('tc_id' => $tc_id)));

		$this->output('office/train/category_form');

		return;
	}

	/**
	 * 取回有权限查看的部门及人员信息
	 * @param array $arr 信息数组
	 */
	private function __get_rights_info (&$users, &$deps) {

		$existed_rights = array();
		if (isset($users)) { //取回有权限查看文章的人员信息
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($users);
			if ($users) {
				foreach ($users as $user) {
					$existed_rights[] = array(
						'm_uid' => $user['m_uid'],
						'm_username' => $user['m_username'],
						'selected' => (bool)true,
					);
				}
			}
			$users = $existed_rights;
		} else {
			$users = array();
		}
		$existed_rights = array();
		if (isset($deps)) { //取回有权限查看文章的部门信息
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$deps = $serv_d->fetch_all_by_key($deps);
			if ($deps) {
				foreach ($deps as $dep) {
					$existed_rights[] = array(
						'id' => $dep['cd_id'],
						'cd_name' => $dep['cd_name'],
						'isChecked' => (bool)true,
					);
				}
			}
			$deps = $existed_rights;
		} else {
			$deps = array();
		}

		return true;
	}

	/**
	 * 取回目录权限
	 * @param array $arr 信息数组
	 */
	private function __get_right_limit ($tc_id) {
		$uda_category = &uda::factory('voa_uda_frontend_train_action_categoryedit');
		$category = array();
		$existed_range = array();
		$uda_category->get_rights($tc_id,$category);
		if ($category) {
			/** 人员 */
			if (isset($category['contacts']) && !empty($category['contacts'])) {
				foreach ($category['contacts'] as $contact) {
					$existed_range[] = array('id' => $contact, 'input_name' => 'contacts[]');
				}
			}
			/** 部门 */
			if (isset($category['deps']) && !empty($category['deps'])) {
				foreach ($category['deps'] as $deps) {
					$existed_range[] = array('id' => $deps, 'input_name' => 'deps[]');
				}
			}

		}

		return $existed_range;
	}

}
