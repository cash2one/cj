<?php
/**
 * voa_uda_frontend_notice_format
 * 统一数据访问/通知公告/格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_notice_format extends voa_uda_frontend_notice_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化通知公告信息
	 * @param array $data
	 * @return boolean
	 */
	public function format(&$data, $attach_view_url_base = '') {
		$data['_created'] = rgmdate($data['nt_created'], 'Y-m-d H:i');
		$data['_created_u'] = rgmdate($data['nt_created'], 'u');
		$data['_created_hi'] = rgmdate($data['nt_created'], 'H:i');
		$data['_updated'] = rgmdate($data['nt_updated'], 'Y-m-d H:i');
		$data['_updated_u'] = rgmdate('nt_updated', 'u');
		$receiver = (array)@unserialize($data['nt_receiver']);
		$data['_remindtime'] = rgmdate($data['nt_remindtime'], 'Y-m-d H:i');
		if ($attach_view_url_base) {
			$data['_message'] = preg_replace('/\[attach\](\d+)\[\/attach\]/', $attach_view_url_base.'\1', $data['nt_message']);
		} else {
			$data['_message'] = $data['nt_message'];
		}

		$data['_receiver'] = array();
		foreach ($receiver as $_uid => $_time) {
			$data['_receiver'][$_uid] = array(
				'time' => $_time ? rgmdate($_time, 'Y-m-d H:i') : '',
				'time_u' => $_time ? rgmdate($_time, 'u') : '',
			);
		}

		return true;
	}

	/**
	 * 格式化通知公告列表信息
	 * @param array $list
	 * @return boolean
	 */
	public function format_list(&$list) {
		foreach ($list as &$data) {
			$this->format($data);
		}

		return true;
	}

	public function message($callback, &$message) {

	}
}
