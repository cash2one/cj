<?php
/**
 * voa_c_admincp_office_namecard_list
 * 企业后台/微办公管理/微名片/名片列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_inspect_list extends voa_c_admincp_office_inspect_base {
	// uda shop
	protected $_uda_shop_list = null;
	// 打分项
	protected $_inspect_item = array();
	// 区域
	protected $_regions = array();
	// 门店
	protected $_shops = array();

	public function execute() {

		// 读取巡店所有打分项
		$this->_inspect_item = voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa');
		// 取地区配置
		$this->_regions = voa_h_cache::get_instance()->get('region', 'oa');
		// 取店铺配置
		$this->_shops = voa_h_cache::get_instance()->get('shop', 'oa');

		$this->_uda_shop_list = new voa_uda_frontend_common_shop_list();

		$cache_config = voa_h_cache::get_instance()->get('plugin.inspect.setting', 'oa');
		$this->view->set('cache_config', $cache_config);

		$act = $this->request->get('act');
		$act = empty($act) || !in_array($act, array('getshoplist', 'view')) ? 'list' : $act;
		$func = '_ac_'.$act;
		if (method_exists($this, $func)) {
			$this->$func();
		}

		$this->view->set('getRegionUrl', $this->cpurl($this->_module, $this->_operation, 'plan', $this->_module_plugin_id, array('act'=>'getregionlist')));
		$this->view->set('getShopUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'getshoplist')));
		$this->view->set('getUsersUrl', $this->cpurl($this->_module, $this->_operation, 'plan', $this->_module_plugin_id, array('act'=>'getusers')));
		// 详情url
		$this->view->set('viewUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('act'=>'view', 'ins_id'=>'')));

		$this->view->set('items', $this->_inspect_item);
		$this->view->set('regions', $this->_regions);
		$this->view->set('shops', $this->_shops);

		if ($act == 'view') {
			$this->output('office/inspect/view_inspect');
		} else {
			$this->output('office/inspect/list');
		}
	}

	// 获取门店列表
	protected function _ac_getshoplist() {

		$name = $this->request->post('kw');
		echo json_encode($this->__get_shop_list($name));
		exit;
	}

	// 读取列表
	protected function _ac_list() {

		$start_date = 0;
		$end_date = 0;
		$condi = array();
		$search = array(
			'city' => array(),
			'district' => array()
		);
		$post = array();
		if ($this->request->post('submit')) {
			$post = $this->request->postx();
		} elseif ($this->request->get('submit')) {
			$post = $this->request->getx();
		}

		if ($post) {
			$search = $post['search'];
			// 指定用户
			$this->__assign_uid($condi, $search);
			// 指定门店id
			$this->__assign_csp_id($condi, $search);
			// 指定区域
			if (!$this->__assign_district($condi, $search)) {
				$this->__assign_city($condi, $search);
			}

			if ($search['start_date']) {
				$start_date = rstrtotime($search['start_date']);
			}

			if ($search['end_date']) {
				$end_date = rstrtotime($search['end_date']) + 86400;
			}
		}

		// 读取巡店数据
		list($total, $multi, $list) = $this->__list_inspect($condi, $start_date, $end_date);

		$this->view->set('search', $search);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('region', $this->_get_region_list());
		$this->view->set('acurl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array()));
	}

	// 查看指定巡店信息
	protected function _ac_view() {

		$ins_id = $this->request->get('ins_id');

		// 读取巡店详情表
		$uda_ins_get = new voa_uda_frontend_inspect_get();
		$inspect = array();
		$uda_ins_get->execute(array('ins_id' => $ins_id), $inspect);

		// 读取打分记录
		$inspect_score = array();
		$uda_ins_scr_list = new voa_uda_frontend_inspect_score_list();
		$uda_ins_scr_list->execute(array('ins_id' => $ins_id), $inspect_score);

		// 补齐缺失的打分项
		$this->__get_ext_items($this->_inspect_item, $inspect_score);
		$uda_ins_scr_list->set_items($this->_inspect_item);

		// 计算主评分项分数
		$total = 0;
		$item2score = array();
		$uda_ins_scr_list->calc_score($total, $item2score, $inspect_score);
		if ($total < 0) {
			$total = 0;
		}

		//$inspect['score'] = $total;

		// 读取附件
		$uda_ins_att_list = new voa_uda_frontend_inspect_attachment_list();
		$attachs = array();
		$uda_ins_att_list->execute(array('ins_id' => $ins_id), $attachs);
		$att_list = array();
		foreach ($attachs as $_att) {
			if (!isset($att_list[$_att['isr_id']])) {
				$att_list[$_att['isr_id']] = array();
			}

			$att_list[$_att['isr_id']][] = array(
				'picurl' => voa_h_attach::attachment_url($_att['at_id'], 45),
				'orgpicurl' => voa_h_attach::attachment_url($_att['at_id'])
			);
		}

		// 读取巡店接收人/抄送人
		$uda_ins_mem_list = new voa_uda_frontend_inspect_mem_list();
		$uda_ins_mem_list->set_limit(false);
		$mem_list = array();
		$uda_ins_mem_list->execute(array('ins_id' => $ins_id), $mem_list);
		$mem_tc = array('to' => array(), 'cc' => array());
		foreach ($mem_list as $_mem) {
			if (voa_d_oa_inspect_mem::TYPE_TO == $_mem['insm_type']) {
				$mem_tc['to'][] = $_mem['m_username'];
			} else {
				$mem_tc['cc'][] = $_mem['m_username'];
			}
		}

		// 获取门店
		$shop = array();
		$p_region = array();
		$c_region = array();
		if (isset($this->_shops[$inspect['csp_id']])) {
			$shop = $this->_shops[$inspect['csp_id']];
			if (isset($this->_regions['data'][$shop['cr_id']])) {
				$c_region = $this->_regions['data'][$shop['cr_id']];
				$p_region = $this->_regions['data'][$c_region['cr_parent_id']];
			}

			$this->view->set('shop', $shop);
			$this->view->set('p_region', $p_region);
			$this->view->set('c_region', $c_region);
		}

		$this->view->set('mem_tc', $mem_tc);
		$this->view->set('inspect', $inspect);
		$this->view->set('inspect_score', $inspect_score);
		$this->view->set('att_list', $att_list);
		$this->view->set('item2score', $item2score);
	}

	private function __get_ext_items(&$items, $exts) {

		$noids = array();
		foreach ($exts as $_scr) {
			if (isset($items[$_scr['insi_id']])) {
				continue;
			}

			$noids[] = $_scr['insi_id'];
		}

		if (empty($noids)) {
			return true;
		}

		$uda_ins_item = new voa_uda_frontend_inspect_item_list();
		$uda_ins_item->set_limit(false);
		$curitems = array();
		$p_ids = array();
		$uda_ins_item->execute(array('insi_id' => $noids), $curitems);
		foreach ($curitems as $_v) {
			if (!array_key_exists($_v['insi_parent_id'], $items['p2c'])) {
				$items['p2c'][$_v['insi_parent_id']] = array();
			}

			$items[$_v['insi_id']] = $_v;
			$items['p2c'][$_v['insi_parent_id']][$_v['insi_id']] = $_v['insi_id'];
			if (0 < $_v['insi_parent_id'] && !isset($curitems[$_v['insi_parent_id']])
					&& !isset($items[$_v['insi_parent_id']])) {
				$p_ids[] = array('insi_id' => $_v['insi_parent_id']);
			}
		}

		return $this->__get_ext_items($items, $p_ids);
	}

	/**
	 * 根据条件读取巡店记录
	 * @param array $condi 查询条件
	 * @param int $start_date 开始时间
	 * @param int $end_date 结束时间
	 * @return multitype:number string array |multitype:number array string
	 */
	private function __list_inspect($condi, $start_date, $end_date) {

		$condi['ins_type'] = 3;
		$condi['start_date'] = $start_date;
		$condi['end_date'] = $end_date;
		// 每页显示数
		$condi['perpage'] = 20;
		$condi['page'] = $this->request->get('page');

		// 读取数据
		$uda_ins = new voa_uda_frontend_inspect_list();
		$list = array();
		$uda_ins->execute($condi, $list);
		$total = $uda_ins->get_total();
		$perpage = $uda_ins->get_perpage();
		$page = $uda_ins->get_page();

		// 分页显示
		$multi = '';
		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 取所巡门店地区信息
		foreach ($list as &$_v) {
			$_v['_cr_id'] = 0;
			$_v['_cr_parent_id'] = 0;
			if (empty($this->_shops[$_v['csp_id']])) {
				continue;
			}

			$shop = $this->_shops[$_v['csp_id']];
			$cr_id = (int)$shop['cr_id'];
			$region = $this->_regions['data'][$cr_id];
			$_v['_cr_id'] = $cr_id;
			if (empty($region)) {
				continue;
			}

			$_v['_cr_parent_id'] = $region['cr_parent_id'];
		}

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $perpage,
			'current_page' => $page,
			'show_total_items' => true,
		);

		$multi = pager::make_links($pager_options);

		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		return array($total, $multi, $list);
	}

	/**
	 * 获取门店列表
	 * @param string $name 门店名称
	 * @return boolean|multitype:
	 */
	protected function __get_shop_list($name) {

		// 如果为空
		if (empty($name)) {
			return array();
		}

		$result = array();
		$this->_uda_shop_list->set_limit(false);
		$this->_uda_shop_list->execute(array('csp_name' => $name), $result);

		return empty($result) ? array() : array_values($result);
	}

	/**
	 * 指定用户
	 * @param array $condi 整理后的条件
	 * @param array $search 用户提交的搜索条件
	 * @return boolean
	 */
	private function __assign_uid(&$condi, &$search) {

		if (empty($search['assign_uid'])) {
			return false;
		}

		$condi['m_uid'] = explode(',', $search['assign_uid']);
		$serv = &service::factory('voa_s_oa_member');
		$assign_users = $serv->fetch_all_by_ids(explode(',', $search['assign_uid']));
		$search['assign_users'] = array();
		foreach ($assign_users as $item) {
			$search['assign_users'][] = $item['m_username'];
		}

		if (!empty($search['assign_users'])) {
			$search['assign_users'] = implode(',', $search['assign_users']);
		}

		return true;
	}

	/**
	 * 指定门店
	 * @param array $condi 整理后的条件
	 * @param array $search 用户提交的搜索条件
	 * @return boolean
	 */
	private function __assign_csp_id(&$condi, &$search) {

		if (empty($search['csp_ids'])) {
			return false;
		}

		$result = array();
		$this->_uda_shop_list->set_limit(false);
		$this->_uda_shop_list->execute(array('csp_id' => $search['csp_ids']), $result);
		$shops = empty($result) ? array() : array_values($result);

		$search['csp_names'] = array();
		foreach ($shops as $item) {
			$search['csp_names'][] = $item['csp_name'];
			$condi['csp_id'][] = $item['csp_id'];
		}

		if (!empty($condi['csp_id'])) {
			$search['csp_names'] = implode(',', $search['csp_names']);
		}

		return true;
	}

	/**
	 * 指定地区
	 * @param array $condi 整理后的条件
	 * @param array $search 用户提交的搜索条件
	 * @return boolean
	 */
	private function __assign_district(&$condi, &$search) {

		if (empty($search['district'])) {
			return false;
		}

		// for search form
		$uda = new voa_uda_frontend_common_region_list();
		$uda->set_limit(false);
		$uda->execute(array('cr_parent_id' => $search['city']), $search['district_org']);

		// unset($regions);
		$shops = array();
		$this->_uda_shop_list->set_limit(false);
		$this->_uda_shop_list->execute(array('cr_id' => $search['district']), $shops);
		if ($shops) {
			$shop_ids = array();
			foreach ($shops as $val) {
				$shop_ids[] = $val['csp_id'];
			}

			$condi['csp_id'] = $shop_ids;
		} else {
			$condi['csp_id'] = '';
		}

		return true;
	}

	/**
	 * 指定城市
	 * @param array $condi 整理后的条件
	 * @param array $search 用户提交的搜索条件
	 * @return boolean
	 */
	private function __assign_city(&$condi, &$search) {

		if (empty($search['city'])) {
			return false;
		}

		$regions = array();
		$uda = new voa_uda_frontend_common_region_list();
		$uda->set_limit(false);
		$uda->execute(array('cr_parent_id' => $search['city']), $regions);
		// for search form
		$search['district_org'] = $regions;
		if ($regions) {
			$regions_ids = array();
			foreach ($regions as $val) {
				$regions_ids[] = $val['cr_id'];
			}

			$shops = array();
			$this->_uda_shop_list->set_limit(false);
			$this->_uda_shop_list->execute(array('cr_id' => $regions_ids), $shops);
			if ($shops) {
				$shop_ids = array();
				foreach ($shops as $val) {
					$shop_ids[] = $val['csp_id'];
				}

				$condi['csp_id'] = $shop_ids;
			} else {
				$condi['csp_id'] = '';
			}
		} else {
			$condi['csp_id'] = '';
		}

		return true;
	}

}
