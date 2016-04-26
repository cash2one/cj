<?php
/**
 * 名片夹数据格式化
 * $Author$
 * $Id$
 */

class voa_uda_frontend_namecard_format extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化职位列表
	 * @param array $list
	 * @return boolean
	 */
	public function job_list(&$list) {
		foreach ($list as &$data) {
			$this->job($data);
		}

		return true;
	}

	public function job(&$data) {
		$data['_name'] = rhtmlspecialchars($data['ncj_name']);
		return true;
	}

	/**
	 * 格式化公司列表
	 * @param array $list
	 * @return boolean
	 */
	public function company_list(&$list) {
		foreach ($list as &$data) {
			$this->company($data);
		}

		return true;
	}

	public function company(&$data) {
		$data['_name'] = rhtmlspecialchars($data['ncc_name']);
		return true;
	}

	/**
	 * 格式化名片夹列表
	 * @param array $list
	 * @return boolean
	 */
	public function namecard_list(&$list) {
		foreach ($list as &$data) {
			$this->namecard($data);
		}

		return true;
	}

	public function namecard(&$data) {
		$data['_realname'] = rhtmlspecialchars($data['nc_realname']);
		$data['_wxuser'] = rhtmlspecialchars($data['nc_wxuser']);
		$data['_address'] = rhtmlspecialchars($data['nc_address']);
		$data['_remark'] = bbcode::instance()->bbcode2html($data['nc_remark']);
		$data['_remark_escape'] = rhtmlspecialchars($data['nc_remark']);
		return true;
	}

	/**
	 * 格式化分组列表
	 * @param array $data 数据数组
	 */
	public function folder_list(&$list) {
		foreach ($list as &$data) {
			$this->folder($data);
		}

		return true;
	}

	/**
	 * 格式化分组
	 * @param array $data 名片夹数据
	 */
	public function folder(&$data) {
		$data['_name'] = rhtmlspecialchars($data['ncf_name']);
		$data['_created_u'] = rgmdate($data['ncf_created'], 'u');
		$data['_created'] = rgmdate($data['ncf_created'], 'Y-m-d H:i');

		return true;
	}
}
