<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/23
 * Time: 下午8:01
 */

class voa_c_admincp_office_questionnaire_situation extends voa_c_admincp_office_questionnaire_base {

	protected function execute() {

		$qu_id = $this->request->get('qu_id');
		if (empty($qu_id)) {
			$this->_error_message('缺失问卷ID');
		}

		$this->view->set('qu_id', $qu_id);

		$this->output('office/questionnaire/situation');
	}


}