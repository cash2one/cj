<?php
/**
 * abstract.php
 * 统一数据访问/派单/集类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_abstract extends voa_uda_frontend_base {

	/** 应用的唯一标识名 */
	public $plugin_identifier = '';
	/** 工单执行状态文字描述 */
	public $wostate = array();
	/** 接单执行状态文字描述 */
	public $worstate = array();
	/** 应用设置信息 */
	public $plugin_setting = array();
	/** 站点全局设置 */
	public $setting = array();
	/** 可执行的动作映射名 */
	public $actions = array();
	/** 附件上传者的角色映射名 */
	public $attachment_roles = array();

	/** 当前时间戳 */
	protected $_timestamp = 0;

	public function __construct() {

		parent::__construct();

		$this->_timestamp = startup_env::get('timestamp');

		// 如果未指定当前应用的唯一标识名，则自当前类提取判断
		if (!$this->plugin_identifier) {
			list(,,,$this->plugin_identifier) = explode('_', rstrtolower(__CLASS__));
		}

		// 当前应用的设置信息
		$this->plugin_setting = voa_h_cache::get_instance()->get('plugin.'.$this->plugin_identifier.'.setting', 'oa');

		// 站点全局配置
		$this->setting = voa_h_cache::get_instance()->get('setting', 'oa');

		// 工单执行状态
		$this->wostate = array(
			voa_d_oa_workorder::WOSTATE_WAIT => '待确认',
			voa_d_oa_workorder::WOSTATE_REFUSE => '已拒绝',
			voa_d_oa_workorder::WOSTATE_CONFIRM => '已接受',
			voa_d_oa_workorder::WOSTATE_COMPLETE => '已完成',
			voa_d_oa_workorder::WOSTATE_CANCEL => '已撤单',
		);

		// 接单人执行状态
		$this->worstate = array(
			voa_d_oa_workorder_receiver::WORSTATE_WAIT => '待执行',
			voa_d_oa_workorder_receiver::WORSTATE_REFUSE => '已拒绝',
			voa_d_oa_workorder_receiver::WORSTATE_CONFIRM => '已确认',
			voa_d_oa_workorder_receiver::WORSTATE_MYCOMPLETE => '已完成',
			voa_d_oa_workorder_receiver::WORSTATE_COMPLETE => '别人已完成',
			voa_d_oa_workorder_receiver::WORSTATE_ROBBED => '被抢单',
			voa_d_oa_workorder_receiver::WORSTATE_MYCANCEL => '接单人已撤单',
			voa_d_oa_workorder_receiver::WORSTATE_CANCEL => '派单人已撤单',
		);

		// 可执行的动作映射名
		$this->actions = array(
			voa_d_oa_workorder::ACTION_CANCEL => '撤回',
			voa_d_oa_workorder::ACTION_COMPLETE => '完成',
			voa_d_oa_workorder::ACTION_CONFIRM => '接受',
			voa_d_oa_workorder::ACTION_MYCANCEL => '退回',
			voa_d_oa_workorder::ACTION_REFUSE => '拒绝',
			voa_d_oa_workorder::ACTION_SEND => '派单',
			voa_d_oa_workorder::ACTION_UNKNOWN => '未知动作',
		);

		// 附件上传者的角色名
		$this->attachment_roles = array(
			voa_d_oa_workorder_attachment::ROLE_SENDER => '派单人',
			voa_d_oa_workorder_attachment::ROLE_RECEIVER => '接收人',
			voa_d_oa_workorder_attachment::ROLE_OPERATOR => '执行人',
		);
	}

	/**
	 * 获取工单详情页的微信企业号授权链接
	 * @param string $url (引用结果)链接字符串
	 * @param number $woid 工单ID
	 * @return boolean
	 */
	public function get_view_url(&$url, $woid) {

		// 站点使用的传输协议，自全局配置读取
		$url = config::get(startup_env::get('app_name').'.oa_http_scheme');
		// 站点域名
		$url .= $this->setting['domain'].'/frontend/';
		// 应用唯一标识符
		$url .= $this->plugin_identifier.'/index?';
		// 应用ID
		$url .= 'pluginid='.$this->plugin_setting['pluginid'];
		// 执行的动作
		$url .= '&__view=detail&__params[id]='.$woid;

		// 生成链接
		$url = voa_wxqy_service::instance()->oauth_url($url);

		return true;
	}

	/**
	 * 获取工单执行状态名称（workorder表wostate）
	 * @param string $wostate (引用结果)工单执行状态名
	 * @param number $val 工单执行状态储存名
	 * @return void
	 */
	public function get_wostate_name(&$wostate, $val) {
		$wostate = isset($this->wostate[$val]) ? $this->wostate[$val] : '-';
	}

	/**
	 * 获取接单人执行状态名称（workorder_receiver表worstate）
	 * @param string $worstate (引用结果)接收人执行状态名
	 * @param number $val 接收人执行状态储存名
	 * @return void
	 */
	public function get_worstate_name(&$worstate, $val) {
		$worstate = isset($this->worstate[$val]) ? $this->worstate[$val] : '-';
	}

	/**
	 * 获取执行动作的名称（workorder_log表action）
	 * @param string $action (引用结果)动作可视名
	 * @param string $val 动作储存名
	 * @return void
	 */
	public function get_action_name(&$action, $val) {
		$action = isset($this->actions[$val]) ? $this->actions[$val] : '-';
	}

}
