<?php
/**
 * voa_uda_frontend_talk_chatrcd
 * sales 聊天记录
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_chatrcd extends voa_uda_frontend_talk_abstract {
	// 初始化
	const FLAG_INIT = 1;
	// 读取最新
	const FLAG_LATEST = 2;
	// 读取旧记录
	const FLAG_RECORD = 3;

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 执行
	 * @param array $in 输入
	 * @param array $out 输出
	 * @return boolean
	 */
	public function execute($in, &$out = null) {

		// 输入参数
		$this->_params = $in;

		// 聊天记录服务类
		$serv_viewer = &service::factory('voa_s_oa_talk_wechat');
		$serv_viewer->set_params($in);

		// 获取 sales uid 和客户 uid
		$uid = 0;
		$tv_uid = 0;
		if (!$serv_viewer->chk_sales($uid, $tv_uid)) {
			return false;
		}

		// 需要提取的参数列表
		$fields = array(
			array('tw_id', self::VAR_INT, '', null, null), // 最后一次取聊天记录的自增id
			array('page', self::VAR_INT, '', null, null), // 当前页码
			array('limit', self::VAR_INT, '', null, null), // 每页记录数
		);
		// 提取数据
		$data = array();
		if (!$serv_viewer->extract_field($data, $fields)) {
			return false;
		}

		// 分页信息
		list($start, $limit, $page) = voa_h_func::get_limit($data['page'], $data['limit']);
		$page_option = array($start, $limit);

		// 查询条件
		$conds = array(
			'`uid`=?' => $uid,
			'`tv_uid`=?' => $tv_uid,
		);

		// 读取操作标志
		$flag = (int)$this->get('flag');
		switch ($flag) {
			case self::FLAG_INIT: // 读取聊天记录初始化
				$orderby = array('tw_id' => 'DESC');
				break;
			case self::FLAG_LATEST: // 读取最新聊天记录
				$conds['`tw_id`>?'] = $data['tw_id'];
				$orderby = array('tw_id' => 'ASC');
				break;
			case self::FLAG_RECORD: // 读取旧的聊天记录
				$conds['`tw_id`<?'] = $data['tw_id'];
				$orderby = array('tw_id' => 'DESC');
				break;
			default: // 默认为聊天记录初始化
				$orderby = array('tw_id' => 'DESC');
				break;
		}

		// 读取记录
		$out = $serv_viewer->list_by_conds($conds, $page_option, $orderby);

		return true;
	}

}
