<?php
/**
 * voa_uda_uc_webhost_search
 * uc/web主机/查询
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_webhost_search extends voa_uda_uc_webhost_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取指定的web主机的信息
	 * @param number $web_id
	 * @param array $webhost <strong style="color:red">(引用结果)</strong>web主机信息
	 * @return boolean
	 */
	public function fetch($web_id, &$webhost) {
		$webhost = $this->service->fetch($web_id);
		if (empty($webhost)) {
			return false;
		}
		return true;
	}

}
