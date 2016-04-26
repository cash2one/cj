<?php
/**
 * 新的备忘
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_new extends voa_c_frontend_vnote_base {

	public function execute() {
		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		$weeknames = config::get('voa.misc.weeknames');
		/** 日报时间可选范围:前后15天 */
		$btime = startup_env::get('timestamp') - 86400 * 15;
		$days = array();
		$default_index = 15;
		for ($i = 0; $i < 31; ++ $i) {
			$ts = $btime + $i * 86400;
			$ymdw = voa_h_func::date_fmt('Y m d w', $ts);
			$days[$ymdw['Y'].'-'.$ymdw['m'].'-'.$ymdw['d']] = $ymdw;
		}

		/** 取草稿信息 */
		$data = array();
		$this->_get_draft($data);

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', '/vnote/new?handlekey=post');
		$this->view->set('vnote', array('_message' => isset($data['message']) ? $data['message'] : ''));
		$this->view->set('ccusers', isset($data['ccusers']) ? $data['ccusers'] : array());
		$this->view->set('days', $days);
		$this->view->set('weeknames', $weeknames);
		$this->view->set('default_index', $default_index);

		// 赋值jsapi接口需要的ticket
		$this->_get_jsapi("['startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice',
				'stopVoice', 'onVoicePlayEnd', 'uploadVoice']");
		//$this->view->set('jsapi_debug',true);

		$this->_output('vnote/post');
	}

	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_vnote_insert');
		/** 日报信息 */
		$vnote = array();
		/** 日报详情信息 */
		$post = array();
		/** 抄送人信息 */
		$cculist = array();
		if (!$uda->vnote_new($vnote, $post, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 更新草稿信息 */
		$this->_update_draft(array_keys($cculist));

		/** 把消息推入队列 */
		$this->_to_queue($vnote, $cculist);

		$this->_success_message('发布备忘成功', "/vnote/view/{$vnote['vn_id']}");
	}


}
