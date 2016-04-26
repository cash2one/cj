<?php
/**
 * add.php
 * 内部api方法/超级报表查看  （已提交报表/未提交报表/全部  ）门店
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_shops extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 类型 service 类 */
	private $__service = null;

	CONST SUBMITED = 1;   //已提交门店
	CONST UNSUBMITED = 2; //未提交门店
	CONST ALL = 3;        //全部门店

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_dailyreport();
		}
	}

	/**
	 * 获取门店列表
	 * @param array $request 输入参数
	 * @param array $result 输入结果（引用）
	 * @return boolean
	 */
	public function list_shops($request, &$result) {

		$ident = $request['ident'];
		//若请求的是已提交、未提交门店列表，则需要时间参数
		if ($ident == 1 || $ident == 2) {
			// 定义参数请求规则
			$fields = array(
				// 请求参数 date(时间)
				'date' => array(
					'date', parent::VAR_STR,
					array($this->__service, 'validator_date'),
					null, false
				)
			);

			// 检查过滤，参数
			if (!$this->extract_field($this->__request, $fields, $request)) {
				return false;
			}
		}

		//获取门店列表
		$shop_list = array();
		$shop_request = array(
			'uid' => $request['uid'],
			'name' => '',
			'placeregionid' => '',
			'placetypeid' => $this->plugin_setting['placetypeid'],
			'address' => '',
			'lng' => '',
			'lat' => ''
		);
		$uda = &uda::factory('voa_uda_frontend_common_place_list');
		if (!$uda->doit($shop_request, $shop_list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;

			return true;
		}
		//若请求为所有门店，则返回所有提交门店列表
		$result['total'] = $shop_list['option']['total'];
		$shops = $shop_list['result'];

		/**已提交门店、未提交门店 */
		$submited = array();
		$unsubmited = array();
		if ($ident == self::SUBMITED || $ident == self::UNSUBMITED) {
			$s_detail = new voa_s_oa_superreport_detail();
			$details = $s_detail->list_by_conds(array('cdate' => $this->__request['date']));
			if ($details) {
				$submited_ids = array_column($details, 'csp_id');
				foreach ($shops as $v) {
					if (in_array($v['placeid'], $submited_ids)){ //已提交门店
						$submited[] = $v;
					} else { //未提交门店
						$unsubmited[] = $v;
					}
				}

			} else {
				$unsubmited = $shops;
			}
		}

		$result['submited_list'] = $this->__format_shops($submited, $shop_list);
		$result['submited_total'] = count($submited);
		$result['unsubmited_list'] = $this->__format_shops($unsubmited, $shop_list);
		$result['unsubmited_total'] = count($unsubmited);
		$result['all_list'] = $this->__format_shops($shops, $shop_list);
		$result['all_total'] = count($shops);

		return true;
	}

	public function list_shops_for_month($request, &$result) {

		//获取门店列表
		$shop_list = array();
		$shop_request = array(
			'uid' => $request['uid'],
			'name' => '',
			'placeregionid' => '',
			'placetypeid' => $this->plugin_setting['placetypeid'],
			'address' => '',
			'lng' => '',
			'lat' => ''
		);
		$uda = &uda::factory('voa_uda_frontend_common_place_list');
		if (!$uda->doit($shop_request, $shop_list)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;

			return true;
		}
		//返回所有提交门店列表
		$result['total'] = $shop_list['option']['total'];
		$shops = $shop_list['result'];

		$result['list'] = $this->__format_shops($shops, $shop_list);

		return true;
	}

	/**
	 * 格式化门店列表
	 * @param array $shops
	 * @param array $shop_list
	 * @return array
	 */
	private  function __format_shops($shops, $shop_list) {

		$result = array();
		if ($shops) {
			foreach ($shops as $k => $shop) {
				$result[$k]['csp_id'] = $shop['placeid'];
				$result[$k]['csp_name'] = $shop['name'];
				$result[$k]['address'] = $shop['address'];
				$result[$k]['area'] = $this->__union_area($shop_list['placeregion'][$shop['placeid']]);
			}
		}

		return $result;
	}

	/**
	 * 拼接各级区域
	 * @param array $area
	 * @return string
	 */
	private  function __union_area($area) {

		$str = '';
		if (isset($area) && !empty ($area)) {
			ksort($area);
			$arr = array_column($area, 'name');
			$str = implode('-', $arr);
		}

		return $str;
	}
}
