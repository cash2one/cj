<?php
/**
 * @Author: ppker
 * @Date:   2015-10-19 18:07:02
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-22 15:44:18
 */

class voa_c_cyadmin_enterprise_overdue_list extends voa_c_cyadmin_enterprise_base {

	protected $act_array = array(
		0 => '系统管理员',
		1 => '主管',
		2 => ' 销售人员'
	);

	public function execute() {

		// 小权限
		$act = $this->_user['ca_job'];
		$page = $this->request->get( 'page' );
		$user = $this->_user;
		$uid = (int)$user['ca_id'];
		if(empty($uid)) $this->message('error', '数据异常，非法操作！');
		$uda      = &uda::factory( 'voa_uda_cyadmin_enterprise_overdue' );
		$multi    = '';
		$over_list = array();
		$total    = '';

		// 检测小权限
		$list = $uda->getlist( $page, $over_list, $multi, $total, $uid, $act);

		// $list     = $uda->getlist( $page, $over_list, $multi, $total, $uid );
		$data     = array();
		$tao_data = $this->_domain_plugin_list;

		$tao_data = array_column($tao_data, 'cpg_name', 'cpg_id'); // 套件数据，通过Think框架的RPC 进行URL 获取 by `commom_plugin_group`

		$uda->format( $over_list, $data, $tao_data);

		$this->view->set( 'data', $data );
		$this->view->set( 'multi', $multi );
		$this->view->set( 'total', $total );
		$this->view->set('uid', $uid);

		$this->output( 'cyadmin/enterprise/overdue/list' );
		return true;
	}

}
