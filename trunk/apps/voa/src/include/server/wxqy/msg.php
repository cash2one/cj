<?php
/**
 * voa_server_wxqy_msg
 * 微信企业普通消息处理
 *
 * $Author$
 * $Id$
 */


class voa_server_wxqy_msg {
	/** 微信 service 实例 */
	protected $_wxserv;
	/** 取应用信息 */
	protected $_plugin = array();

    /**
     * __construct
     * 构造函数
     *
     * @return void
     */
    public function __construct() {

    	$this->_wxserv = voa_wxqy_service::instance();
		/** 取应用信息 */
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists(startup_env::get('pluginid'), $plugins)) {
			$this->_plugin = $plugins[startup_env::get('pluginid')];
		}

		// 如果是未开启的应用
		if (voa_d_oa_common_plugin::AVAILABLE_OPEN != $this->_plugin['cp_available']) {
			$this->_plugin = array();
		}
    }

    /** 文本消息 */
    public function text($args) {
		/** 返回给用户的响应消息内容 */
		$response_text = '';

		//XXX 此处逻辑未来可能需要调整
		$serverMsg = $this->_wxserv->msg['content'];

// 		if (0 === strpos($serverMsg, '#')) {
// 			/** 请求需要响应的消息 */
// 			$wxscreen = new wxscreen;
// 			if (0 === stripos($serverMsg, voa_h_wxcmd::WXWALL_POST_CODE)) {
// 				/** 发送上墙编码的内容 */
// 				$wxscreen->wxwall_online_clear();
// 				$response_text = $wxscreen->wxwall_post($this->_wxserv);
// 			} elseif (0 === stripos($serverMsg, voa_h_wxcmd::WXWALL_QUIT_CODE)) {
// 				/** 下墙命令 */
// 				$wxscreen->wxwall_online_clear();
// 				$response_text = $wxscreen->wxwall_quit($this->_wxserv);
// 			} else {
// 				/** 其他命令 */
// 			}
// 		} else {
// 			/** 其他操作 */
// 			$wxscreen = new wxscreen;
// 			if (($response_text = $wxscreen->wxwall_post($this->_wxserv))) {
// 				/** 尝试已上墙继续发布微信墙消息 */
// 				$wxscreen->wxwall_online_clear();
// 			}
// 		}

		/** 判断应用是否存在 */
		if ($this->_plugin) {
			$class = 'voa_c_frontend_'.$this->_plugin['cp_identifier'].'_base';
			$this->_wxserv->response_text(call_user_func(array($class, 'show_menu'), $this->_wxserv->msg, $this->_plugin), true);
			return true;
		}

		/** 返回信息 */
		if ($response_text) {
			$this->_wxserv->response_text($response_text, true);
		}

		return true;
    }

    /** 语言消息 */
    public function voice($args) {
    	return true;
    }

    /** 位置信息 */
    public function location($args) {
    	return true;
    }

    /** 链接信息 */
    public function link($args) {
    	return true;
    }

    /** 图片信息 */
    public function image($args) {
	    //XXX 可能需要改进
// 		$wxscreen = new wxscreen;
// 		if (($ww_id = $wxscreen->get_wwid($this->_wxserv))) {
// 			/** 微信墙图片 */
// 			$response_text = $wxscreen->wxwall_post($this->_wxserv);
// 			/** 清理过期的微信墙 */
// 			$wxscreen->wxwall_online_clear();
// 			/** 回复消息给用户 */
// 			$this->_wxserv->response_text($response_text, true);
// 		}

    	$data = array();
		/** 从微信服务器取图片 */
    	if (!$this->_wxserv->get_media($data, $this->_wxserv->msg['media_id'])) {
    		return false;
    	}

    	/** 读取用户 */
    	$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
    	$user = $serv_m->fetch_by_openid($this->_wxserv->msg['from_user_name']);
    	startup_env::set('wbs_uid', $user['m_uid']);
    	startup_env::set('wbs_username', $user['m_username']);

    	/** 附件信息 */
		$file_field = 'base64Data';
		$type = 'base64';
		$_POST['base64Data'] = $data['file_data'];
		if ($data['file_name']) {
			$_POST['fileName'] = $data['file_name'];
		} else {
			$_POST['fileName'] = random(16);
			$file_mimes = config::get(startup_env::get('app_name').'.attachment.file_mime');
			if ($data['content-type'] && array_key_exists($data['content-type'], $file_mimes)) {
				$_POST['fileName'] .= '.'.$file_mimes[$data['content-type']];
			}
		}

		$uda = &uda::factory('voa_uda_frontend_attachment_insert');
		$attachment = array();
		if (!$uda->upload($attachment, $file_field, $type)) {
			return false;
		}

		/** 记录附件到具体应用信息 */
		if ($this->_plugin) {
			$class = 'voa_c_frontend_'.$this->_plugin['cp_identifier'].'_base';
			$this->_wxserv->response_text(call_user_func(array($class, 'wx_attach'), $attachment, $this->_wxserv->msg, $this->_plugin), true);
			return true;
		}

		return true;
    }
}
