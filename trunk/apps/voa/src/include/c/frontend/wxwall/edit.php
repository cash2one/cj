<?php
/**
 * 编辑微信墙信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_wxwall_edit extends voa_c_frontend_wxwall_base {
	protected $_ww_id;
	protected $_wall;

	public function execute() {
		/** 获取微信墙信息 */
		$this->ww_id = intval($this->request->get('ww_id'));
		$serv_w = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$this->_wall = $serv_w->fetch_by_id($this->ww_id);
		if (empty($this->_wall)) {
			$this->_error_message('当前微信墙记录不存在');
		}

		/** 如果已经结束 */
		if ($this->_wall['ww_endtime'] < startup_env::get('timestamp')) {
			$this->_error_message('当前微信墙已经结束, 不能编辑');
		}

		/** 处理编辑 */
		if ($this->_is_post()) {
			$this->_edit();
		}

		/** form 地址 */
		$form_action = '/wxwall/edit/'.$this->ww_id.'/post';
		/** 时间格式化 */
		$this->_wall['_begintime'] = rgmdate($this->_wall['ww_begintime'], 'Y-m-d H:i');
		$this->_wall['_endtime'] = rgmdate($this->_wall['ww_endtime'], 'Y-m-d H:i');
		$this->view->set('navtitle', '微信墙编辑');

		$this->_output('wxwall/post');
	}

	/** 编辑操作 */
	protected function _edit() {
		/** 微信墙开始/结束时间 */
		$begintime = rstrtotime($this->request->get('begintime'));
		$endtime = rstrtotime($this->request->get('endtime'));
		if ($endtime < startup_env::get('timestamp')) {
			$this->_error_message('结束时间必须大于当前时间');
		}

		if ($begintime >= $endtime) {
			$this->_error_message('结束时间必须大于开始时间');
		}

		/** 主题/内容 */
		$subject = trim($this->request->get('subject'));
		$message = trim($this->request->get('message'));
		if (0 >= strlen($subject)) {
			$this->_error_message('主题不能为空');
		}

		/** 是否启用/回复是否需要验证/最大回复数 */
		$isopen = intval($this->request->get('isopen'));
		$postverify = intval($this->request->get('postverify'));
		$maxpost = intval($this->request->get('maxpost'));

		/** 重置密码标识 */
		$resetp = intval($this->request->get('resetp'));

		$serv_w = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_w->begin();

			/** 入库 */
			$wxwall = array(
				'ww_subject' => $subject,
				'ww_message' => $message,
				'ww_begintime' => $begintime,
				'ww_endtime' => $endtime,
				'ww_isopen' => $isopen,
				'ww_postverify' => $postverify,
				'ww_maxpost' => $maxpost
			);

			if (!empty($resetp)) {
				/** 密码 */
				$passwd = random(8);
				/** 干扰码 */
				$salt = random(4);
				//$wxwall['ww_passwd'] = memberPassword(md5($passwd), $salt);
				$wxwall['ww_salt'] = $salt;

				/** 用户名/密码 */
				$wxwall['_passwd'] = $passwd;
			}

			$serv_w->update($wxwall, array('ww_id' => $this->ww_id));

			$serv_w->commit();
		} catch (Exception $e) {
			$serv_w->rollback();
			$this->_error_message('微信墙新增操作失败');
		}

		/** 推入消息队列 */

		$this->_success_message('编辑操作成功', "/wxwall/view/{$this->ww_id}");
	}
}
