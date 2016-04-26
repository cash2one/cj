<?php
/**
 * list.php
 * 内部api方法/超级报表---日报列表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_list extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** diy uda 类 */
	private $__diy = null;
	/** service 类 */
	private $__service = null;

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_detail();
		}
		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_data_list();
			$this->_init_diy_data($this->__diy);
		}
	}
	/**
	 * 根据条件查找记录,用于后台日报列表
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 */
	public function result(&$result, $conds, $page_options) {

		$conds = $this->__parse_conds($conds);
		$result['list'] =  $this->_list_reports_by_conds($conds, $page_options);
		$result['total'] = $this->_count_reports_by_conds($conds);

		return true;
	}

	/**
	 * 根据条件查找目录
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @return array $list
	 */
	protected function _list_reports_by_conds($conds, $page_options) {

		$list = array();
		$list = $this->__service->list_by_conds($conds, $page_options, array('updated' => 'DESC'));
		$this->__attach_reserve_columns($list);
		$this->__format_list($list);

		return $list;
	}

	/**
	 * 根据条件计算日报数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_reports_by_conds($conds) {

		$total = $this->__service->count_by_conds($conds);

		return $total;
	}

	/**
	 * 处理查询条件
	 * @param array $conds
	 * @return array
	 */
	private function __parse_conds($conds) {

		$place_result = array();
		if (isset($conds['name']) || (isset($conds['placeregionid']) && $conds['placeregionid'] != 0)) {
			//根据名称和区域查找门店
			$uda_place_list = &uda::factory('voa_uda_frontend_common_place_list');
			$place_cond = array(
				'name' => '',
				'placeregionid' => '',
				'placetypeid' => $this->plugin_setting['placetypeid'],
				'address' => '',
				'lng' => '',
				'lat' => '',
			);
			if (isset($conds['name'])) { //如果有门店名称
				$place_cond['name'] = $conds['name'];
			}
			if (isset($conds['placeregionid']) && $conds['placeregionid'] != 0) { //如果有区域
				$place_cond['placeregionid'] = $conds['placeregionid'];
			}
			$uda_place_list->doit($place_cond, $place_result);
		}


		//拼接所有查询条件
		$search_conds = array();
		if (!empty($place_result)) {
			if ($place_result['option']['total'] > 0) { //根据门店名称和区域取得的门店ID
				$place_ids = array_column($place_result['result'], 'placeid');
				$search_conds['csp_id'] = $place_ids;
			}
		}

		if (isset($conds['created_begintime'])) { //开始时间
			$search_conds['created >=?'] = rstrtotime($conds['created_begintime']);
		}
		if (isset($conds['created_endtime'])) {  //结束时间
			$search_conds['created <?'] = rstrtotime($conds['created_endtime']) + 86400;
		}
		if (isset($conds['contacts'])) {  //结束时间
			$search_conds['m_uid'] = $conds['contacts'];
		}

		return $search_conds;
	}


	/**
	 * 格式化数据列表
	 * @param array $list 列表（引用）
	 */
	private function __format_list(&$list) {
		if ($list) {
			foreach ($list as $k => &$v) {
				$v['created'] = rgmdate($v['created'], 'Y-m-d H:i:s');
			}
		}
	}

	/**
	 * 将后台报表保留字段加入列表
	 * @param array $list 列表（引用）
	 */
	private function __attach_reserve_columns (&$list) {
		$uda_diy = new voa_uda_frontend_diy_data_list();
		$this->_init_diy_data($uda_diy);
		if ($list) {
			$dr_ids = array_column($list, 'dr_id');
			$attach = array();
			$uda_diy->execute(array('dr_id' => $dr_ids, 'page' => '', 'limit' => ''), $attach);
			foreach ($list as $k => &$v) {
				$col = $this->plugin_setting['volume'];
				$v['volume'] = isset($attach[$v['dr_id']]['_'.$col]) ?  $attach[$v['dr_id']]['_'.$col] : 0;
			}
		}
	}


}
