<?php
/**
 * FormatService.class.php
 * $author$ zhubeihai
 */

namespace Sales\Service;

class FormatService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 客户信息格式化数据
	 * @param array &$data 待格式化操作
	 */
	public function customer_format(&$data) {

		$data['_created'] = rgmdate($data['cg_created']);
		$data['_updated'] = rgmdate($data['cg_updated']);

		return true;
	}

	/**
	 * 商机信息格式化数据
	 * @param array &$data 待格式化操作
	 */
	public function business_format(&$data) {

		foreach ($data as $_key => $_val) {
			$data[$_key]['_created'] = rgmdate($data[$_key]['sb_created']);
			$data[$_key]['_updated'] = rgmdate($data[$_key]['sb_updated']);
		}

		return true;
	}

	/**
	 * 联合跟进人格式化数据
	 * @param array &$data 待格式化操作
	 */
	public function partner_format(&$data) {

		return true;
	}

	/**
	 * 商机状态变更记录列表查询格式化数据
	 * @param array &$data 待格式化操作
	 */
	public function record_format(&$data) {

		return true;
	}


}
