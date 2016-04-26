<?php

/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/12/1
 * Time: 19:12
 */
class voa_uda_frontend_association_marklist extends voa_uda_frontend_community_abstract {

	// 列表
	protected $_serv = null;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_association_mark();
		}
	}

	public function get_list_by_mark() {

		$result = $this->_serv->list_all();
		if (!$result) {
			$result = array();
		}

		return $result;
	}
}