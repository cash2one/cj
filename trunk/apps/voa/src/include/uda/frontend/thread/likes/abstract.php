<?php
/**
 * voa_uda_frontend_thread_likes_abstract
 * 统一数据访问/社区应用/评论基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_thread_likes_abstract extends voa_uda_frontend_thread_abstract {

	public function __construct() {
		$this->_serv = new voa_s_oa_thread_likes();
		parent::__construct();
	}

}
