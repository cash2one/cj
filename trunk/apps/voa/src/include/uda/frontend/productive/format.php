<?php
/**
 * voa_uda_frontend_productive_format
 * 统一数据访问/活动/产品应用/数据格式化
 * $Author$
 * $Id$
 */
class voa_uda_frontend_productive_format extends voa_uda_frontend_productive_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化活动/产品主表数据
	 * @param array &$productive 活动/产品信息
	 * @return boolean
	 */
	public function productive(&$productive) {
		/** 发起时间 */
		$productive['_created'] = rgmdate($productive['pt_created'], 'Y-m-d H:i');
		list($productive['_created_ymd'], $productive['_created_hi']) = explode(' ', $productive['_created']);
		/** 个性化发起时间 */
		$productive['_created_u'] = rgmdate($productive['pt_created'], 'u');
		/** 备注 */
		$productive['_note'] = bbcode::instance()->bbcode2html($productive['pt_note']);
		return true;
	}

	/**
	 * 格式化活动/产品列表
	 * @param array $list 活动/产品列表
	 * @return boolean
	 */
	public function productive_list(&$list) {
		foreach ($list as &$productive) {
			$this->productive($productive);
		}

		return true;
	}
}
