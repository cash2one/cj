<?php
/**
 * 编辑文章
 * voa_c_cyadmin_content_article_edit
 */
class voa_c_cyadmin_content_article_edit extends voa_c_cyadmin_content_article_base {

	public function execute() {
		$aid = (int) $this->request->get('aid');
		if (empty($aid)) {
			$this->message('error', '请指定要编辑的数据');
		}
		$uda = &uda::factory('voa_uda_cyadmin_content_article_list');
		$view = $uda->get_view($aid);
		if (!empty($view['logo_atid'])) {
			$view['logo_url'] = $this->_get_img_url($view['logo_atid']);
		}
		
		if (!empty($view['face_atid'])) {
			$view['face_url'] = $this->_get_img_url($view['face_atid']);
		}
		$this->view->set('view', $view);
		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = '/static/ueditor/';
		// 处理上传文件路径
		$ueditor->server_url = '/ueditor/';
		
		$ueditor->ueditor_config = array(
			'toolbars' => '_cyadmin',
			'textarea' => $content_key,
			'initialFrameHeight' => 300,
			'initialContent' => isset($view['content']) ? $view['content'] : '',
			'elementPathEnabled' => false 
		);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		$this->view->set('ueditor_output', $ueditor_output);
		$this->output('cyadmin/content/article/edit');
	}
}
