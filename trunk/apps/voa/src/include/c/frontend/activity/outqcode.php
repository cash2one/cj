<?php

/**
 * voa_c_frontend_activity_outsign
 * 外部人员二维码操作
 * Created by zhoutao.
 * Created Time: 2015/5/22  11:00
 */
class voa_c_frontend_activity_outqcode extends voa_c_frontend_activity_base {

	//不强制登录，允许外部人员访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute() {

		//外部人员提交信息，获取签到二维码
		$ispost = $this->_is_post();
		if ($ispost) {
			$post = $this->request->postx();
			$post['acid'] = rbase64_decode($post['acid']);
			$acid = $this->_decode_acid($post['acid']);
			$true = array(
				'acid' => $acid,
				'outname' => $post['outname'],
				'outphone' => $post['outphone']
			);
			//获取标题
			$title = null;
			$title_uda = new voa_uda_frontend_activity_sign();
			$title_uda->gettitle($true['acid'], $title);

			try {
				$uda = &uda::factory('voa_uda_frontend_activity_view');
				$istrue = null;
				$uda->true($true, $istrue);
				if (!$istrue) {
					$data['message'] = '报名信息错误';
					$this->view->set('navtitle', $title);
					$this->view->set('data', $data);
					$this->_output('mobile/' . $this->_plugin_identifier . '/error');
				}
				$getqcode = array(
					'acid' => urlencode(authcode($true['acid'], 'zhoutao', 'ENCODE', '0')),
					'outname' => urlencode(authcode($true['outname'], 'zhoutao', 'ENCODE', '0')),
					'outphone' => urlencode(authcode($true['outphone'], 'zhoutao', 'ENCODE', '0'))
				);
				$this->view->set('navtitle', $title);
				$this->view->set('allow', '1');
				$this->view->set('data', $getqcode);
				$this->_output('mobile/' . $this->_plugin_identifier . '/outqcode');
			} catch (help_exception $h) {
				$this->_error_message($h->getMessage());
				return false;
			} catch (Exception $e) {
				logger::error($e);
				$this->_error_message($e->getMessage());
				return false;
			}
		}


		$gets = $this->request->getx();
		if (!empty($gets['ac'])) {
			switch ($gets['ac']) {
				//输入报名信息获取二维码界面
				case 'recode' :
					//获取标题
					$recodegets = rbase64_decode($gets['acid']);
//					if (strlen($recodegets) > '40') {
//						$recodeacid = authcode($recodegets, 'zhoutao', 'DECODE', '0');
//					} else {
//						$recodeacid = $recodegets;
//					}
					$recodeacid = $this->_decode_acid($recodegets);
					$title = null;
					$title_uda = new voa_uda_frontend_activity_sign();
					$title_uda->gettitle($recodeacid, $title);
					$this->view->set('navtitle', $title);

					//这里需要传输加密后的acid
					$this->view->set('acid', $gets['acid']);
					$this->_output('mobile/' . $this->_plugin_identifier . '/outqcode');
					break;
				//获取二维码
				case 'takecode' :
					$this->getcode($gets);
					break;
				//验证二维码
				case 'check' :
					try {
						$m_uid = startup_env::get('wbs_uid');
						$uda = &uda::factory('voa_uda_frontend_activity_get');
						$codedata = array(
							'acid' => authcode($gets['acid'], 'zhoutao', 'DECODE', '0'),
							'outname' => authcode($gets['outname'], 'zhoutao', 'DECODE', '0'),
							'm_uid' => $m_uid,
							'outphone' => authcode($gets['outphone'], 'zhoutao', 'DECODE', '0')
						);
						//获取标题
						$title = null;
						$title_uda = new voa_uda_frontend_activity_sign();
						$title_uda->gettitle($codedata['acid'], $title);
						$this->view->set('navtitle', $title);

						$is = null;
						$remark = null;
						$uda->outjudgem($codedata, $is, $remark);
						if ($is === true) {
							$data = array(
								'name' => $codedata['outname'],
								'phone' => $codedata['outphone'],
								'outsider' => 1,
								'remark' => $remark
							);
							$this->view->set('user', $data);
							$this->view->set('is', $is);
							$this->_output('mobile/' . $this->_plugin_identifier . '/check');
						} else {
							$this->view->set('is', $is);
							$this->_output('mobile/' . $this->_plugin_identifier . '/check');
						}
					} catch (help_exception $h) {
						$this->_error_message($h->getMessage());
						return false;
					} catch (Exception $e) {
						logger::error($e);
						$this->_error_message($e->getMessage());
						return false;
					}
					break;
			}
		}
		return true;
	}

	//获取二维码
	private function getcode($qdata) {
		$uda_code = new voa_uda_frontend_activity_view();
		$uda_code->outqrcode($qdata, '', 0);
		return true;
	}

}
