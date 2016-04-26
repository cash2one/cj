<?php

/**
 * voa_uda_frontend_event_add
 * 统一数据访问/社群活动/添加活动
 * Created by gaosong.
 * Created Time: 2015/11/12
 */
class voa_uda_frontend_event_add extends voa_uda_frontend_event_base {

	private $_invite;

	public function __construct() {
		parent::__construct();
		$this->__service = new voa_s_oa_event();
		$this->__service_banner = new voa_s_oa_banner();
		$this->__invite = new voa_s_oa_event_invite();
	}

	private $__request = array();

	/**
	 * 入库操作
	 * @param $in
	 * @param $out
	 * @param object $session
	 * @return bool
	 */
	public function addact($in, &$out, $session) {

		$data = array();
		// 提交的值进行过滤
		if (!$this->getact($in, $data)) {
			return false;
		}

		// 报名自定义字段设置
		//$data['custom'] = empty($in['custom']) ? '' : serialize( $in['custom'] );
		if (isset($in['is_voucher'])) {
			$data['is_voucher'] = $in['is_voucher'];//是否报名
			$data['mode'] = $in['mode'];//报名方式
		}
		if(isset($in['filed']) && !empty($in['filed'])){
			$this->_filed_can_add($in['filed'], $filed);
			$data['field_partake'] = serialize($filed);
		}

		if (isset($in['outsider'])) {
			$data['outsider'] = $in['outsider'];//权限设置
		}

		if (isset($in['allow_qy'])) {
			$data['allow_qy'] = $in['allow_qy'];//权限设置
		}

		$data['source'] = 1; //操作来源 1、后台  2、前端
		$data['source_name'] = $in['source_name'];

		if (empty($data['uname'])) {
			$p_sets = voa_h_cache::get_instance()->get('setting', 'oa');
			$data['uname'] = $p_sets['sitename'];
		}

		//签到权限处理
		$data['sign_uids'] = isset($in['sign_uids'])? serialize($in['sign_uids']):'';
		$data['sign_dids'] = isset($in['sign_ids']) ? serialize($in['sign_ids']):'';

		// 社群活动入库
		$data = $this->__service->insert($data);

		//加入活动
		if (!empty($data['acid']) && !empty($data['m_uid'])) {
			$dynamic_data = array(
				'm_uid'          => $data['m_uid'],
				'm_username'          => $data['uname'],
				'obj_id'        => $data['acid'],
				'cp_identifier' => 'event',
				'dynamic'       => 11
			);
			$dynamic_s = &service::factory('voa_s_oa_common_dynamic');
			$dynamic_s->insert($dynamic_data);
		}
		// 判断显示首页添加入库
		if (!empty($data['acid'])) {
			if (!empty($in['index_show'])) {
				$banner_data = array(
					'handpicktype' => 1,
					'title' => $data['title'],
					'lid' => $data['acid'],
					'attid' => $data['at_ids'],
					'b_created' => $data['created'],
					'b_order' => 1
				);
				$this->__service_banner->update_order_all();
				$this->__service_banner->insert($banner_data);
			}
		}
		//全部人员
		if (isset($in['is_all']) && $in['is_all'] == 1) {
			$dp[] = -1;
			$users[] = -1;
			$right = array();
		} else {
			//指定人员
			if (!empty($in['add_uids'])) {
				$users = $in['add_uids'];
			} else {
				$users = '';
			}
			if (!empty($in['add_ids'])) {
				$dp = $in['add_ids'];
			} else {
				$dp = '';
			}
			$right = true;
		}
		if (!empty($data['acid']) && $in['is_push'] == 1) {
			//插入人员
			if($right){
				$idata = $idata2 = array();
				if($users) {
					foreach($users as $val) {
						$idata[] = array(
							'primary_id' => $val,
							'type' => 2,
							'acid' => $data['acid']
						);
					}
				}
				if($dp) {
					foreach($dp as $val) {
						$idata2[] = array(
							'primary_id' => $val,
							'type' => 1,
							'acid' => $data['acid']
						);
					}
				}

				$mydata = array_merge($idata, $idata2);
				$this->__invite->insert_multi($mydata);
			}
			startup_env::set('pluginid', $this->_sets['pluginid']);
			startup_env::set('agentid', $this->_plugins[$this->_sets['pluginid']]['cp_agentid']);

			$settings = voa_h_cache::get_instance()->get('setting', 'oa');
			// 发送微信消息
			$address = '';
			if ($data['province'] == $data['city']) {
				$address = $data['city'].$data['area'].$data['street'];
			} else {
				$address = $data['province'].$data['city'].$data['area'].$data['street'];
			}
			$msg_title = "[活动]" . $data['title'];
			$scheme = config::get('voa.oa_http_scheme');
			$msg_desc = "活动时间：" . rgmdate($data['start_time'], "m-d H:i") . " 到 " . rgmdate($data['end_time'], "m-d H:i") . "\n";
			$msg_desc .= "活动地点：" . $address . "\n";
			$msg_picurl = voa_h_attach::attachment_url($data['at_ids'], 0);
			$msg_url = $scheme . $settings['domain'] . '/previewh5/micro-community/index.html#/app/page/activity/activity-detail?id=' . $data['acid'];

			// 发消息
			voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $users, $dp, $msg_picurl, 0, 0, -1);
		}

		return true;
	}


	/**
	 * 格式化签到人员
	 * @param        $in
	 * @param string $did
	 * @param string $uid
	 */
	protected function _sign_data($in, $did = '', $uid='') {
		$uid = serialize($in['sign_uids']);
		$did = serialize($in['sign_ids']);
	}

	/**
	 * 处理提交的数据
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function getact($in, &$out) {

		//获取数据
		if (!empty($in)) {
			$data['title'] = $in['title'];
			if (!isset($in['content']) || $in['content'] == '') {
				$data['content'] = '';
			} else {
				$data['content'] = $in['content'];
			}
			$data['province'] = $in['province'];
			$data['city'] = $in['city'];
			$data['area'] = $in['area'];
			$data['street'] = $in['street'];
			$data['np'] = $in['np'];
			if(isset($in['m_uid'])){
				$data['m_uid'] = $in['m_uid'];
				$user = voa_h_user::get($in['m_uid']);
				$data['uname'] = $user['m_username'];
			}

			$data['at_ids'] = $in['cover_id'];
			//消息推送
			$data['is_all'] = $in['is_all'];
			if (isset($in['is_push'])) {
				$data['is_push'] = $in['is_push'];
			} else {
				$data['is_push'] = 0;
			}

			//签到权限
			$data['sign_all'] = $in['sign_all'];
			if($data['sign_all'] == 0 ) {
				$data['sign_uids'] = isset($in['sign_uids']) ? $in['sign_uids']:'';
				$data['sign_ids'] = isset($in['sign_ids']) ? $in['sign_ids']:'';
			}


			// 处理时间
			$std = $in['start_time']['data'];
			$stt = $in['start_time']['time'];
			$etd = $std;
			$ett = $in['end_time']['time'];
			$cod = $in['cut_off_time']['data'];
			$cot = $in['cut_off_time']['time'];
			$in['start_time'] = $std . ' ' . $stt;
			$in['end_time'] = $etd . ' ' . $ett;
			$in['cut_off_time'] = $cod . ' ' . $cot;
			$data['start_time'] = rstrtotime($in['start_time']);
			$data['end_time'] = rstrtotime($in['end_time']);
			$data['cut_off_time'] = rstrtotime($in['cut_off_time']);

		} else {
			return false;
		}


		$fields = array(
			'title' => array('title', parent::VAR_STR, null, null, false),
			'content' => array('content', parent::VAR_STR, null, null, false),
			'province' => array('province', parent::VAR_STR, null, null, false),
			'city' => array('city', parent::VAR_STR, null, null, false),
			'area' => array('area', parent::VAR_STR, null, null, false),
			'street' => array('street', parent::VAR_STR, null, null, false),
			'np' => array('np', parent::VAR_INT, null, null, false),
			'm_uid' => array('m_uid', parent::VAR_INT, null, null, false),
			'is_all' => array('is_all', parent::VAR_INT, null, null, false),
			'sign_all' => array('sign_all', parent::VAR_INT, null, null, false),
			'is_push' => array('is_push', parent::VAR_INT, null, null, false),
			'at_ids' => array('at_ids', parent::VAR_STR, null, null, false),
			'uname' => array('uname', parent::VAR_STR, null, null, false),
			'start_time' => array('start_time', parent::VAR_INT, null, null, false),
			'end_time' => array('end_time', parent::VAR_INT, null, null, false),
			'cut_off_time' => array('cut_off_time', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}

		$out = $this->__request;


		if (isset($in['is_voucher'])) {
			$out['is_voucher'] = 1;
		}


		if ($out['start_time'] >= $out['end_time']) {
			$this->errmsg(10001, '开始时间不能大于结束时间');
			return false;
		}
		if ($out['cut_off_time'] < startup_env::get('timestamp')) {
			$this->errmsg(10002, '报名截止时间不能早于当前时间');
			return false;
		}
		if ($out['cut_off_time'] > $out['end_time']) {
			$this->errmsg(10003, '报名截止时间不能大于活动结束时间');
			return false;
		}

		if (!validator::is_string_count_in_range($out['title'], 1, 25)) {
			$this->errmsg(10004, '标题字数最高15字，最低1个字');
			return false;
		}
		if (!empty($out['np']) && $out['np'] <= 0) {
			$this->errmsg(10006, '限制人数不能为负和零');
			return false;
		}
		if (empty($out['content'])) {
			$this->errmsg(10007, '内容不能为空');
			return false;
		}
		return true;
	}

	/**
	 *
	 * @param array $in
	 * @param array $out
	 */
	protected function field_user($in=array(), &$out=array()){

	}


	/**
	 * 后台活动编辑更新
	 * @param $in
	 * @param $out
	 * @param object session
	 * @return bool
	 */
	public function updataact($in, &$out, $session) {

		var_dump($in);die;
		// 处理时间
		$std = $in['start_time']['data'];
		$stt = $in['start_time']['time'];
		$etd = $std;
		$ett = $in['end_time']['time'];
		$cod = $in['cut_off_time']['data'];
		$cot = $in['cut_off_time']['time'];
		$in['start_time'] = $std . ' ' . $stt;
		$in['end_time'] = $etd . ' ' . $ett;
		$in['cut_off_time'] = $cod . ' ' . $cot;
		$in['start_time'] = rstrtotime($in['start_time']);
		$in['end_time'] = rstrtotime($in['end_time']);
		$in['cut_off_time'] = rstrtotime($in['cut_off_time']);

		$fields = array(
			'title' => array('title', parent::VAR_STR, null, null, false),
			'content' => array('content', parent::VAR_STR, null, null, false),
			'address' => array('address', parent::VAR_STR, null, null, false),
			'start_time' => array('start_time', parent::VAR_INT, null, null, false),
			'end_time' => array('end_time', parent::VAR_INT, null, null, false),
			'cut_off_time' => array('cut_off_time', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}
		// 判断字段规则
		if ($in['start_time'] >= $in['end_time']) {
			$this->errmsg(10001, '开始时间不能大于结束时间');
			return false;
		}
		if ($in['cut_off_time'] > $in['start_time']) {
			$this->errmsg(10002, '报名截止时间不能早于开始时间');
			return false;
		}
		if ($in['cut_off_time'] > $in['end_time']) {
			$this->errmsg(10003, '报名截止时间不能大于活动结束时间');
			return false;
		}

		if (!validator::is_string_count_in_range($in['title'], 1, 25)) {
			$this->errmsg('10004', '标题字数最高15字，最低1个字');
			return false;
		}

		$data = array(
			'title' => $in['title'],
			'content' => $in['content'],
			'start_time' => $in['start_time'],
			'end_time' => $in['end_time'],
			'cut_off_time' => $in['cut_off_time'],
			'np' => $in['np'],
			'is_voucher' => isset($in['is_voucher']) ? $in['is_voucher']: 2,
			'at_ids' => $in['cover_id'],
			'province' => $in['province'],
			'city' => $in['city'],
			'area' => $in['area'],
			'street' => $in['street'],
			'sign_uids' => isset($in['sign_uids']) ? serialize($in['sign_uids']) : '',
			'sign_dids' => isset($in['sign_ids']) ? serialize($in['sign_ids']) : '',
			'sign_all' => 0
		);
		try {
			$this->__service->begin();
			$out = $this->__service->update_by_conds($in['acid'], $data);

			$news_right = array();
			//如果is_all=1则是全公司
			if ($in['is_push'] == 1 && $in['is_all'] == 0) {
				/**添加权限begin**/
				//先将旧有的权限删除，再新增新的权限
				$s_event_right = new voa_s_oa_event_invite();
				$s_event_right->delete_by_conds(array('acid'=> $in['acid']));
				//指定人员
				if (!empty($in['add_uids'])) {
					$users = $in['add_uids'];
				} else {
					$users = '';
				}
				if (!empty($in['add_ids'])) {
					$dp = $in['add_ids'];
				} else {
					$dp = '';
				}
				$idata = $idata2 = array();
				if($users) {
					foreach($users as $val) {
						$idata[] = array(
							'primary_id' => $val,
							'type' => 2,
							'acid' => $data['acid']
						);
					}
				}
				if($dp) {
					foreach($dp as $val) {
						$idata2[] = array(
							'primary_id' => $val,
							'type' => 1,
							'acid' => $data['acid']
						);
					}
				}

				$mydata = array_merge($idata, $idata2);
				$s_event_right->insert_multi($mydata);
			}
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		/**权限end**/
		return true;
	}

	/**
	 * 添加自定义值
	 * @param $in
	 * @param $out
	 */
	protected function _filed_can_add($in, &$out) {

		$field_data = array_column($in, 'desc');
		$serv = &service::factory('voa_s_oa_event_field');
		$data = array('field_name' => $field_data);
		$list = $serv->list_get_binary($data);
		//判断查找是否存在
		$list_filename = array();
		if($list){
			$list_filename = array_column($list, 'field_name');
		}

		$in_data = array();
		foreach($field_data as $val) {
			if(in_array($val, $list_filename)) {
				continue;
			}
			//组合sql插入
			$in_data[] = array(
				'field_name' => $val
			);
		}

		if($in_data){
			$serv-> insert_multi($in_data);
		}

		$out = $serv->list_get_binary($data);
		return true;
	}


}
