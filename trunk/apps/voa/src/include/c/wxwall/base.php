<?php
/**
 * voa_c_wxwall_base
 * 微信墙前端:基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_base extends controller {

	protected $_module = '';
	protected $_action = '';
	protected $_is_ajax = false;

	protected function _before_action($action) {
		exit;
		/** 初始化页面标题 */
		$this->view->set('navTitle', '');
		$this->_module = $this->_action = '';
		@list($this->_module, $this->_action) = explode('_', $this->action_name);

		$this->_is_ajax = $this->request->is_xml_http_request();

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 输出模板
	 * @param unknown $tpl
	 */
	public function output($tpl) {

		$this->view->set('module', $this->_module);
		$this->view->set('action', $this->_action);

		/** 静态文件目录url */
		$this->view->set('staticUrl', APP_STATIC_URL);

		/** 当前时间戳 */
		$this->view->set('timestamp', startup_env::get('timestamp'));

		/** 输入当前实例 */
		$this->view->set('cinstance', $this);

		/** 输出 forumHash */
		$this->view->set('formhash', $this->_generate_form_hash());
		$this->view->render($tpl);

		return $this->response->stop();
	}

	/**
	 * 生成formhash
	 * @return string
	 */
	protected function _generate_form_hash() {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$fh_key = $this->request->server('HTTP_HOST').(isset($sets['formhash_key']) ? $sets['formhash_key'] : '');
		return voa_h_form_hash::generate($fh_key);
	}

	/**
	 * 构造微信前端 后台管理Url
	 * @param unknown $action
	 * @param string $module
	 * @param unknown $param
	 * @param boolean $htmlEncode
	 * @return string
	 */
	public function wxwall_admincp_url($module, $action = '', $param = array(), $htmlEncode = true) {
		$url = '/';
		if (defined('APP_DIRNAME') && APP_DIRNAME) {
			$url .= APP_DIRNAME.'/';
		}
		if ($module) {
			$url .= $module.'/';
			if ($action) {
				$url .= $action.'/';
			}
		}
		if ($param) {
			$urlParam = array();
			foreach ($param AS $k => $v) {
				$urlParam[] = $k.'='.$v;
			}
			if ($urlParam) {
				$url .= '?'.implode($htmlEncode ? '&amp;' : '&', $urlParam);
			}
		}
		return $url;
	}

	/**
	 * message
	 * 消息提示
	 * @param  string $type
	 * @param  mixed $message
	 * @param  mixed $title
	 * @param  mixed $extra
	 * @param  mixed $redirect
	 * @param  mixed $url
	 * @param  string $tpl
	 * @return void
	 */
	protected function _message($type = 'success', $message = null, $url = null,
			$redirect = false, $tpl = '', $title = null, $extra = null) {

		if ($type == 'success') {
			if (!$title) {
				$title = '成功';
			}
			if (!$message) {
				$message = '操作已成功';
			}
		} else {
			if (!$title) {
				$title = '失败';
			}
			if (!$message) {
				$message = '操作失败';
			}
		}
		if (!$url) {
			/** 检查来源链接，不合法的跳转到首页 */
			$referer = $this->request->server('HTTP_REFERER');
			if (!$referer || !preg_match('/^[htps]+\:\/\/(bbs\.|)life\.qq\.com/', $referer)) {
				$url = '/';
			} else {
				$url = $referer;
			}
		}
		$this->view->set('title', $title);
		$this->view->set('redirect', $redirect);
		$this->view->set('url', $url);
		$this->view->set('jsUrl', str_replace('&amp;', '&', $url) );
		$this->view->set('message', $message);
		$this->view->set('extra', $extra);
		$this->view->set('type', $type);
		return $this->output($tpl);
		return $this->output('wxwall/admincp/message');
	}

}
