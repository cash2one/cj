<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2015/12/16 0016
 * Time: 20:00
 * Email：muzhitao@vchangyi.com
 */

class voa_uda_frontend_community_dynamic extends voa_uda_frontend_community_abstract {

	protected $_serv = null;

	public function __construct() {
		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_common_dynamic();
		}
	}
}
