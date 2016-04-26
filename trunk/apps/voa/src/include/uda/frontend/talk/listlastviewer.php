<?php
/**
 * voa_uda_frontend_talk_listlastviewer
 * 获取咨询用户列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_listlastviewer extends voa_uda_frontend_talk_abstract {

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

		// 需要提取的参数列表
		$fields = array(
			array('page', self::VAR_INT, '', null, true), // 当前页码
			array('limit', self::VAR_INT, '', null, true), // 每页记录数
			array('uid', self::VAR_INT, '', null, true),
			array('tv_uid', self::VAR_INT, '', null, true)
		);
		// 提取数据
		$conds = array('viewts>?' => 0);
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		list($start, $limit, $page) = voa_h_func::get_limit($conds['page'], $conds['limit']);
		$page_option = array($start, $limit);
		unset($conds['page'], $conds['limit']);

		// 访客服务类
		$serv_lastview = &service::factory('voa_s_oa_talk_lastview');
		$viewers = array();
		if (empty($conds['uid'])) {
			$conds['uid'] = startup_env::get('wbs_uid');
		}
		if (!$viewers = $serv_lastview->list_by_conds($conds, $page_option, array('newct' => 'DESC', 'lastts' => 'DESC'))) {
			return false;
		}

		$tv_uids = array();
		foreach ($viewers as $_v) {
			$tv_uids[] = $_v['tv_uid'];
		}

		// 访客信息入库
		$serv_viewer = &service::factory('voa_s_oa_talk_viewer');
		$out = $serv_viewer->list_by_pks($tv_uids);

		// 合并客户信息
		foreach ($viewers as $_v) {
			$out[$_v['tv_uid']] = array_merge($out[$_v['tv_uid']], $_v);
		}

		return true;
	}

}
