<?php
/**
 * voa_c_api_travel_get_goodsrank
 * 产品统计
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_goodsrank extends voa_c_api_travel_abstract {
	// 当前页码
	protected $_page = 0;
	// 每页显示数
	protected $_perpage = 0;
	// 起始位置
	protected $_start = 0;
	// 记录总数
	protected $_total = 0;

	public function execute() {

		// 分页参数
		$page = (int)$this->request->get('page');
		$perpage = (int)$this->request->get('limit');
		list($this->_start, $this->_perpage, $this->_page) = voa_h_func::get_limit($page, $perpage);

		// 根据不同条件读取数据
		$acs = array('view', 'inquiry');
		$ac = (string)$this->request->get('ac');
		$ac = empty($ac) || !in_array($ac, $acs);

		$list = array();
		$func = '_'.$ac;
		if (method_exists($this, $func)) {
			$list = $this->$func();
		}

		// 读取产品信息
		$dataids = array();
		foreach ($list as $_v) {
			$dataids[] = $_v['goods_id'];
		}

		// 获取产品信息
		$serv_g = new voa_s_oa_goods_data();
		$goodses = $serv_g->list_by_conds(array('dataid' => $dataids));

		foreach ($list as &$_v) {
			if (empty($goodses[$_v['goods_id']])) {
				continue;
			}

			$_v['subject'] = $goodses[$_v['goods_id']]['subject'];
		}

		// 返回数据
		$this->_result = array(
			'total' => $this->_total,
			'limit' => $this->_perpage,
			'data' => $list
		);

		return true;
	}

	// 浏览排行
	protected function _view() {

		$conds = array('uid' => $this->_member['m_uid']);
		$serv = new voa_s_oa_travel_sharecount();
		$list = $serv->list_by_conds($conds, array($this->_start, $this->_perpage), array('viewcount' => 'DESC'));
		$this->_total = $serv->count_by_conds($conds);
		return empty($list) ? array() : $list;
	}

	// 咨询排行
	protected function _inquiry() {

		$conds = array('uid' => $this->_member['m_uid']);
		$serv = new voa_s_oa_travel_sharecount();
		$list = $serv->list_by_conds($conds, array($this->_start, $this->_perpage), array('inquirycount' => 'DESC'));
		$this->_total = $serv->count_by_conds($conds);
		return empty($list) ? array() : $list;
	}

}
