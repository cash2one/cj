<?php
/**
 * 获取列表
 * $Author$
 * $Id$
 */

class voa_c_api_notice_get_list extends voa_c_api_sign_base {

	public function execute() {

		$page = (int)$this->_get('page', 0);
		$limit = (int)$this->_get('limit', 0);

		list(
			$start, $perpage, $page
		) = voa_h_func::get_limit($page, $limit > 100 ? 100 : (0 >= $limit ? 10 : $limit));

		/** 读取公告 */
		$serv_nt = &service::factory('voa_s_oa_notice', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_nt->fetch_all($start, $perpage);

		/** 读取已读列表 */
		$serv_r = &service::factory('voa_s_oa_notice_read', array('pluginid' => startup_env::get('pluginid')));
		$reads = $serv_r->fetch_all_by_nt_id_m_uid(array_keys($list), $this->_member['m_uid']);

		/** 取公告id */
		$readed_ids = array();
		foreach ($reads as $_r) {
			$readed_ids[$_r['nt_id']] = $_r['nt_id'];
		}

		/** 公告列表 */
		$data = array();
		foreach ($list as $_p) {
			$data[] = array(
				'nt_id' => $_p['nt_id'],
				'author' => $_p['nt_author'],
				'subject' => $_p['nt_subject'],
				'message' => bbcode::instance()->bbcode2html($_p['nt_message']),
				'readed' => array_key_exists($_p['nt_id'], $readed_ids) ? 1 : 0,
				'timestamp' => $_p['nt_created']
			);
		}

		/** 统计总数 */
		$total = $serv_nt->count_by_conditions();
		$pages = ceil($total / $perpage);

		$this->_result = array(
			'list' => $data,
			'total' => $total,
			'page' => $page,
			'pages' => $pages
		);

		return true;
	}

}
