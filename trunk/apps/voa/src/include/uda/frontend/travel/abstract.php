<?php
/**
 * voa_uda_frontend_travel_abstract
 * 统一数据访问/商品应用/基类
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_abstract extends voa_uda_frontend_base {
	// 商品配置
	protected $_sets = array();
	// 插件名称
	protected $_ptname = array();
	// 分类id
	protected $_classes = array();
	// 表格列表
	protected $_tables = array();
	// 表格列属性列表
	protected $_tablecols = array();
	// 表格列选项
	protected $_tablecolopts = array();
	// 用户信息数组
	protected $_mem = array();

	public function __construct($ptname = null) {

		parent::__construct($ptname);
		// 取应用配置
		$this->_sets = voa_h_cache::get_instance()->get('plugin.travel.setting', 'oa');
		$this->_ptname = $ptname;

	}

	/**
	 * 构造话题查看页面前端url
	 * @param unknown $url
	 * @param unknown $dr_id
	 * @return boolean
	 */
	public function viewurl(&$url, $orderid) {

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$pluginid = $this->_sets['pluginid'];
		$http = config::get(startup_env::get('app_name').'.oa_http_scheme');
		$url = $http.$sets['domain'].'/frontend/travel/orderdetail/orderid/'.$orderid.'?pluginid='.$pluginid;
		return true;
	}


	/**
	 * 服务号发送消息
	 * @author Deepseath@20141222#310
	 * @param array $mq_ids (引用结果)当前消息队列ID
	 * @param array $thread 话题详情数据
	 * @param string $type 消息类型: import=发货通知
	 * @param number $senderid 消息发送者的uid
	 * @return true;
	 */
	public function send_msg($order, $type,$senderid,$session_obj) {

		$p_sets = voa_h_cache::get_instance ()->get ('setting', 'oa' );
		// 构造日报查看链接/
		$viewurl = '';
		$this->viewurl($viewurl, $order['orderid']);

		$weixin = &service::factory('voa_weixin_service');
		$tplid = config::get('voa.wepay.fh_tplid');
		$data = array(
				'first' => '您在'.$p_sets['appname'].'购买的货物已发货',
				'keyword1' => $order['ordersn'],
				'keyword2' => $order['express'],
				'keyword3' => $order['expressn'],
				'remark' => ''
		);
		$weixin->send_tpl_msg($order['customer_openid'], $tplid, $data, $viewurl);
        return true;
	}


}
