<?php

/**
 * 快递列表
 * $Author$
 * $Id$
 */
class voa_c_frontend_express_list extends voa_c_frontend_express_base
{

	public function execute()
	{
		$this->view->set('navtitle', '快递列表');
		//模板
		$tpl = 'mobile/express/list';
		$this->_output($tpl);
	}
}
