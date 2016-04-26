<?php
/**
 * 微信墙列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_wxwall_list extends voa_c_frontend_wxwall_base {

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$start, $perpage, $page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 读取审核中/正运行的 */
		$serv_w = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$updated = intval($this->request->get('updated'));
		if (empty($updated)) {
			$mine_list = $serv_w->fetch_mine_apply(startup_env::get('wbs_uid'), 0, 20);
			foreach ($mine_list as &$v) {
				$v['ww_subject'] = rhtmlspecialchars($v['ww_subject']);
				$v['ww_message'] = rhtmlspecialchars($v['ww_message']);
				$v['_begintime'] = rgmdate($v['ww_begintime']);
			}
			unset($v);

			$run_list = $serv_w->fetch_running(0, 20);
			foreach ($run_list as &$v) {
				$v['ww_subject'] = rhtmlspecialchars($v['ww_subject']);
				$v['ww_message'] = rhtmlspecialchars($v['ww_message']);
				$v['_begintime'] = rgmdate($v['ww_begintime']);
			}
			unset($v);
		}

		/** 读取列表 */
		$updated = empty($updated) ? startup_env::get('timestamp') + 1 : $updated;
		$fin_list = $serv_w->fetch_fin_by_updated($updated, $start, $perpage);
		/** 如果有记录, 则取最后时间 */
		foreach ($fin_list as &$v) {
			$v['ww_subject'] = rhtmlspecialchars($v['ww_subject']);
			$v['ww_message'] = rhtmlspecialchars($v['ww_message']);
			$v['_endtime'] = rgmdate($v['ww_endtime']);
			$updated = $v['ww_updated'];
		}
		unset($v);

		/** 读取已结束的 */
		$ct_fin = $serv_w->count_fin();
		$ct_running = $serv_w->count_running();

		$inajax = $this->request->get('inajax');
		$tpl = 'wxwall/list';
		if (!empty($inajax)) {
			$tpl .= '_li';
		}

		$this->view->set('fin_list', $fin_list);
		$this->view->set('run_list', $run_list);
		$this->view->set('mine_list', $mine_list);
		$this->view->set('updated', $updated);
		$this->view->set('ct_fin', $ct_fin);
		$this->view->set('ct_running', $ct_running);
		$this->view->set('perpage', $perpage);
		$this->view->set('navtitle', '微信墙');

		$this->_output($tpl);
	}
}

