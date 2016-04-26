<?php

/**
 * 后台管理/线下培训新增
 * Create By liyongjian
 */
class voa_c_cyadmin_content_train_add extends voa_c_cyadmin_content_train_base {

	public function execute() {
		$ueditor = new ueditor();
		
		// 百度地图
		// 编辑器资源路径
		$ueditor->ueditor_home_url = '/static/ueditor/';
		// 处理上传文件路径
		$ueditor->server_url = '/ueditor/';
		$ueditor->ueditor_config = array(
			'toolbars' => '_map',
			'textarea' => 'address',
			'initialFrameHeight' => 100,
			'initialContent' => '',
			'elementPathEnabled' => false 
		);
		if (!$ueditor->create_editor('address', '')) {
			$ueditor_map = $ueditor->ueditor_error;
		} else {
			$ueditor_map = $ueditor->ueditor_html;
		}
		
		$this->view->set('ueditor_map', $ueditor_map);
		
		// 编辑器资源路径
		$ueditor->ueditor_home_url = '/static/ueditor/';
		// 处理上传文件路径
		$ueditor->server_url = '/ueditor/';
		$ueditor->ueditor_config = array(
			'toolbars' => '_cyadmin',
			'textarea' => 'content',
			'initialFrameHeight' => 300,
			'initialContent' => '',
			'elementPathEnabled' => false 
		);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('ac', 'add');
		$this->view->set('sign_field', $this->_field());
		$this->_render('new');
	}

	protected function _field() {
		$serv = &service::factory('voa_s_cyadmin_content_train_setting');
		return $serv->list_all();
	}
}
