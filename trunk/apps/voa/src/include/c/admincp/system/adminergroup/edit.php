<?php
/**
 * voa_c_admincp_system_adminergroup_edit
 * 企业后台/系统设置/后台管理组/编辑管理组
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminergroup_edit extends voa_c_admincp_system_adminergroup_base {
	public function execute(){

		// 重置所有菜单，移除系统固有的菜单
		$this->_module_list = $this->_remove_cpmenu_system_all($this->_module_list);
		$this->_operation_list = $this->_remove_cpmenu_system_all($this->_operation_list);
		$this->_subop_list = $this->_remove_cpmenu_system_all($this->_subop_list);
		$this->view->set('module_list', $this->_module_list);
		$this->view->set('operation_list', $this->_operation_list);
		$this->view->set('subop_list', $this->_subop_list);

		/** 获取当前待操作的管理组id */
		$cag_id	=	$this->request->get('cag_id');
		if ( !$cag_id ) {
			$this->message('error', '请指定要编辑的管理组');
		}
		/** 管理组详情 */
		$groupDetail	=	$this->_group_detail($cag_id);
		/** 管理组不存在 */
		if ( !$groupDetail || !$groupDetail['cag_id'] ) {
			$this->message('error', '对不起，指定的管理组不存在 或 已被删除');
		}
		/** 提交修改动作 */
		if ( $this->_is_post() ) {
			$this->_response_submit_edit($cag_id);
		}
		/** 添加管理组的提交表单目标路径 */
		$this->view->set('editTargetUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('cag_id'=>$cag_id)));
		/** 管理组的默认表单数据填充 */
		$groupDetail['cag_role']	=	explode(',', $groupDetail['cag_role']);
		$this->view->set('groupDetail', $groupDetail);
		$this->output('system/adminergroup/edit_form');
	}
}
