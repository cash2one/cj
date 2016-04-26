<?php
/**
 *
 * voa_c_cyadmin_data_template_view
 * @author Burce
 *
 */
class voa_c_cyadmin_data_template_view extends voa_c_cyadmin_data_base {
	public function execute() {
		$neid = $this->request->get ( 'ne_id' );
		$uda = &uda::factory ( 'voa_uda_cyadmin_news_template' );
		$uda->getview ( $neid, $data );
		if ($uda->errmsg) {
			$this->message ( 'error', $uda->errmsg );
		}
		$this->view->set ( 'data', $data );
		$this->output ( 'cyadmin/data/template/view' );
	}
}
