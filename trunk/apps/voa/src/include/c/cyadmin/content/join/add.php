<?php
class voa_c_cyadmin_content_join_add extends voa_c_cyadmin_content_join_base {

	public function execute() {
		$this->view->set('ac', 'add');
		$this->output('cyadmin/content/join/new');
	}
}
