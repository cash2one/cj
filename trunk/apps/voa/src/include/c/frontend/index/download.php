<?php
/**
 * 下载pc版快捷方式
 * voa_c_frontend_index_download
 * User: luckwang
 * Date: 28/7/15
 * Time: 16:24
 */


class voa_c_frontend_index_download extends voa_c_frontend_base {


	protected function _before_action($action) {

		$this->_require_login = false;

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		$str = '[{000214A0-0000-0000-C000-000000000046}]' . chr(13);
		$str .= 'Prop3=19,2' . chr(13);
		$str .= '[InternetShortcut]' . chr(13);
		$scheme = config::get('voa.oa_http_scheme');
		$str .= 'URL=' . $scheme . $this->_setting['domain'] . '/pc' . chr(13);
		$str .= 'IDList=' . chr(13);

		$file_name = $this->_setting['sitename'] . '.url';
		$file_size =  strlen($str);

		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length:".$file_size);
		Header("Content-Disposition: attachment; filename=".$file_name);

		echo $str;

		exit;
	}
}
