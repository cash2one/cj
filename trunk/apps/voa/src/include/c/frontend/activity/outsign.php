<?php

/**
 * voa_c_frontend_activity_outsign
 * 外部人员报名
 * Created by zhoutao.
 * Created Time: 2015/5/20  18:20
 */
class voa_c_frontend_activity_outsign extends voa_c_frontend_activity_base {

	//不强制登录，允许外部人员访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute() {

		//获取外部人员报名信息
		$is_post = $this->_is_post();
		if ($is_post) {
			try {
				$post = $this->request->postx();
				$postuda = &uda::factory('voa_uda_frontend_activity_sign');
				$out_post_deal = null;
				$title = null;
				$other_feild = null;
				$postuda->out_post_deal($post, $out_post_deal, $title, $other_feild);
				switch ($out_post_deal) {
					case 1:
						$data['message'] = '该手机号已经报过名';
						$this->view->set('data', $data);
						$this->_output('mobile/' . $this->_plugin_identifier . '/error');
						break;
					case 2:
						$data['message'] = '报名人数已满';
						$this->view->set('data', $data);
						$this->_output('mobile/' . $this->_plugin_identifier . '/error');
						break;
					default:
						//标记当前外部用户已报名
						$this->session->setx('activity_' . $post['acid'], '1', time() + 86400 * 90);
						// 成功报完名，生成二维码对应的需要数据
						$getqcode = array(
							'acid' => urlencode(authcode($post['acid'], 'zhoutao', 'ENCODE', '0')),
							'outname' => urlencode(authcode($post['outname'], 'zhoutao', 'ENCODE', '0')),
							'outphone' => urlencode(authcode($post['outphone'], 'zhoutao', 'ENCODE', '0'))
						);
						// 转义自定义字段
						foreach ($other_feild as $k => &$v) {
							$v = htmlspecialchars($v);
						}
						// 页面数据
						$data['message'] = '报名成功';
						$post['outname'] = htmlspecialchars($post['outname']);
						$post['remark'] = htmlspecialchars($post['remark']);
						$this->view->set('viewurl', '/frontend/activity/view/?pluginid=' . startup_env::get('pluginid') . '&acid=' . $post['acid']);
						$this->view->set('data', $post);
						$this->view->set('other_feild', $other_feild);
						$this->view->set('getqcode', $getqcode);
						$this->view->set('navtitle', $title);
						$this->_output('mobile/' . $this->_plugin_identifier . '/success');
						break;
				}
			} catch (help_exception $h) {
				$this->_error_message($h->getMessage());
				return false;
			} catch (Exception $e) {
				logger::error($e);
				$this->_error_message($e->getMessage());
				return false;
			}
		}

		//get获取当前操作参数
		$acid = $this->request->get('acid');
		$acid = rbase64_decode($acid);
		if (strlen($acid) >= 20) {
			$acid = authcode($acid, 'zhoutao', 'DECODE', '0');
		}
		if ($acid == '') {
			$data['message'] = '未知活动ID';
			$this->view->set('data', $data);
			$this->_output('mobile/' . $this->_plugin_identifier . '/error');
		}
		$ac = $this->request->get('ac');
		if ($ac) {
			switch ($ac) {
				//外部报名
				case 'out-sign':
					try {
						$data = null;
						$outacid['acid'] = $acid;
						$uda = &uda::factory('voa_uda_frontend_activity_get');
						if (!$uda->getact($outacid, $data)) {
							$data['message'] = '没有这条数据';
							$this->view->set('data', $data);
							$this->_output('mobile/' . $this->_plugin_identifier . '/error');
						}
					} catch (help_exception $h) {
						$this->_error_message($h->getMessage());
						return false;
					} catch (Exception $e) {
						logger::error($e);
						$this->_error_message($e->getMessage());
						return false;
					}
					$outfield = unserialize($data['outfield']);
					$outfield = array_slice($outfield, 3);
					$this->view->set('outfield', $outfield);
					$this->view->set('navtitle', $data['title']);
					$this->view->set('data', $data);
					$this->view->set('acid', $acid);
					$this->_output('mobile/' . $this->_plugin_identifier . '/outsign');
					break;

				default:
					$data['message'] = '传入参数错误';
					$this->view->set('data', $data);
					$this->_output('mobile/' . $this->_plugin_identifier . '/error');
			}

		} else {
			return false;
		}


		return true;
	}

}
