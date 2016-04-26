<?php
class voa_c_cyadmin_content_link_view extends voa_c_cyadmin_content_link_base {

	public function execute() {
		$lid = $this->request->get('lid');
		if (empty($lid)) {
			$this->message('error', '请指定要查看的数据');
		}
		
		$uda = &uda::factory('voa_uda_cyadmin_content_link_list');
		$view = $uda->get_view($lid);
		$this->view->set('view', $this->_formart($view));
		$this->output('cyadmin/content/link/view');
	}
}
