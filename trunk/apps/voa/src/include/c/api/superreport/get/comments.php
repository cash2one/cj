<?php
/**
 * voa_c_admincp_office_superreport_list
* 企业后台/微办公管理/超级报表/数据列表
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_api_superreport_get_comments extends voa_c_api_superreport_abstract {

	public function execute() {

		$dr_id = (int)$this->request->get('dr_id');   //日报ID
		$limit = $this->request->get('limit');   // 每页显示数量
		if (!$limit) {
			$limit = $this->_plugin_setting['comment_perpage'];
		}

		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 载入uda类
		$uda_list = &uda::factory('voa_uda_frontend_superreport_listcomments');
		// 列出数据请求
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$pager = array($start, $limit);
		// 数据结果
		$result = array();
		if (!$uda_list->result($pager, $result, $dr_id)) {
			$this->_errcode = $uda_list->errno;
			$this->_errmsg = $uda_list->error;

			return true;
		}

		$this->_result = array(
			'dr_id' => $dr_id,
			'page' => $page,
			'limit' => $limit,
			'total' => $result['total'],
			'list' => empty($result['list']) ? array() : array_values($result['list']),
			'total_page' => ceil($result['total']/$limit)
		);

		return true;
	}


}
