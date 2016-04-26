<?php
/**
 * voa_c_api_travel_get_category
 * 获取客户有权限查看的目录
 * $Author$
 * $Id$
 */

class voa_c_api_showroom_get_category extends voa_c_api_showroom_abstract {

	public function execute() {

		$page = (int)$this->_get('page');   // 获取页码
		$perpage = (int)$this->_get('limit');   // 获取每页个数
		$m_uid = (int)$this->_member['m_uid'];  // 获取用户ID
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $perpage);

		// 获取目录
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_showroom_action_categorylist');
		if (!$uda->list_right_categories($m_uid, array($start, $perpage), $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return false;
		}

		$this->_result = array(
			'page' => $this->_get_real_page($page),
			'limit' => $perpage,
			'uid' => $m_uid,
			'total' => $list['count'],
			'list' => array_values($this->_format_data($list['list']))
		);

		return true;
	}

	/**
	 * 格式化目录列表
	 * @param array $list 列表
	 * @return array
	 */
	protected  function _format_data($list) {

		$result = array();
		if ($list) {
			foreach ($list as $k => $val) {
				$result[$k]['tc_id'] = $val['tc_id'];
				$result[$k]['title'] = rhtmlspecialchars($val['title']);
			}
		}

		return $result;
	}
}

