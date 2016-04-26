<?php
/**
 * voa_c_admincp_office_news_add
 * 企业后台/微办公管理/新闻公告/添加
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_news_add extends voa_c_admincp_office_news_base {

	public function execute() {

		if ($this->_is_post()) {
			$data_post = $this->request->postx();
			$data = array();
			$this->data_form($data_post, $data);
			$news = array();
			try {
				// 读取数据
				$uda = &uda::factory('voa_uda_frontend_news_insert');
				$options['user_id'] = (int)$data_post['author'];
				$uda->add_news($data, $news, $options);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
			$is_publish = $data_post['is_publish'];
			$is_push = isset($data_post['is_push']) ? $data_post['is_push'] : 0;
			if (!$is_publish) { //如果是发布，则跳转到列表页
				if (!empty($news['is_check'])) {
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
		$ne_id = (int)$this->request->get('tem_id');
		$result = array();
		if($ne_id) {
			//$uda_cy_news = &uda::factory('voa_uda_cyadmin_news_template');
			//$uda_cy_news->get_by_id(array('ne_id' => $ne_id), $result);
			$rpc = voa_h_rpc::phprpc(config::get('voa.cyadmin_url').'OaRpc/Rpc/NewsTemplates');
			$result = $rpc->get_by_id($ne_id);
		}

		// 初始化编辑器

		$ueditor = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name') . '.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';
		$ueditor->ueditor_config = array('toolbars' => '_mobile', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => isset($result['content']) ? $result['content'] : '', 'elementPathEnabled' => false);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		// 获取分类
		$uda_cat = &uda::factory('voa_uda_frontend_news_category');
		$categories = $uda_cat->list_categories();

		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('categories', $categories);
		$this->view->set('result', $result);
		// 输出模板
		$this->output('office/news/add');
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

