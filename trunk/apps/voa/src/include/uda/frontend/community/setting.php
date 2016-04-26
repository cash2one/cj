<?php

/**
 * 微社区配置
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/20
 * Time: 10:50
 */
class voa_uda_frontend_community_setting extends voa_uda_frontend_community_abstract {

	/** service 类 */
	private $__service = null;

	public function __construct() {

		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_community_setting();
		}
	}

	public function execute($request, &$result) {

		$this->_params = $request;
		// 提取用户提交的数据
		$fields = array(
			'offical_img' => array('offical_img', self::VAR_INT, null, null, true),
			'screen' => array('screen', self::VAR_STR, null, null, true),
			'add_forum' => array('add_forum', self::VAR_STR, null, null, true),
			'add_reply_five' => array('add_reply_five', self::VAR_STR, null, null, true),
			'add_reply_fiveth' => array('add_reply_fiveth', self::VAR_STR, null, null, true),
			'add_total' => array('add_total', self::VAR_STR, null, null, true),
			'add_ext_fourth' => array('add_ext_fourth', self::VAR_STR, null, null, true),
			'event_join' => array('event_join', self::VAR_STR, null, null, true),
			'event_sign' => array('event_sign', self::VAR_STR, null, null, true),
			'event_week' => array('event_week', self::VAR_STR, null, null, true),
			'cnvote_join' => array('cnvote_join', self::VAR_STR, null, null, true),
			'cnvote_day' => array('cnvote_day', self::VAR_STR, null, null, true),
			'view_forum' => array('view_forum', self::VAR_STR, null, null, true),
			'view_day' => array('view_day', self::VAR_STR, null, null, true),
			'comment' => array('comment', self::VAR_STR, null, null, true),
			'comment_day' => array('comment_day', self::VAR_STR, null, null, true),
			'levels' => array('levels', self::VAR_ARR, null, null, true),
			'pluginid' => array('pluginid', self::VAR_INT, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$result = $this->__service->update_setting($data);

		return true;
	}

}
