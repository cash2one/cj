<?php

/**
 * 活动报名详情
 * $Author$
 * $Id$
 */
class voa_c_frontend_activity_view extends voa_c_frontend_activity_base {

//  不强制登录，允许外部人员访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute() {
		$npe = startup_env::get('wbs_uid');
		$npe = authcode($npe, 'zhoutao', 'ENCODE', '0');
		$npe = urlencode($npe);
		$ac = $this->request->getx();
		$acac = $this->request->get('ac');

		$ac['acid'] = $this->_decode_acid($ac['acid']);
		if (!empty($acac)) {
			switch ($acac) {
//              获取二维码
				case 'getcode' :
					$qdata = array(
						"acid" => $ac['acid'],
						"npe" => $ac['npe']
					);
					$this->getcode($qdata);
					break;
//              二维码签到
				case 'checkin' :
					$m_uid = startup_env::get('wbs_uid');
					$ac['npe'] = authcode($ac['npe'], 'zhoutao', 'DECODE', '0');
					$qdata = array(
						"m_uid" => $m_uid,        //当前用户ID(组织人，如果不是那么报错)
						"acid" => $ac['acid'],    //活动ID
						"npe" => $ac['npe']        //报名人
					);
					$act = &uda::factory("voa_uda_frontend_activity_get");
					$is = null;
//                  判断当前是否组织人，扫描，验证过报名人，则签到成功
					$act->judgem($qdata, $is);
					if ($is === true) {
						$user = voa_h_user::get($ac['npe']);
						$data = array(
							'name' => $user['m_username'],
							'phone' => $user['m_mobilephone'],
							'email' => $user['m_email']
						);
						$this->view->set('user', $data);
						$this->view->set('is', $is);
						$this->view->set('navtitle', '签到成功');
						$this->_output('mobile/' . $this->_plugin_identifier . '/check');
					} else {
						$this->view->set('is', $is);
						$this->view->set('navtitle', '签到失败');
						$this->_output('mobile/' . $this->_plugin_identifier . '/check');
					}
					break;
//              内部人员查看报名人员
				case 'interior' :
					if (!startup_env::get('wbs_uid')) {
						$data['message'] = '只有内部人员可看';
						$this->view->set('data', $data);
						$this->_output('mobile/' . $this->_plugin_identifier . '/error');
					}
					$acid = $ac['acid'];
					try {
						$uda = &uda::factory('voa_uda_frontend_activity_view');
						$data = null;
						$uda->interior($acid, $data);
						if ($data) {
							foreach ($data as $k => &$v) {
								$v['remark'] = htmlspecialchars($v['remark']);
								$v['m_face'] = voa_h_user::avatar($v['m_uid']);
							}
						}
					} catch (help_exception $h) {
						$this->_error_message($h->getMessage());
						return false;
					} catch (Exception $e) {
						logger::error($e);
						$this->_error_message($e->getMessage());
						return false;
					}
					$this->view->set('acid', $acid);
					$this->view->set('data', $data);
					$this->_output('mobile/' . $this->_plugin_identifier . '/interior');
					break;
				// 查看外部报名人员
				case 'exterior' :
					if (!startup_env::get('wbs_uid')) {
						$data['message'] = '只有内部人员可看';
						$this->view->set('data', $data);
						$this->_output('mobile/' . $this->_plugin_identifier . '/error');
					}
					$acid = $ac['acid'];
					try {
						$uda = &uda::factory('voa_uda_frontend_activity_view');
						$data = null;
						$uda->exterior($acid, $data);
						if ($data) {
							foreach ($data as $k => &$v) {
								$v['outname'] = htmlspecialchars($v['outname']);
								$v['remark'] = htmlspecialchars($v['remark']);
								$v['other'] = unserialize($v['other']);
								foreach ($v['other'] as $k => &$v) {
									$v = htmlspecialchars($v);
								}
							}
						}
					} catch (help_exception $h) {
						$this->_error_message($h->getMessage());
						return false;
					} catch (Exception $e) {
						logger::error($e);
						$this->_error_message($e->getMessage());
						return false;
					}
					$this->view->set('acid', $acid);
					$this->view->set('data', $data);
					$this->_output('mobile/' . $this->_plugin_identifier . '/exterior');
					break;
			}
		}

//      请求参数
		$acid = $this->request->get('acid');
		$uda_view = &uda::factory('voa_uda_frontend_activity_view');
		$data = array();
		$exacid = rbase64_encode($acid); //为了给外部链接加密
		$acid = $ac['acid'];
//      没有这条活动，报错
		if (!$uda_view->doit($acid, $data)) {
			$this->view->set('navtitle', '出错了');
			$data['message'] = '没有这条活动，或者请稍后再试';
			$this->view->set('data', $data);
			$this->_output('mobile/' . $this->_plugin_identifier . '/error');
		}

//      生成页面数据
		$view = array();
		$uda_view->format($data, $view);
		$view['exacid'] = $exacid;
		$view['npe'] = $npe;
		$view['out-people-join'] = $view['not-allow-out-people'] == "1" ? "0" : "1";

//      判断当前用户是不是能报名
		$now_people = startup_env::get('wbs_uid');
		if (!empty($now_people)) {
			$check_join = new voa_uda_frontend_activity_sign();
			$member = voa_h_user::get($now_people);
			$cd_id = $member['cd_id'];
			$can_join = $check_join->check_join($now_people, $cd_id, $acid);
			if ($can_join == 'true') {
				$can_join = 0;
			}
			$view['now_user'] = $member['m_username'];
			$view['can_join'] = $can_join;
		}

		// 二维码acid的加密
		$view['acidm'] = authcode($view['acid'], 'zhoutao', 'ENCODE', '0');
		$view['acidm'] = urlencode($view['acidm']);

		// 应用默认图标
		$icon_url = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		// 站点域名
		$icon_url .= $this->_setting['domain'] . '/admincp/static/images/application/activity_big.png';

		// 分享数据
		$share_data = array(
			'title' => '',// 分享标题
			'desc' => rsubstr(strip_tags(str_replace('&nbsp;', '', $view['content'])), 70, ' ...'),// 分享描述
			//'link' => '',// 分享链接
			'imgUrl' => $icon_url,// 分享图标
			//'type' => '',// 分享类型,music、video或link，不填默认为link
			//'dataUrl' => '',// 如果type是music或video，则要提供数据链接，默认为空
			//'cb_success' => '',// 成功时的回调函数名
			//'cb_cancel' => ''// 失败时的回调函数名
		);
		if (!empty($view['image'])) {
			foreach ($view['image'] as $_attach) {
				$share_data['imgUrl'] = voa_h_attach::attachment_url($_attach['aid']);
				break;
			}
			unset($_attach);
		}

		if ($view['in'] == 0) {
			$share_data['title'] = $view['now_user'] . '报名了《' . $view['title'] . '》活动' . '你也赶紧来参与吧';
		} else {
			$share_data['title'] = $view['title'];
		}
		//当前外部人员是否报名过
		$view['outside_is_apply'] = isset($_COOKIE['activity_' . $acid]) ? 1 : 0;

		$this->view->set('share_data', $share_data);

//      引入应用模板
		$this->view->set('editurl', '/frontend/activity/new/?acid=' . $acid . '&pluginid=' . startup_env::get('pluginid'));
		$this->view->set('data', $view);
		$this->view->set('navtitle', $view['title']);
		$this->_output('mobile/' . $this->_plugin_identifier . '/view');

		return true;
	}

//  获取二维码
	private function getcode($qdata) {
		$acid = $qdata['acid'];
		$npe = $qdata['npe'];   //内部报名人ID
		$uda_code = new voa_uda_frontend_activity_view();
		$uda_code->qrcode($acid, $npe, '', 0);
		return true;
	}
}
