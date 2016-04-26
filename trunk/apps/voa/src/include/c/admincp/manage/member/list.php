<?php

/**
 * voa_c_admincp_manage_member_list
 * 员工列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_list extends voa_c_admincp_manage_member_base
{

	public function execute()
	{
		$this->view->set('department_list_url', '/admincp/api/department/list');
		$this->view->set('department_edit_url', '/admincp/api/department/edit');
		$this->view->set('department_delete_url', '/admincp/api/department/delete');
		$this->view->set('department_detail_url', '/admincp/api/department/detail');
		$this->view->set('member_list_url', '/admincp/api/member/list');
		$this->view->set('member_edit_url', '/admincp/api/member/edit');
		$this->view->set('member_delete_url', '/admincp/api/member/delete');
		$this->view->set('member_invite_url', '/admincp/api/member/invite');
		$this->view->set('member_fields_url', '/admincp/api/member/fields');
		$this->view->set('member_detail_url', '/admincp/api/member/detail');
		$this->view->set('member_active_url', '/admincp/api/member/active');
		$this->view->set('member_impmem_url', $this->cpurl($this->_module, $this->_operation, 'impmem', $this->_module_plugin_id));
		$this->view->set('fields', isset($this->_settings['fields']) ? $this->_settings['fields'] : '');

		//获取公司
		$department = null;
		foreach ($this->_departments as $d) {
			if ($d['cd_upid'] == 0) {
				$department = $d;
				break;
			}
		}
		$is_show_tips = 0;
		if (time() < rstrtotime('2015-08-01')) {
			$is_show_tips = empty($_COOKIE['is_show_member_tips']) ? '1' : 0;
		}
		$this->view->set('is_show_tips', $is_show_tips);

		$this->view->set('department', $department);


		/*
		// 查询条件，因为列出全部所以为空
		$search_by = array();
		// 排序
		$orderby = array('m_uid' => 'DESC');
		// 当前页码
		$page = (int)$this->request->get('page');
		$page < 1 && $page = 1;
		// 每页显示数
		$limit = 16;
		// 结果集
		$result = array();
		if (!$this->_uda_member_get->member_search($search_by, $orderby, $page, $limit, $result)) {
			$this->message('error', '检索员工信息发生错误');
		}

		// 取出结果
		list($page, $limit, $total, $pages, $multi, $search_by, $list) = $result;
		// 格式化列表数据
		$this->_uda_member_format->format_list($list);

		// 批量删除提交链接
		$this->view->set('form_delete_action_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		// 单独删除的基本url
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('m_uid'=>'')));
		// 单独编辑的基本url
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('m_uid'=>'')));

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('member_list', $list);
*/
		$this->output('manage/member/list');
	}

}
