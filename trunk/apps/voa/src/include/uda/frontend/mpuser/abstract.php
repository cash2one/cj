<?php
/**
 * voa_uda_frontend_mpuser_abstract
 * 统一数据访问/公众号用户/基类
 * $Author$
 * $Id$
 */

class voa_uda_frontend_mpuser_abstract extends voa_uda_frontend_base {
	// 公众号 service 类
	protected $_serv;

	public function __construct() {

		parent::__construct();
		// 初始化 service
		if (null == $this->_serv) {
			$this->_serv = new voa_s_oa_mpuser();
		}
	}

}
