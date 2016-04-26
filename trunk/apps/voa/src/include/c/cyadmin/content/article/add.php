<?php
/**
 * voa_c_cyadmin_content_article_add
 * 添加文章
 */
class voa_c_cyadmin_content_article_add extends voa_c_cyadmin_content_article_base {

	public function execute() {
		
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
			'initialContent' => '请编辑文章内容',
			'elementPathEnabled' => false 
		);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		
		$this->view->set('ueditor_output', $ueditor_output);
		$this->output('cyadmin/content/article/add');
	}
}
