<?php
class voa_c_cyadmin_content_join_view extends voa_c_cyadmin_content_join_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_join_list');
		$jid = $this->request->get('jid');
		if (empty($jid)) {
			$this->message('error', '请指定要查看的数据！');
		}
		$view = $uda->get_view($jid);
		$this->view->set('view', $view);
		$this->output('cyadmin/content/join/view');
	}
}
