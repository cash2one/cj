<?php
/**
 * view.php
 * 帐号详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_data_template_add extends voa_c_cyadmin_data_base {
	public function execute() {
		$post = $this->request->postx ();
		$neid = $this->request->get ( 'ne_id' );
		
		$uda = &uda::factory ( 'voa_uda_cyadmin_news_template' );
		// $neid为空则为增加，不为空则为编辑
		if (empty ( $neid )) {
			
			$data = array ();
			$uda->add ( $post, $data );
			if ($uda->errmsg) {
				$this->message ( 'error', $uda->errmsg );
			}
			if ($data) {
				$this->message ( 'success', '添加成功', $this->cpurl ( $this->_module, $this->_operation, 'list' ) );
			}
		} else {
			$uda_cy_news = &service::factory ( 'voa_s_cyadmin_news_template' );
			$result = $uda_cy_news->get ( $neid );
			if (! empty ( $post ['edit'] )) {
				
				$data = array ();
				$uda->edit ( $post, $data );
				if ($data) {
					$this->message ( 'success', '修改成功', $this->cpurl ( $this->_module, $this->_operation, 'list' ) );
				}
			}
		}
		
		// 初始化编辑器
		$ueditor = new ueditor ();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = '/static/ueditor/';
		// 处理上传文件路径
		$ueditor->server_url = '/ueditor/';
		
		$ueditor->ueditor_config = array (
				'toolbars' => '_cyadmin',
				'textarea' => $content_key,
				'initialFrameHeight' => 300,
				'initialContent' => isset ( $result ['content'] ) ? $result ['content'] : '',
				
				'elementPathEnabled' => false 
		);
		if (! $ueditor->create_editor ( 'content', '' )) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		
		if (! empty ( $neid )) {
			$this->view->set ( 'result', $result );
		}
		$this->view->set ( 'ueditor_output', $ueditor_output );
		
		$this->view->set ( 'list_url_base', $this->cpurl ( $this->_module, $this->_operation, 'list' ) );
		$this->output ( 'cyadmin/data/template/add' );
		return true;
	}
}



