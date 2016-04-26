<?php
/**
* 统计通知
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_tjnotify extends voa_c_admincp_office_exam_base {

	public function execute() {
		$ids = 0;
		$notify = $this->request->get('notify');
		$id = $this->request->get('id');

		if ($notify) {
			$ids = rintval($notify, true);
		} elseif ($id) {
			$ids = rintval($id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要提醒的考生');
		}

		$s_tj = new voa_s_oa_exam_tj();
		$tjs = $s_tj->list_by_ids($id);
		foreach($tjs as $tj) {
			$m_uids[] = $tj['m_uid'];
			$paperid = $tj['paper_id'];
		}

		$s_member = new voa_s_oa_member();
		$members = $s_member->fetch_all_by_ids($m_uids);
		foreach($members as $member) {
			$tousers[] = $member['m_openid'];
		}

		// 发送消息
		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get($paperid);
		$serv_qy = voa_wxqy_service::instance();
		$url = 'http://' . $this->_setting['domain'] . '/Exam/Frontend/Index/PaperDetail?paper_id=' . $paper['id'];
		$picurl = voa_h_attach::attachment_url($paper['cover_id']);
		$data = array(
			'title' => '【考试提醒】您有一门考试需要参与',
			'description' => "试卷名称：{$paper['name']}\n考试说明：".msubstr($paper['intro'], 0, 60),
			'url' => $url,
			'picurl' => $picurl,
		);

		$serv_qy->post_news($data, $this->_module_plugin['cp_agentid'], $tousers);
		//$this->message('success', '提醒成功！', $_SERVER['HTTP_REFERER']);
		$this->redirect($_SERVER['HTTP_REFERER']);
	}

}
