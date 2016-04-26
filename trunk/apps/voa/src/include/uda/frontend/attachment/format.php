<?php
/**
 * 附件数据格式化
 * $Author$
 * $Id$
 */

class voa_uda_frontend_attachment_format extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 过滤附件信息列表
	 * @param array $list
	 * @return boolean
	 */
	public function format_list(&$list) {
		foreach ($list as &$data) {
			$this->format($data);
		}

		return true;
	}

	public function format(&$data, $date_format = 'Y-m-d H:i') {
		$data['_filename'] = rhtmlspecialchars($data['at_filename']);
		$data['_description'] = rhtmlspecialchars($data['at_description']);
		$data['_created_u'] = rgmdate($data['at_created'], 'u');
		$data['_created'] = rgmdate($data['at_created'], $date_format);
		$data['_filesize'] = size_count($data['at_filesize']);
		return true;
	}
}
