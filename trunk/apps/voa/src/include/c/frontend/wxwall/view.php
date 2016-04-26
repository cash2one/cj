<?php
/**
 * 查看微信墙信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_wxwall_view extends voa_c_frontend_wxwall_base {

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$start, $perpage, $page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 获取微信墙信息 */
		$ww_id = intval($this->request->get('ww_id'));
		$serv_w = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$wall = $serv_w->fetch_by_id($ww_id);
		if (empty($wall)) {
			$this->_error_message('当前微信墙信息不存在');
		}

		$wall['ww_subject'] = rhtmlspecialchars($wall['ww_subject']);
		$wall['ww_message'] = rhtmlspecialchars($wall['ww_message']);

		/** 读取回复信息 */
		$updated = intval($this->request->get('updated'));
		$serv_p = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_ww_id_updated($ww_id, $updated, $start, $perpage);
		/** 按时间倒序整理, 进行反转操作 */
		$posts = array_reverse($posts);
		/** 如果有记录, 则取最后时间 */
		if (!empty($posts)) {
			$updated = $posts[0]['wwp_updated'];
		}

		$tpl = 'wxwall/view';
		$inajax = startup_env::get('inajax');
		if (!empty($inajax)) {
			$tpl .= '_li';
		}

		$wall_status = $this->_chk_wall($wall);
		/** 月/日/时/分 */
		$mdhi = rgmdate($wall['ww_begintime'], 'm:d:h:i');
		list($b_m, $b_d, $b_h, $b_i) = explode(':', $mdhi);
		$mdhi = rgmdate($wall['ww_endtime'], 'm:d:h:i');
		list($e_m, $e_d, $e_h, $e_i) = explode(':', $mdhi);

		$this->view->set('wall', $wall);
		$this->view->set('wall_status', $wall_status);
		$this->view->set('b_m', $b_m);
		$this->view->set('b_d', $b_d);
		$this->view->set('b_h', $b_h);
		$this->view->set('b_i', $b_i);
		$this->view->set('e_m', $e_m);
		$this->view->set('e_d', $e_d);
		$this->view->set('e_h', $e_h);
		$this->view->set('e_i', $e_i);
		$this->view->set('posts', $posts);
		$this->view->set('updated', $updated);
		$this->view->set('ts', startup_env::get('timestamp'));
		$this->view->set('wall_url', $this->_p_sets['domain']."/".config::get('voa.wxwall_path'));
		$this->view->set('navtitle', '微信墙详情');

		$this->_output($tpl);
	}

	/** 检查微信是否可用 */
	protected function _chk_wall($wall) {
		if (voa_d_oa_wxwall::IS_CLOSE == $wall['ww_isopen']) {
			$this->view->set('st_tip', '已关闭');
			return false;
		}

		if ($wall['ww_status'] != voa_d_oa_wxwall::STATUS_APPROVE) {
			$this->view->set('st_tip', '审核中');
			return false;
		}

		return true;
	}
}

