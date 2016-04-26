<?php
/**
 * daily.php
 * 内部api方法/超级报表查看日报
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_daily extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** service 类 */
	private $__service = null;
	/** diy uda 类 */
	private $__diy = null;

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_dailyreport();
		}
		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_data_get();
		}
	}

	/**
	 * 取得日报
	 * @param array $request 请求的参数
	 * + sp_id 门店ID
	 * + date 日期
	 * @param array $result (引用结果)月报信息数组
	 * @return boolean
	 */
	public function get_daily(array $request, array &$result) {

		// 定义参数请求规则
		$fields = array(
			// 请求参数 门店ID
			'csp_id' => array(
				'csp_id', parent::VAR_INT,
				array($this->__service, 'validator_csp_id'),
				null, false,
			),
			// 日期
			'date' => array(
				'date', parent::VAR_STR,
				array($this->__service, 'validator_date'),
				null, false
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}


		// 取得查询参数
		$csp_id =  $this->__request['csp_id'];
		$date =  $this->__request['date'];
	//	$uid = $request['uid'];

		//取得日报详情
		$s_detail = new voa_d_oa_superreport_detail();
		$detail = $s_detail->get_by_conds(array('csp_id' => $csp_id, 'cdate' => $date));
		if (!$detail) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::DAILYREPORT_ERROR);
		}
		$result['csp_id'] = $detail['csp_id'];
		$uda_shop = &uda::factory('voa_uda_frontend_common_place_get');

		//取得门店
		$shop = array();
		$uda_shop->doit(array('placeid' => $detail['csp_id']), $shop);
		$result['csp_name'] = $shop['place']['name'];                                                 //门店名称

		$result['dr_id'] = $detail['dr_id'];
		$result['uid'] = $detail['m_uid'];
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $servm->fetch_by_uid($detail['m_uid']);
		$result['username'] = $user['m_username'];
		$result['reporttime'] = $detail['cdate'];
		$result['created_u'] = rgmdate($detail['created']);

		//判断日报是否是当日发送的可修改日报
		$current = rgmdate(time(),'Y-m-d');
		if ($current == $detail['cdate']) {
			$result['editable'] = true;
		} else {
			$result['editable'] = false;
		}

		//取得上一天日期
		$forward_date = date(
				'Y-m-d',
				mktime(0,0,0,date('m',strtotime($date)),date('d',strtotime($date))-1,date('Y',strtotime($date))));
		$detail_foward = $s_detail->get_by_conds(array('csp_id' => $csp_id, 'cdate' => $forward_date));

		// 取得当日日报数据和上一天数据
		$this->_init_diy_data($this->__diy);
		$daily = array();
		$daily_forward = array();
		$this->__diy->execute(array('dr_id' => $detail['dr_id']), $daily);
		if ($detail_foward) {
			$this->__diy->execute(array('dr_id' => $detail_foward['dr_id']), $daily_forward);
		}
		if (!$daily) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::MONTHLYREPORT_ERROR);
		}

		// 取得模板数据
		$templates = array();
		$diy = new voa_uda_frontend_diy_column_list();
		$this->_init_diy_data($diy);  //设置选项
		$diy->execute(array(),$templates);

		$result['report'] = $this->__service->format_daily($templates, $daily, $daily_forward);

		//取得评论
		$s_comment = new voa_s_oa_superreport_comment();
		$comments = array();
		$comments = $s_comment->list_comments_by_dr_id($detail['dr_id'], null);
		if ($comments) {
			$uids = array_column($comments, 'm_uid');
			/** 评论用户头像信息 */
			$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $servm->fetch_all_by_ids($uids);
			voa_h_user::push($users);
			foreach ($comments as &$comment) {
				$comment['username'] = $users[$comment['m_uid']]['m_username'];
				$comment['avatar'] = voa_h_user::avatar($comment['m_uid'], $users[$comment['m_uid']]);
			}
		}
		$result['comments'] = $s_comment->format_comments_list($comments);
		$result['comments_total'] = $s_comment->count_comments_by_dr_id($detail['dr_id']);

		return true;
	}

}
