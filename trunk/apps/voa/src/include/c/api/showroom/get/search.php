<?php
/**
 * voa_c_api_travel_get_search
 * 根据关键字搜索文章
 * $Author$
 * $Id$
 */

class voa_c_api_showroom_get_search extends voa_c_api_showroom_abstract {

	public function execute() {

		$page = (int)$this->_get('page');   // 获取页码
		$perpage = (int)$this->_get('limit');   // 获取每页个数
		$m_uid = (int)$this->_member['m_uid'];  // 获取用户ID
		$keyword = (string)trim($this->_get('keyword'));  // 获取搜索关键字
		$perpage = 20 > $perpage ? 20 : $perpage;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $perpage);

		// 获取文章列表
		$list = array();

		$uda = &uda::factory('voa_uda_frontend_showroom_action_articlesearch');
		if (!$uda->search_artilce($m_uid, $keyword, array($start, $perpage), $list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return false;
		}

		$this->_result = array(
			'list' => array_values($this->_format_data($list['list'])),
			'total' => $list['total']
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
				$result[$k]['updated'] = $val['updated'];
			}
		}

		return $result;
	}

}

