<?php
/**
 * 巡店排行信息
 * voa_c_api_inspect_get_rank
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_get_rank extends voa_c_api_inspect_base {
	/** 起始位置 */
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {

		/*需要的参数*/
		$fields = array(
			/*日期*/
			'ymd' => array('type' => 'int', 'required' => false),
			/*地区*/
			'pid' => array('type' => 'int', 'required' => false),
			/*门店*/
			'cid' => array('type' => 'int', 'required' => false),
			/*当前页码*/
			'page' => array('type' => 'int', 'required' => false),
			/*每页显示数据数*/
			'limit' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			/*检查参数*/
			return false;
		}
		if ($this->_params['page'] < 1) {
			/*设定当前页码的默认值*/
			$this->_params['page'] = 1;
		}

		if ($this->_params['limit'] < 1) {
			/*设定每页数据条数的默认值*/
			$this->_params['limit'] = 10;
		}

		/*获取分页参数*/
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		/** 是否只允许查看自己的排行 */
		$uids = array();
		if (0 < $this->_p_sets['rank_only_view_self']) {
			$uids[] = $this->_user['m_uid'];
		}

		$insi_id = 0;
		$cr_ids = array();
		$this->_get_region_id($cr_ids);

		/** 读取排行列表 */
		$serv = &service::factory('voa_s_oa_inspect_score', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->list_rank_by_cr_id_insi_id_score($insi_id, $cr_ids, $ymd, $uids, $this->_start, $this->_perpage);
		$total = $serv->count_by_conditions(array(
				'cr_id' => array($cr_id, 'in'),
				'm_uid' => array($uids, 'in'),
				'isr_date' => $ymd
			)	
			);

		$index = $this->_start;
		foreach ($list as $k => $v) {
			$list[$k]['_rank_id'] = ++ $index;
		}

		$regions = array();
		$this->get_shop_json($regions);

		// 输出结果
		$this->_result = array(
			'total' => $total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $list,
			//'regions' => $regions
		);

		return true;
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
