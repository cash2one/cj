<?php
/**
 * view.php
 * 内部api方法/超级报表查看
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_view extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 类型 service 类 */
	private $__service = null;
	/** diy uda 类 */
	private $__diy = null;

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
			$this->__diy = new voa_uda_frontend_diy_data_get();
		}
	}

	/**
	 * 查找指定日期报表
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function get_view(array $request, array &$result) {

		// 定义参数请求规则
		$fields = array(
			'dr_id' => array(
				'dr_id', parent::VAR_STR,
				array($this->__service, 'validator_dr_id'),
				null, false,
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$uid = $this->member['m_uid'];

		//取得门店
		$shop_list = array();
		$shop_request = array(
			'uid' => $uid,
			'name' => '',
			'placeregionid' => '',
			'placetypeid' => $this->plugin_setting['placetypeid'],
			'address' => '',
			'lng' => '',
			'lat' => ''
		);
		$uda_shop = &uda::factory('voa_uda_frontend_common_place_list');
		$uda_shop->doit($shop_request, $shop_list);
		$users = $shop_list['placemember'];

		//$csp_id = 8;      					//(等待调用接口数据)

		// 取得参数
		$dr_id = $this->__request['dr_id'];

		// 取回日报详情
		$result = $this->__service->get_detail_by_dr_id(array('dr_id' => $dr_id));
		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DAILYREPORT_ERROR);
		}

		//检查用户是否有更改权限（若现用户绑定的门店与日报记录的门店一致，则有权限）
		/* if ($csp_id != $result['csp_id']) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_RIGHT_ERROR);
		} */

		//取回日报数据
		$diy_uda_data_get = new voa_uda_frontend_diy_data_get();
		$this->_init_diy_data($diy_uda_data_get);  //设置选项
		$daily = array();
		$diy_uda_data_get->execute(array('dr_id' => $result['dr_id']), $daily);

		//取回模板
		$templates = array();
		$uda_column_list = new voa_uda_frontend_diy_column_list();
		$this->_init_diy_data($uda_column_list);  //设置选项
		$uda_column_list->execute(array(), $templates);

		//将日报数据合并到模板中
		if ($templates) {
			foreach ($templates as &$template) {
				$field = '_'.$template['tc_id'];
				$template['fieldvalue'] = $daily[$field];
			}
		}

		//格式化输出
		$s_tmplate = new voa_s_oa_superreport_template();
		$result = $s_tmplate->format_template($templates);

		return true;
	}

}
