<?php
class voa_c_cyadmin_content_train_edit extends voa_c_cyadmin_content_train_base {

	public function execute() {
		$tid = $this->request->get('tid');
		$uda = &uda::factory('voa_uda_cyadmin_content_train_list');
		$view = $uda->get_view($tid);
		$view['start_time'] = $this->_pro_time($view['start_time']);
		$view['end_time'] = $this->_pro_time($view['end_time']);
		$view['url'] = $this->_pro_url($view['face_atid']);
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
			'initialContent' => $view['address'],
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
			'initialContent' => $view['content'],
			'elementPathEnabled' => false 
		);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		$this->view->set('ac', 'update');
		$this->view->set('view', $view);
		$this->view->set('sign', $this->_pro_sign($view['sign_fields']));
		$this->view->set('ueditor_output', $ueditor_output);
		
		$this->_render('new');
	}
}
