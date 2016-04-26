<?php
/**
 * 新闻公告添加多条
 * voa_c_admincp_office_news_madd
 * @date: 2015年5月12日
 * @author: kk
 * @version:
 */

class voa_c_admincp_office_news_madd extends voa_c_admincp_office_news_base {

	public function execute() {
		// 获取分类
		$uda_cat = &uda::factory('voa_uda_frontend_news_category');
		$categories = $uda_cat->list_categories();

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
		$form_action_url = "/admincp/office/news/madd/pluginid/".$this->_module_plugin_id;
		$api_action_url = "/api/news/post/madd/pluginid/".$this->_module_plugin_id;
		$this->view->set('categories', $categories);
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('form_action_url', $form_action_url);
		$this->view->set('api_action_url', $api_action_url);
		//输出模板
		$this->output('office/news/madd');
	}
}
