<?php

/**
 * 列出所有员工信息(包括头像/名称/uid/职位)
 * $Author$
 * $Id$
 */
class voa_c_frontend_addressbook_list extends voa_c_frontend_addressbook_base {

	public function execute() {

		$params = array(
			'nca_id' => rintval($this->request->get('nca_id')),
			'navtitle' => $this->request->get('navtitle'),
			'pluginid' => $this->request->get('pluginid')
		);
		$url = '/h5/index.html#/app/page/contacts/contacts-department?' . http_build_query($params);
		$this->redirect($url);
		return true;
		/** 获取部门信息 */
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		/** 获取职位信息 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');
		/** 搜索条件 */
		$sotext = (string)$this->request->get('sotext');
		/** 读取员工信息 */
		//$servm = &uda::factory('voa_uda_frontend_member_getuserlist', array('pluginid' => 0));
		$servm = &uda::factory('voa_s_oa_member', array('pluginid' => 0));
		if (!empty($sotext)) {
			/** 根据用户名搜索通讯录信息 */
			//$serv_addrso = &uda::factory('voa_uda_frontend_member_search', array('pluginid' => 0));
			$serv_addrso = &uda::factory('voa_s_oa_member_search', array('pluginid' => 0));
			/** 搜索条件 */
			$conditions = array(
				'ms_message' => array('%' . $sotext . '%', 'like')
			);
			/** 从搜索表取通讯录数据 */
			$list_so = $serv_addrso->fetch_by_conditions($conditions);
			//$list_so = $serv_addrso->get_purview($this->_user['m_uid'],$conditions);
			/** 取出 nc_id */
			$m_uids = array();
			foreach ($list_so as $v) {
				$m_uids[] = $v['m_uid'];
			}
			$members = $servm->fetch_all_by_ids($m_uids);
			$this->view->set('list', $members);
		} else {
			$members = $servm->fetch_all();
			// $members =$servm->fetch_all_purview($this->_user['m_uid']);
			/** 拼凑返回数据 */
			$list = array();
			foreach ($members as $k => $v) {
				if (empty($departments[$v['cd_id']])) {
					continue;
				}

				if (!isset($departments[$v['cd_id']]['_num'])) {
					$departments[$v['cd_id']]['_num'] = 1;
				} else {
					$departments[$v['cd_id']]['_num']++;
				}
				$list[$v['cd_id']][] = $v;
				voa_h_user::push($v);
			}

			$this->view->set('list', $list);
		}

		$this->view->set('sotext', $sotext);
		$this->view->set('jobs', $jobs);
		$this->view->set('departments', $departments);
		$this->view->set('navtitle', '通讯录');

		$this->_output('addressbook/list');
	}
}
