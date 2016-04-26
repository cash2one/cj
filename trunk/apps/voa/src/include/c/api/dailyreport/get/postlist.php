<?php
/**
 * voa_c_api_dailyreport_get_postlist
 * 日报评论列表
 * $Author$
 * $Id$
 */

class voa_c_api_dailyreport_get_postlist extends voa_c_api_dailyreport_base {

	public function execute() {
		/*需要的参数*/
		$fields = array(
			/*日报id*/
			'dr_id' => array('type' => 'string', 'required' => true),
			/*当前页码*/
			'page' => array('type' => 'int', 'required' => false),
			/*每页显示数据数*/
			'limit' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			/*检查参数*/
			return false;
		}
		/** 获取分页参数 */
		$page = $this->request->get('page');
		$limit = $this->request->get('limit');
		if (!$limit || 20 > $limit) {
			$limit = 20;
		}
		list(
				$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $limit);

		//报告id
		$dr_id = $this->request->get('dr_id');
		//查询条件，只查找评论
		$conditions = array('drp_first' => 0,'dr_id' => $dr_id);

		//读取评论内容 及总数
		$serv = &service::factory('voa_s_oa_dailyreport_post', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_conditions($conditions, $this->_start, $this->_perpage);
		$count = $serv->count_by_conditions($conditions);

		//获取用户ID
		$mems = array();
		if ($list) {
			foreach ($list as $key => $row) {
				$mems[$row['m_uid']] = $row['m_uid'];
			}
		}

		//用户头像信息
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids(array_keys($mems));
		voa_h_user::push($users);

		//整理输出
		$uda = &uda::factory('voa_uda_frontend_dailyreport_format');
		$posts = $uda->format_post_reply($list);
		foreach ($posts as &$v) {
			$v['avatar'] = voa_h_user::avatar($v['uid'], isset($users[$v['uid']]) ? $users[$v['uid']] : array());
		}
		unset($v);
		//输出结果
		$this->_result = array(
			'total' => $count,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $posts ? array_values($posts) : array()
		);
		return true;
	}
}
