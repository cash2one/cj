<?php
/**
 * FormatService.class.php
 * $author$
 */

namespace Guestbook\Service;

class FormatService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化数据
	 * @param array &$data 待格式化操作
	 */
	public function guestbook(&$data) {

		$data['_created'] = rgmdate($data['created']);
		$data['_updated'] = rgmdate($data['updated']);
	}

}
