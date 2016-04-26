<?php
/**
 * 企业后台 - 活动 - 查看活动
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_view extends voa_c_admincp_office_campaign_base {

	public function execute() {

		$id = intval($this->request->get('id'));
		$d = new voa_d_oa_campaign_campaign();
		$act = $d->get($id);
		if ($id && ! $act) {
			$this->_error_message('活动不存在');
		}

		// 格式化
		$act['_overtime'] = rgmdate($act['overtime'], 'Y-m-d H:i');

		// 取得分类列表
		$act['catname'] = voa_d_oa_campaign_type::get_type($act['typeid']);

		// 读分享数
		$total = new voa_d_oa_campaign_total();
		$row = $total->get_total($id);
		if ($row) {
			$act = array_merge($act, $row);
		}

		$act['share'] = intval($act['share']);
		$act['hits'] = intval($act['hits']);

		$act['_created'] = rgmdate($act['created']);

		$this->view->set('act', $act);

		// 获取签到排行
		$sign_order = $total->sign_order($id);
		$this->view->set('sign_order', $sign_order);

		$this->output('office/campaign/view');
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
			$this->ajax(1, $this->url('edit') . '?id=' . $act['id']);
		}
	}
}
