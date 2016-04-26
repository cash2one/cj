<?php
/**
 * 巡店主题表
 * $Author$
 * $Id$
 */

class voa_s_oa_inspect extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化巡店主表数据
	 * @param array &$inspect 巡店信息
	 * @return boolean
	 */
	public function format(&$inspect) {

		// 发起时间
		$inspect['_created'] = rgmdate($inspect['ins_created'], 'Y-m-d H:i');
		list($inspect['_created_ymd'], $inspect['_created_hi']) = explode(' ', $inspect['_created']);
		// 个性化发起时间
		$inspect['_updated_u'] = rgmdate($inspect['ins_updated'], 'u');
		// 备注
		$inspect['_note'] = bbcode::instance()->bbcode2html($inspect['ins_note']);
		return true;
	}

	/**
	 * 检查店铺id是否正常
	 * @param int &$id 店铺id
	 * @param array &$data 当前数据
	 * @param array $odata 旧数据
	 */
	public function chk_csp_id(&$id) {

		$id = (int)$id;
		$shops = voa_h_cache::get_instance()->get('shop', 'oa');
		if (!array_key_exists($id, $shops)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_inspect::SHOP_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

}

