<?php
/**
 * 企业后台 - 活动 - 编辑/添加
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_campaign_add extends voa_c_admincp_office_campaign_base {

	public function execute() {

		// 如果有POST数据则保存数据,否则显示表单
		if ($_POST) {
			$this->save();
			exit();
		}

		// 设置活动默认值
		$act = array('content' => '', 'is_push' => 0, '_time' => '全天', 'is_custom' => 1);

		$this->view->set('act', $act);

		// 时间列表(半小时为一段)
		$this->times();

		// 取得分类列表
		$cats = voa_d_oa_campaign_type::get_type();
		$this->view->set('cats', $cats);

		// 编辑器
		$this->ueditor();

		$this->output('office/campaign/form');
	}

	private function save() {
		// 读取数据
		$uda = &uda::factory('voa_uda_frontend_campaign_campaign');
		$act = array();
		$_POST['uid'] = $this->_user['ca_id'];
		$_POST['username'] = $this->_user['ca_username'];
		$rs = $uda->save($_POST, $act, $error);
		if (! $rs) {
			$this->ajax(0, $error);
		}

		if ($act['is_push']) { // 如果是发布，则跳转到列表页
			$this->_to_queue($act, $_POST['deps']);
			$this->ajax(1, $this->url('list'));
		} else { // 如果是保存草稿，则跳转到编辑页
			$this->ajax(1, $this->url('edit') . '?id='.$act['id']);
		}
	}
}
