<?php
class voa_c_cyadmin_content_join_new extends voa_c_cyadmin_content_join_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_join_list');
		$data = array();
		$data['jobname'] = strip_tags($this->request->post('jobname'));
		$data['jobdesc'] = $this->request->post('jobdesc');
		$data['jsort'] = $this->request->post('jsort');
		$data['is_publish'] = !empty($_POST['is_publish']) ? $_POST['is_publish'] : 1;
		$this->_is_legal($data['jobname'], 2, 100, '标题长度在2-100字符', 'utf-8');
		$this->_is_legal($data['jobdesc'], 10, 10000, '内容长度在10-10000字符', 'utf-8');
		$this->_is_negative($data['jsort']);
		if ($_POST['ac'] == 'add') { // 代表进行新增
			if ($uda->add_job($data)) {
				$this->message('success', '新增岗位成功', $this->cpurl($this->_module, 'join', 'list'), false);
			} else {
				$this->message('error', '新增失败！');
			}
		} elseif ($_POST['ac'] == 'update') { // 代表更新
			$jid = $_POST['jid'];
			if ($uda->update_job($jid, $data)) {
				$this->message('success', '更新岗位成功', $this->cpurl($this->_module, 'join', 'list'), false);
			} else {
				$this->message('error', '更新失败！');
			}
		} else {
			$this->message('error', '非法操作！');
		}
	}
}
