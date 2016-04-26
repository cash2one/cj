<?php
/**
 * list.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_help_list extends voa_c_admincp_help_base {

	public function execute() {

		$this->view->set('doc_list', $this->_category_doc_list);
		$this->view->set('nav_title', $this->_navtitle);
		$this->output('help/list');
	}

}
