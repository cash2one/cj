<?php
/**
 * 活动/产品排行信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_rank extends voa_c_frontend_productive_base {
	/** 起始位置 */
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {

		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 日期 */
		$ymd = (string)$this->request->get('ymd');
		if (empty($ymd)) {
			$ymd = rgmdate(startup_env::get('timestamp'), 'Ymd');
		}

		/** 是否只允许查看自己的排行 */
		$uids = array();
		if (0 < $this->_p_sets['rank_only_view_self']) {
			$uids[] = $this->_user['m_uid'];
		}

		$pti_id = 0;
		$cr_ids = array();
		$this->_get_region_id($cr_ids);

		/** 读取排行列表 */
		$serv = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->list_rank_by_cr_id_pti_id_score($pti_id, $cr_ids, $ymd, $uids, $this->_start, $this->_perpage);
		$index = $this->_start;
		foreach ($list as $k => $v) {
			$list[$k]['_rank_id'] = ++ $index;
		}

		/** 过滤 */
		/**$fmt = &uda::factory('voa_uda_frontend_productive_format');
		if (!$fmt->productive_list($list)) {
			$this->_error_message($fmt->error);
			return false;
		}*/

		$next_page = $this->_page;
		if (!empty($list)) {
			$next_page = $this->_page + 1;
		}

		$regions = array();
		$this->get_shop_json($regions);

		$this->view->set('list', $list);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('page', $next_page);
		$this->view->set('shops', $this->_shops);
		$this->view->set('ymd', $ymd);
		$this->view->set('regions', rjson_encode($regions));

		/** 模板 */
		$tpl = 'productive/rank';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	public function _get_region_id(&$ids) {

		/** 取地区id */
		$cr_parent_id = (string)$this->request->get('pid');
		$cr_id = (string)$this->request->get('cid');

		if (array_key_exists($cr_parent_id, $this->_regions['data'])) {
			$this->view->set('parent_shop', $this->_regions['data'][$cr_parent_id]);
		}

		if (array_key_exists($cr_id, $this->_regions['data'])) {
			$this->view->set('cur_shop', $this->_regions['data'][$cr_id]);
		}

		$cr_ids = array();
		if ($cr_id) {
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
