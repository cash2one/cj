<?php
/**
 * voa_uda_frontend_footprint_format
 * 统一数据访问/销售轨迹应用/数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_footprint_format extends voa_uda_frontend_footprint_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化轨迹主表数据
	 * @param array &$footprint
	 * @return boolean
	 */
	public function format(&$footprint) {
		/** 发起时间 */
		$footprint['_created'] = rgmdate($footprint['fp_created'], 'Y-m-d H:i');
		/** 个性化发起时间 */
		$footprint['_created_u'] = rgmdate($footprint['fp_created'], 'u');
		/** 客户名称 */
		$footprint['_subject'] = rhtmlspecialchars($footprint['fp_subject']);
		/** 地理位置信息 */
		$footprint['_address'] = rhtmlspecialchars($footprint['fp_address']);
		// 轨迹类别
		$footprint['_type'] = isset($this->_sets['types'][$footprint['fp_type']]) ? $this->_sets['types'][$footprint['fp_type']] : $footprint['fp_type'];
		// 拜访时间
		$footprint['_visittime'] = rgmdate($footprint['fp_visittime']);
		// 个性化显示拜访时间
		$footprint['_visittime_u'] = rgmdate($footprint['fp_visittime'], 'u');
		return true;
	}

	/**
	 * 格式化轨迹列表
	 * @param array &$list
	 * @return boolean
	 */
	public function footprint_list(&$list) {
		foreach ($list as &$data) {
			$this->format($data);
		}

		return true;
	}

	/**
	 * 格式化轨迹回复信息列表
	 * @param unknown $list
	 * @return boolean
	 */
	public function post_list(&$list) {
		foreach ($list as &$data) {
			$this->format_post($data);
		}

		return true;
	}

	public function format_post(&$post) {
		/** 发起时间 */
		$post['_created'] = rgmdate($post['fppt_created'], 'Y-m-d H:i');
		/** 个性化发起时间 */
		$post['_created_u'] = rgmdate($post['fppt_created'], 'u');
		/** 标题 */
		$post['_subject'] = rhtmlspecialchars($post['fppt_subject']);
		/** 详情 */
		$post['_message'] = bbcode::instance()->bbcode2html($post['fppt_message']);
	}
}
