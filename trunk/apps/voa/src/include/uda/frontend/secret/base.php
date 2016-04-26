<?php
/**
 * voa_uda_frontend_secret_base
 * 统一数据访问/秘密应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_secret_base extends voa_uda_frontend_base {

	/** 配置信息 */
	protected $_sets = array();

	/**
	 * 内容表数据类型文字描述
	 * @var array
	 */
	public $post_first_type = array(
			voa_d_oa_secret_post::FIRST_YES => '主题',
			voa_d_oa_secret_post::FIRST_NO => '回复',
	);

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.secret.setting', 'oa');
	}

	/**
	 * 计算指定的主题id的回复数
	 * @param array $st_ids
	 * @param array $replies_count_list
	 * @return boolean
	 */
	public function secret_replies_count($st_ids, &$replies_count_list) {

		// 计算主题的回复数
		$serv_post = &service::factory('voa_s_oa_secret_post', array('pluginid' => startup_env::get('pluginid')));
		$replies_count_list = array();
		foreach ($serv_post->count_group_by_st_ids($st_ids) as $_st_id => $_st) {
			$replies_count_list[$_st_id] = $_st['_count'];
		}

		return true;
	}

}
