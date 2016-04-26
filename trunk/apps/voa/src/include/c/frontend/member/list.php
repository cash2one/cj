<?php

/**
 * 列出所有员工信息(包括头像/名称/uid/职位)
 * $Author$
 * $Id$
 */
class voa_c_frontend_member_list extends voa_c_frontend_base {

	public function execute() {

		/** 搜索条件 */
		$sotext = (string)$this->request->get('sotext');
		$type = (int)$this->request->get('type');
		$uda_so = &uda::factory('voa_uda_frontend_member_search');
		$data = array();
		$uda_so->search(array('kw' => $sotext, 'type' => $type), $data, $this->_user['m_uid']);

		$this->_json_message($data);
	}
}

