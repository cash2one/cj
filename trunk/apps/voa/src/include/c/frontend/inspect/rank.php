<?php
/**
 * 巡店排行信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_rank extends voa_c_frontend_inspect_base {

	public function execute() {

		// 是否只允许查看自己的排行
		$uids = array();
		if (0 < $this->_p_sets['rank_only_view_self']) {
			$uids[] = $this->_user['m_uid'];
		}

		$insi_id = 0;
		$cr_ids = array();
		$this->_get_region_id($cr_ids);

		// 如果 ymd 为空
		$ymd_s = $this->request->get('ymd_s');
		$ymd_e = $this->request->get('ymd_e');
		if (empty($ymd_s) || empty($ymd_e)) {
			$ymd_s = rgmdate(startup_env::get('timestamp'), 'Ymd');
			$ymd_e = rgmdate(startup_env::get('timestamp') - 7 * 86400, 'Ymd');
		}

		if ($ymd_e < $ymd_s) {
			list($ymd_e, $ymd_s) = array($ymd_s, $ymd_e);
		}

		// 读取排行列表
		$uda_rank = new voa_uda_frontend_inspect_score_rank();
		$list = array();
		$params = $this->request->getx();
		$params['ymd_s'] = $ymd_s;
		$params['ymd_e'] = $ymd_e;
		$params['_uids'] = $uids;
		$params['_cr_ids'] = $cr_ids;
		$uda_rank->execute($params, $list);

		$index = $uda_rank->get_start();
		foreach ($list as $k => $v) {
			$list[$k]['_rank_id'] = ++ $index;
		}

		$next_page = $uda_rank->get_page();
		if (!empty($list)) {
			$next_page = $uda_rank->get_page() + 1;
		}

		$regions = array();
		$this->get_shop_json($regions);

		$this->view->set('list', $list);
		$this->view->set('perpage', $uda_rank->get_perpage());
		$this->view->set('page', $next_page);
		$this->view->set('shops', $this->_shops);
		$this->view->set('ymd_s', $ymd_s);
		$this->view->set('ymd_e', $ymd_e);
		$this->view->set('regions', rjson_encode($regions));

		// 模板
		$tpl = 'inspect/rank';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	public function _get_region_id(&$ids) {

		// 取地区id
		$cr_parent_id = (string)$this->request->get('pid');
		$cr_id = (string)$this->request->get('cid');

		if (array_key_exists($cr_parent_id, $this->_regions['data'])) {
			$this->view->set('parent_shop', $this->_regions['data'][$cr_parent_id]);
		}

		if (array_key_exists($cr_id, $this->_regions['data'])) {
			$this->view->set('cur_shop', $this->_regions['data'][$cr_id]);
		}

		$cr_ids = array();
		if (0 < $cr_id) {
			$ids[] = $cr_id;
			return true;
		}

		if ($cr_parent_id && array_key_exists($cr_parent_id, $this->_regions['level'])) {
			$ids = $this->_regions['level'][$cr_parent_id];
			return true;
		}

		return true;
	}
}
