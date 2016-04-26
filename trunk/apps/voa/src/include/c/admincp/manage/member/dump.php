<?php
/**
 * voa_c_admincp_manage_member_dump
 * 导出通讯录
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_dump extends voa_c_admincp_manage_member_base{

    //deprecated
	public function execute(){
return;
		/** 是否提交搜索 */
		$issearch	=	$this->request->get('issearch');

		/** 是否导出 */
		$isdump		=	$this->request->get('isdump');

		/** 初始化查询条件 */
		$defaults	=	array(
				'cab_realname'		=>	'',
				'cab_mobilephone'	=>	'',
				'cab_active'		=>	'-1',
				'cj_id'				=>	'-1',
				'cd_id'				=>	'-1',
		);
		/** 自上次查询构造初始化查询条件 */
		if ( $issearch ) {
			$searchBy	=	array_merge($defaults, $this->request->getx(array_keys($defaults)));
		} else {
			$searchBy	=	$defaults;
		}

		/** 部门列表 */
		$departmentList	=	$this->_department_list();
		/** 职务列表 */
		$jobList		=	$this->_job_list();
		/** 在职状态 */
		$activeList		=	$this->active_list;

		$page = (int)$this->request->get('page');
		$memberList		=	array();
		$emptyResultTipMessage	=	'指定条件无查询结果';
		$multi					=	'';
		$perpage				=	20;

		/** 提交了搜索 */
		if ( $issearch ) {

			$searchBy['isdump']	=	1;
			$searchBy['_']		=	startup_env::get('timestamp');
			$dump_url = $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, $searchBy, true);
			$this->view->set('dump_url', $dump_url);
			//list($total, $multi, $memberList)	=	$this->_member_search($defaults, $searchBy, $perpage, $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, $searchBy, true));
			$uda_so = &uda::factory('voa_uda_frontend_member_get');
			$result = array();
			$uda_so->member_search($searchBy, array(), $page, $perpage, $result);
			list($page, $limit, $total, $pages, $multi, $searchBy, $memberList) = $result;

		} elseif ( $isdump ) {

			//list(, , $memberList)	=	$this->_member_search($defaults, $searchBy, true, $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, $searchBy, true));
			$uda_so = &uda::factory('voa_uda_frontend_member_get');
			$result = array();
			$uda_so->member_search($searchBy, array(), 0, 0, $result);
			list($page, $limit, $total, $pages, $multi, $searchBy, $memberList) = $result;
			if ( empty($memberList) ) {
				$this->message('error', '没有符合条件的通讯录信息导出');
			}

			$downloadFilename	=	'通讯录';
			$this->_download($downloadFilename, $memberList);

			exit;

		}

		/** 注入变量到模板 */
		$this->view->set('searchBy', $searchBy);
		$this->view->set('departmentList', $departmentList);
		$this->view->set('jobList', $jobList);
		$this->view->set('activeList', $activeList);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->view->set('member_list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('emptyResultTipMessage', $emptyResultTipMessage);
		$this->view->set('issearch', $issearch);

		$this->view->set('formDeleteActionUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('cab_id'=>'')));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('cab_id'=>'')));
		$this->view->set('member_list', $memberList);
		$this->view->set('multi', $multi);
		$this->view->set('perpage', $perpage);

		$this->output('manage/member/search');

	}

	/**
	 * 下载导出的Excel文件
	 * @param string $filename 下载的文件名
	 * @param array $dumpData 导出的数据
	 * @return void
	 */
	private function _download($filename, $dumpData){

		$title_string	=	array();
		$title_width	=	array();
		$row_data		=	array();
		$options		=	array();
		$attrs			=	array();
		$this->gender_list = array_flip($this->gender_list);
		$this->active_list = array_flip($this->active_list);
		list($title_string, $title_width, $row_data)	=	$this->_excel_data($dumpData);
		excel::make_excel_download($filename, $title_string, $title_width, $row_data, $options, $attrs);
	}

}
