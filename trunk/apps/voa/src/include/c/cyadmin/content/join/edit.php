<?php
class voa_c_cyadmin_content_join_edit extends voa_c_cyadmin_content_join_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_join_list');
		$jid = $this->request->get('jid');
		$view = $uda->get_view($jid);
		$this->view->set('view', $view);
		$this->view->set('ac', 'update');
		$this->output('cyadmin/content/join/new');
	}
}
