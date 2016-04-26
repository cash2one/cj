<?php
/**
 * voa_server_weixin_msg
 * 微信普通消息接口服务基类
 *
 * $Author$
 * $Id$
 */


class voa_server_weixin_msg {
	/** 微信 service 实例 */
	protected $_wxserv;
	/** 配置信息 */
	protected $_setting = array();

    /**
     * __construct
     * 构造函数
     *
     * @return void
     */
    public function __construct() {
    	$this->_wxserv = voa_weixin_service::instance();
    	$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
    }

    /** 文本消息 */
    public function text($args) {
		/** 返回给用户的响应消息内容 */
		$response_text = '';

		//XXX 此处逻辑未来可能需要调整
		$serverMsg = $this->_wxserv->msg['content'];

		if (0 === strpos($serverMsg, '#')) {
			/** 请求需要响应的消息 */
			$wxscreen = new voa_weixin_wxscreen();
			if (0 === stripos($serverMsg, voa_h_wxcmd::WXWALL_POST_CODE)) {
				/** 发送上墙编码的内容 */
				$wxscreen->wxwall_online_clear();
				$response_text = $wxscreen->wxwall_post($this->_wxserv);
			} elseif (0 === stripos($serverMsg, voa_h_wxcmd::WXWALL_QUIT_CODE)) {
				/** 下墙命令 */
				$wxscreen->wxwall_online_clear();
				$response_text = $wxscreen->wxwall_quit($this->_wxserv);
			} else {
				/** 其他命令 */
			}
		} else {
			/** 其他操作 */
			$wxscreen = new voa_weixin_wxscreen();
			if (($response_text = $wxscreen->wxwall_post($this->_wxserv))) {
				/** 尝试已上墙继续发布微信墙消息 */
				$wxscreen->wxwall_online_clear();
			}
		}

		if ($response_text == '') {
			//TODO 无任何具体实际命令而显示的调试代码
			$scheme = config::get('voa.oa_http_scheme');
			$reg_url = $this->_wxserv->oauth_url_base($scheme.$this->_setting['domain'].'/register');
			$response_text = $this->_wxserv->msg['content']."<br /><a href='".$reg_url."'>register</a>";
		}

		/** 返回信息 */
		if ($response_text) {
			$this->_wxserv->response_text($response_text);
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

    	$wxscreen = new voa_weixin_wxscreen();
		if (($ww_id = $wxscreen->get_wwid($this->_wxserv))) {
			/** 微信墙图片 */
			$response_text = $wxscreen->wxwall_post($this->_wxserv);
			/** 清理过期的微信墙 */
			$wxscreen->wxwall_online_clear();
			/** 回复消息给用户 */
			$this->_wxserv->response_text($response_text);
		}

		return true;
    }
}
