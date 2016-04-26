<?php
/**
 * voa_c_api_travel_get_article
 * 获取一个目录下客户有权限查看的文章
 * $Author$
 * $Id$
 */

class voa_c_api_showroom_get_article extends voa_c_api_showroom_abstract {

	public function execute() {

		$page = (int)$this->_get('page');   // 获取页码
		$perpage = (int)$this->_get('limit');   // 获取每页个数
		$m_uid = (int)$this->_member['m_uid'];  // 获取用户ID
		$tc_id = (int)$this->_get('tc_id');  // 获取目录ID
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $perpage);

		// 获取文章列表
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_showroom_action_articlelist');
		if (!$uda->list_right_artilce($m_uid, $tc_id, array($start, $perpage), $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return false;
		}

		$this->_result = array(
			'page' => $this->_get_real_page($page),
			'limit' => $perpage,
			'uid' => $m_uid,
			'tc_id' => $tc_id,
			'total' => isset($list['total']) ? $list['total'] : 0,
			'list' => isset($list['list']) ? array_values($this->_format_data($list['list'])) : array()


		);

		return true;
	}

	/**
	 * 格式化文章列表
	 * @param array $list 列表
	 * @return array
	 */
	protected  function _format_data($list) {

		$result = array();
		if ($list) {
			foreach ($list as $k => $val) {
				$result[$k]['ta_id'] = $val['ta_id'];
				$result[$k]['title'] = rhtmlspecialchars($val['title']);
				$result[$k]['author'] = rhtmlspecialchars($val['author']);
				$result[$k]['tc_id'] = $val['tc_id'];
				$result[$k]['tc_name'] = rhtmlspecialchars($val['tc_name']);
				$result[$k]['read'] = $val['read'];
				$result[$k]['updated'] = rgmdate($val['updated'],'Y-m-d H:i');
			}
		}

		return $result;
	}

}

