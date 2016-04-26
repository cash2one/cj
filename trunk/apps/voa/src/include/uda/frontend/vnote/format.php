<?php
/**
 * voa_uda_frontend_vnote_format
 * 统一数据访问/备忘应用/数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_vnote_format extends voa_uda_frontend_vnote_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化备忘主表数据
	 * @param array $vnote
	 * @param array $member
	 * @return boolean
	 */
	public function format(&$vnote, $member = array()) {
		/** 发起时间 */
		$vnote['_created'] = rgmdate($vnote['vn_created'], 'Y-m-d H:i');
		$vnote['_created_md'] = rgmdate($vnote['vn_created'], 'm-d');
		/** 个性化发起时间 */
		$vnote['_created_u'] = rgmdate($vnote['vn_created'], 'u');
		/** 标题 */
		$vnote['_subject'] = rhtmlspecialchars($vnote['vn_subject']);
		/** 备忘详情 */
		$vnote['_message'] = '';

		if (isset($vnote['vnp_message'])) {
			$vnote['_message'] = bbcode::instance()->bbcode2html($vnote['vnp_message']);
		}

		return true;
	}

	/**
	 * 格式化备忘/回复详情
	 * @param array $data
	 */
	public function vnote_post(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['vnp_subject']);
		$data['_message'] = bbcode::instance()->bbcode2html($data['vnp_message']);
		$data['_created_u'] = rgmdate($data['vnp_created'], 'u');
		return true;
	}
}
