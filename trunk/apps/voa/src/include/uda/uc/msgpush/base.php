<?php
/**
 * voa_uda_uc_msgpush_base
 * 统一数据访问/邮件发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_msgpush_base extends voa_uda_frontend_base {
	/** 消息推送配置信息 */
	protected $_sets = array();

	public function __construct() {

		parent::__construct();

		$app_name = basename(APP_PATH);
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_sets = array(
			'batch_tag_max' => config::get($app_name.'.msgpush.batch_tag_max'),
			'xg_access_id' => $sets['xg_access_id'],
			'xg_secret_key' => $sets['xg_secret_key']
		);
	}

}
