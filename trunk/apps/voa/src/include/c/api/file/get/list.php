<?php
/**
 * list.php
 * 文件列表
 * $Author$
 * $Id$
 */
class voa_c_api_file_get_list extends voa_c_api_file_base {

	public function execute() {

		// 定义有效的请求参数数组
		$fileList =	array();
		$total = 0;
		$limit = !empty($this->_params['limit']) ? (int)$this->_params['limit'] : 12;
		$limit = ($limit > 10 ? 10 : $limit);
		$page = !empty($this->_params['page']) ? (int)$this->_params['page'] : 1;
		$id = !empty($this->_params['id']) ? (int)$this->_params['id'] : 0;
		
		$uda = &uda::factory('voa_uda_frontend_file_get');
		$ret = array();
		if (!$uda->search($ret, $this->_member['m_uid'], $id, $limit, $page)) {
			return false;
		}
		list($total, $pages, $fileList)	= $ret ;

		// 输出结果
		$this->_result = array('total' => $total, 'page' => $page, 'pages'=>$pages, 'list' => $fileList);

		return;

	}

	

}
