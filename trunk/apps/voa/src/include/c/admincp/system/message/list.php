<?php

/**
 * @Author: ppker
 * @Date:   2015-07-27 09:49:16
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-06 21:55:49
 * @Description 未读消息
 */
class voa_c_admincp_system_message_list extends voa_c_admincp_system_base {

	public function execute() {

		$search_default = array( //  默认搜索条件
			'title' => ''
		);
		$search_conds = array();   //记住查询条件，填充到视图
		$conditions = array(); // 提供给数据库的查询条件
		$this->_parse_search_cond($search_default, $search_conds, $conditions); // 生产相应数据
		$issearch = $this->request->get('issearch') ? 1 : 0;
		$limit = 12;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		$uid = $this->_user['ca_id']; // 当前登录用户的uid  `ca_id`

		$ep_id = $this->_setting['ep_id'];

		try {
			// 实际查询条件
			$conditions = $issearch ? $conditions : array();
			// 走rpc的方法
			$re_info = $this->_onrequest_rpc($conditions, $page, $ep_id);

			// 处理已读记录的地方
			if ($this->request->get('logid')) {
				$logid = $this->request->get('logid');
				$logid = explode(',', $logid);

				// 通过rpc进行处理
				$rpc = $this->by_rpc_fun("/OaRpc/Rpc/EnterMessage");
				$rpc_mark_end =  $rpc->mark_read($logid, $uid);
				if($rpc_mark_end) return true;
				return false;

			}


		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
		$this->view->set('re_info', $re_info);
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));

		$this->output( 'system/message/list' );
	}


	/**
	 * 重构搜索条件
	 * @param array $searchDefault 默认的查询条件
	 * @param array $searchBy 视图的查询条件
	 * @param array $conditons 数据表的查询条件
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditions) {
		foreach ( $search_default as $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$search_conds[$_k] = $this->request->get($_k);
				if ($_k == 'title') {
					$conditions['title LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} else {
					$conditions[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}

	/**
	 * 通过RPC获取列表进行分页
	 * @param $conditions
	 * @param $page_option
	 */
	protected function _onrequest_rpc($conditions, $page, $ep_id) {

		$uid = $this->_user['ca_id']; // 当前登录用户的uid  `ca_id`
		$domain = config::get('voa.cyadmin_domain.domain_url'); // 总后台domain

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/OaRpc/Rpc/MessageList');

		$rre = $rpc->get_message_list($conditions, $page, $ep_id, $uid);

		// 对获取的数据 进行老框架分页
		$pagerOptions = array(
			'total_items'      => $rre['total'],
			'per_page'         => 12,
			'current_page'     => $rre['page'],
			'show_total_items' => true,
		);
		$multi = pager::make_links( $pagerOptions );
		$end_message_list = isset($rre['data_list']) ? $rre['data_list'] :array();

		return array($multi, $end_message_list);
	}


}
