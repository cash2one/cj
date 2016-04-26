<?php
/**
 * WeixinMsgService.class.php
 * $author$
 */

namespace Common\Service;

class WeixinMsgService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/WeixinMsg");
	}

	/**
	 * 根据 $packageid 获取套件信息
	 * @param string $packageid 套件ID
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_packageid($packageid) {

		return $this->_d->get_by_packageid($packageid);
	}

}
