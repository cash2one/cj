<?php
/**
 * 企业后台 - 活动 - 编辑/添加
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_edit extends voa_c_admincp_office_campaign_base {

	public function execute() {

		// 如果有POST数据则保存数据,否则显示表单
		if ($_POST) {
			$this->save();
			exit();
		}

		$id = $this->request->get('id');
		$d = new voa_d_oa_campaign_campaign();
		$act = $d->get($id);
		if ($id && ! $act) {
			$this->_error_message('活动不存在');
		}

		// 格式化
		$act['_overtime'] = rgmdate($act['overtime'], 'Y-m-d');
		$act['_time'] = rgmdate($act['overtime'], 'H:i');
		if ($act['_time'] == '23:59') {
			$act['_time'] = '全天';
		}

		$act['_begintime'] = rgmdate($act['begintime'], 'Y-m-d');
		$act['_btime'] = rgmdate($act['begintime'], 'H:i');

		// 输出默认选中部门
		$right = new voa_d_oa_campaign_right();
		$deps = $right->get_right($id);
		$this->deps($deps);

		$this->view->set('act', $act);

		// 时间列表(半小时为一段)
		$this->times();

		// 取得分类列表
		$cats = voa_d_oa_campaign_type::get_type();
		$this->view->set('cats', $cats);
		// 编辑器
		$this->ueditor($act['content']);

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
			$this->_to_queue($act, isset($_POST['deps']) ? $_POST['deps'] : array());
			$this->ajax(1, $this->url('list'));
		} else { // 如果是保存草稿，则跳转到编辑页
			$this->ajax(1, $this->url('edit') . '?id='.$act['id']);
		}
	}
}
