<?php
/**
 * list.php
 * 帐号管理列表
 * $Id$
 */
class voa_c_cyadmin_data_template_list extends voa_c_cyadmin_data_template_base {
	public function execute() {
		$page = $this->request->get ( 'page' );
		
		$uda = &uda::factory ( 'voa_uda_cyadmin_news_template' );
		
		$list = array ();
		$multi = '';
		$total = '';
		$data = array ();
		$uda->getlist ( $page, $list, $multi, $total );
		
		// 发送数据
		$this->view->set ( 'data', $list );
		$this->view->set ( 'multi', $multi );
		$this->view->set ( 'total', $total );
		$this->view->set ( 'form_url', $this->cpurl ( $this->_module, $this->_operation, 'delete' ) );
		$this->view->set ( 'edit_url_base', $this->cpurl ( $this->_module, $this->_operation, 'edit', array (
				'ne_id' => '' 
		) ) );
		$this->view->set ( 'view_url_base', $this->cpurl ( $this->_module, $this->_operation, 'view', array (
				'ne_id' => '' 
		) ) );
		$this->view->set ( 'delete_url_base', $this->cpurl ( $this->_module, $this->_operation, 'delete', array (
				'ne_id' => '' 
		) ) );
		$this->view->set ( 'list_url_base', $this->cpurl ( $this->_module, $this->_operation, 'list' ) );
		$this->view->set ( 'add_url_base', $this->cpurl ( $this->_module, $this->_operation, 'add', array (
				'ne_id' => '' 
		) ) );
		return $this->output ( 'cyadmin/data/template/list' );
	}
}
