<?php

/**
 * view.php
 * 帐号详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_news_list extends voa_c_cyadmin_enterprise_base {

	public function execute() {

		$page = $this->request->get( 'page' );

		$uda      = &uda::factory( 'voa_uda_cyadmin_enterprise_news' );
		$multi    = '';
		$msg_list = array();
		$total    = '';
		$list     = $uda->getlist( $page, $msg_list, $multi, $total );
		$data     = array();
		$uda->format( $msg_list, $data );

		$mo_n = count( $this->request->getx() );
		if( 2 == $mo_n ) { // 是通过模态框ajax这边传来的
			$mo_data = array(
				'list' => $data,
				'page' => $multi
			);
			// var_dump(json_encode($mo_data));die;
			echo json_encode( $mo_data );
			exit;
		}

		$this->view->set( 'active', $this->_subop );
		$this->view->set( 'data', $data );
		$this->view->set( 'multi', $multi );
		$this->view->set( 'total', $total );

		$this->view->set( 'form_url', $this->cpurl( $this->_module, $this->_operation, 'delete' ) );
		$this->view->set( 'delete_url_base', $this->cpurl( $this->_module, $this->_operation, 'delete', array( 'meid' => '' ) ) );
		$this->view->set( 'view_url_base', $this->cpurl( $this->_module, $this->_operation, 'view', array( 'meid' => '' ) ) );
		$this->view->set( 'add_url_base', $this->cpurl( $this->_module, $this->_operation, 'add' ) );

		/*新增消息的内容*/
		$author = $this->session->getx( 'cyadmin_username' );
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
		$this->view->set( 'ueditor_output', $ueditor_output );


		$this->output( 'cyadmin/enterprise/news/list' );

		return true;
	}

}


