<?php
/**
 * voa_uda_frontend_express_abstract
 * 统一数据访问/快递助手/基类
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_abstract extends voa_uda_frontend_base {

	// 配置信息
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.express.setting', 'oa');
		// 初始化 service
		if (null == $this->_serv) {
			$this->_serv = new voa_s_oa_express();
		}
		/** 取应用插件信息 */
		$pluginid = $this->_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->errcode = 1001;
			$this->errmsg = '应用信息丢失，请重新开启';
			return false;
		}
		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];
		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->errcode = 1002;
			$this->errmsg = '本应用尚未开启 或 已关闭，请联系管理员启用后使用';
			return false;
		}
	}

	/**
	 * 构造话题查看页面前端url
	 * @param unknown $url
	 * @param unknown $dr_id
	 * @return boolean
	 */
	public function viewurl(&$url, $eid) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$pluginid = $this->_sets['pluginid'];
		$http = config::get(startup_env::get('app_name').'.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($http.$sets['domain'].'/frontend/express/view/eid/'.$eid.'?pluginid='.$pluginid);
		return true;
	}


	/**
	 * 发送快递微信消息
	 * @author Deepseath@20141222#310
	 * @param array $mq_ids (引用结果)当前消息队列ID
	 * @param array $thread 话题详情数据
	 * @param string $type 消息类型: new=新话题,reply=评论,likes=点赞
	 * @param number $senderid 消息发送者的uid
	 * @return true;
	 */
	public function send_msg($express, $type, $session_obj) {
		// 构造日报查看链接/
		$viewurl = '';
		$this->viewurl($viewurl, $express['eid']);

		// 确定消息正文内容
		$content = array();
		if ($type == 'new') {
			$msg_title = "快递抵达前台,请前往领取!";
			$msg_desc = "来自: ".startup_env::get('wbs_username');
			$msg_url = $viewurl;

		} elseif ($type == 'lead') {
			$msg_title = "代领快递请求！";
			$msg_desc = "来自: ".startup_env::get('wbs_username');
			$msg_url = $viewurl;

		} elseif($type == 'lead_ok'){
			$msg_title = "指定快递已领取!";
			$msg_desc = "来自: ".startup_env::get('wbs_username');
			$msg_url = $viewurl;
		}

		if(empty($express['uid'])){
			return true;
		}
		// 发送消息
		voa_h_qymsg::push_news_send_queue($session_obj, $msg_title, $msg_desc, $msg_url, array($express['uid']));

		return true;
	}


	/**
	 * 生成二维码
	 *
	 * @param int $id	快递id
	 * @param string $file	若有文件名则生成文件,否则直接输出
	 * @param boolean $is_download	是否下载文件
	 */
	public function qrcode($id, $file='', $is_download = false)
	{
		$sets = voa_h_cache::get_instance()->get('setting','oa');

		//生成二维码
		include_once(ROOT_PATH.'/framework/lib/phpqrcode.php');
		//跳转地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.$sets['domain']."/frontend/express/scan?eid=".$id;

		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);


		if($file) {
			//生成文件
			imagepng($qrcode, $file);
		}else{
			//直接输出图片
			header('Content-Type: image/png');
			imagepng($qrcode);
		}
	}

}
