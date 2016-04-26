<?php

/**
 * view.php
 * 帐号详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_news_add extends voa_c_cyadmin_enterprise_base {


	public function execute() {
		$author = $this->session->getx($this->_auth_cookie_names['username']);
		//var_dump( $this->session);die;
		//var_dump($this->session->getx($this->_auth_cookie_names['username']));die;
		$post = $this->request->postx();
		if( ! empty( $post ) ) {
			$post['author'] = $author;
			$uda            = &uda::factory( 'voa_uda_cyadmin_enterprise_news' );
			$data           = array();
			$uda->add( $post, $data );
			if( $uda->errmsg ) {
				$this->message( 'error', $uda->errmsg );
			}
			if( $data ) {
				$this->message( 'success', '保存成功', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
			}


		}
		// 初始化编辑器
		$ueditor     = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = '/static/ueditor/';
		// 处理上传文件路径
		$ueditor->server_url = '/ueditor/';


		$ueditor->ueditor_config = array(
			'toolbars'           => '_cyadmin',
			'textarea'           => $content_key,
			'initialFrameHeight' => 300,
			'initialContent'     => '',
			'elementPathEnabled' => false
		);
		if( ! $ueditor->create_editor( 'content', '' ) ) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		$this->view->set( 'active', $this->_subop );
		$this->view->set( 'ueditor_output', $ueditor_output );

		$this->view->set( 'list_url_base', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
		$this->output( 'cyadmin/enterprise/news/add' );

		return true;
	}

}



