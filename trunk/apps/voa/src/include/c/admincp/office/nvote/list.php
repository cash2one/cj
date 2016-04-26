<?php

/**
 * voa_c_admincp_office_nvote_list
 * 投票调研-列表
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:42
 */
class voa_c_admincp_office_nvote_list extends voa_c_admincp_office_nvote_base {

	public function execute() {
		//获取活动列表
		$result = $this->__get_list();

		//获取分页信息
		$multi = $this->_get_multi($result);

		//搜索表单target url
		$this->view->set('search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		//浏览活动 url
		$this->view->set('view_url', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('nv_id' => '')));

		//删除活动 url
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array()));

		$this->view->set('multi', $multi);
		$this->view->set('list', $result['list']);
		$this->view->set('total', $result['count']);

		//渲染
		$this->output('office/nvote/list');
	}

	//获取投票调研列表
	private function __get_list() {

		//初始页码
		$this->_init_page();

		$condi = array(
			'subject' => '', //活动名称
			'vote_status' => 0, //活动状态
			'start_date' => '', //开始时间
			'end_date' => '',  //结束时间
			'show_name' => 0, //是否实名投票
			'submit_uids' => array(), //发起人
		);


		$get = $this->request->getx();

		$this->_init_params($get, $condi);

		//判断是否为仅查询管理员
		if (!empty($get['is_admin']) && $get['is_admin'] == 1) {
			$get['submit_uids'] = json_encode(array());
		} else {
			// 选人组件默认值
			$temp = array();
			if (!empty($get['submit_uids'])) {

				// 查询人名
				$serv_mem = &service::factory('voa_s_oa_member');
				$user_data = $serv_mem->fetch_all_by_ids($get['submit_uids']);

				foreach ($get['submit_uids'] as $_uids) {
					foreach ($user_data as $_u_data) {
						if ($_uids == $_u_data['m_uid']) {
							$temp[] = array(
								'm_uid' => $_uids,
								'm_username' => $_u_data['m_username'],
								'selected' => (bool)true,
							);
							break;
						}
					}
				}
			}
			$get['submit_uids'] = json_encode($temp);
		}
		//分配查询条件
		$this->view->set('search_conds', $get);
		unset($get);

		$this->__get_submit_usernames($condi['submit_uids']);


		// 载入搜索uda类
		$uda_search = &uda::factory('voa_uda_frontend_nvote_list');

		try {
			// 数据结果
			$result = array();
			if (!$uda_search->search($condi, $result, $this->_page)) {
				$this->message('error', $uda_search->errmsg);

				return false;
			}

			if ($result['list']) {
				$uids = array_column($result['list'], 'submit_id');
				$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $servm->fetch_all_by_ids($uids);
				$usernames = array_column($users, 'm_username', 'm_uid');
				$usernames[0] = '后台管理员';
				$this->view->set('usernames', $usernames);
			}

			return $result;
		} catch (help_exception $h) {
			$this->_error_message('系统发生内部错误，错误编码:-9999');
		} catch (Exception $e) {
			logger::error(print_r($e, true));
			$this->_error_message('系统发生内部错误，错误编码:-9999');
		}
	}


	/**
	 * 获取发起人查询组件已选的用户名
	 * @param $submit_uids
	 */
	private function __get_submit_usernames($submit_uids) {
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids($submit_uids);
		$usernames = array();
		if ($users) {
			foreach ($users as $user) {
				$usernames[] = array(
					'm_uid' => $user['m_uid'],
					'm_username' => $user['m_username'],
					'selected' => (bool)true,
				);
			}
		}
		$this->view->set('submit_usernames', json_encode($usernames));

	}
}
