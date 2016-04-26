<?php
class voa_c_cyadmin_content_link_add extends voa_c_cyadmin_content_link_base {

	public function execute() {
		$this->view->set('ac', 'add');
		
		$this->output('cyadmin/content/link/new');
	}
}
