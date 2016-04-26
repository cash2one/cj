<?php
/**
 * power.php
 * 内部api方法/用户权限
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_power extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** diy uda 类 */
	private $__diy = null;

	const MASTER = 1;   //店长
	const CHARGER = 2;  //区域负责人
	const OTHER = 3;    //其他相关人

	/**
	 * 初始化
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_common_place_member_get();
		}

	}

	/**
	 * 取得权限
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)月报信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function get_power(array $request, array &$result) {


		$uid = $this->member['m_uid'];
		$conds = array(
			'uid' => array($uid),
			'placetypeid' => $this->plugin_setting['placetypeid']
		);
		$return = array();
		$this->__diy->doit($conds, $return);

		$result = array();
		if (isset($return[$uid])) {
			if (!empty($return[$uid]['placeregion'][voa_d_oa_common_place_member::LEVEL_CHARGE])){
				$result['power'] = self::CHARGER;
				$result['placeregion'] = $return[$uid]['placeregion'][voa_d_oa_common_place_member::LEVEL_CHARGE];
			} elseif (!empty($return[$uid]['place'][voa_d_oa_common_place_member::LEVEL_CHARGE])){
				$result['power'] = self::MASTER;
				$result['placeid'] = array_shift($return[$uid]['place'][voa_d_oa_common_place_member::LEVEL_CHARGE]);
			} else {
				$result['power'] = self::OTHER;
			}
		} else {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_RIGHT_ERROR);
		}

		return true;
	}

}
