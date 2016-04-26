<?php
/**
 * home.php
 * 【活动页专题】畅移云工作用户2014年年终总结
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_frontend_year2014_home extends voa_c_frontend_year2014_base {

	/** 读取的用户UID */
	private $__uid = 0;
	/** 当前读取的页面 */
	private $__appkey = '';
	/** 分享时间 */
	private $__time = 0;
	/** 页面链接 */
	private $_appkey_link_url = '';
	/** 当前正在读取的用户信息 */
	private $__member = array();

	public function _before_action($action) {

		$this->__uid = (int)$this->request->get('id');
		$this->__time = (int)$this->request->get('t');
		$sig = $this->request->get('sig');
		// 验证分享扰码是否正确
		if ($sig == voa_h_func::sig_create($this->__uid, $this->__time)) {
			// 扰码正确，则不需要登录
			$this->_require_login = false;
		}

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function _after_action($action) {

		if (!parent::_after_action($action)) {
			return false;
		}

		$this->response->stop();

		return true;
	}

	public function execute() {

		$serv_member = &service::factory('voa_s_oa_member');
		$this->__member = $serv_member->fetch($this->__uid);
		if (empty($this->__member)) {
			echo 'no user';
			return true;
		}

		// 合法的appkey
		$appkeys = array('summary','sign', 'dailyreport', 'project', 'meeting');
		$_appkeys = array('sign', 'dailyreport', 'project', 'meeting');
		// 获取所有的应用信息列表
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		foreach ($plugins as $_p) {
			if (!in_array($_p['cp_identifier'], $_appkeys)) {
				continue;
			}
			if ($_p['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
				continue;
			}
			$appkeys[] = $_p['cp_identifier'];
		}
		unset($_appkeys, $_p);

		// 页面类型标识字符串
		$appkey = (string)$this->request->get('key');

		// 请求的方法
		$appkey_data_method = '__get_'.$appkey.'_data';
		// 读取的是指定页面的数据
		if ($appkey && in_array($appkey, $appkeys) && method_exists($this, $appkey_data_method)) {
			// 输出页面
			@header("Content-type: application/json; charset=utf-8");
			// 输出的数据结果
			$result = $this->__appkey_data($appkey, null);
			$data = array(
				'errcode' => 0,
				'errmsg' => 'OK',
				'result' => $result
			);
			echo rjson_encode($data);
			return true;
		}

		//$this->__uid = 1;
		//$this->__time = time();

		// 所有链接
		$year_name = 'year2014';
		$this->_appkey_link_url['home_page'] = $this->_year_url($year_name, $this->__uid, $this->__time, '');
		foreach ($appkeys as $_appkey) {
			$this->_appkey_link_url[$_appkey] = $this->_year_url($year_name, $this->__uid, $this->__time, $_appkey);
		}

		// 静态资源绝对路径
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get(startup_env::get('app_name').'.oa_http_scheme');
		$static_url = $scheme.$sets['domain'];
		$static_url .= '/static/';
		$this->view->set('static_url', $static_url);

		// 页面链接
		$this->view->set('urls', rjson_encode($this->_appkey_link_url));
		// 输出页面
		$this->_output('year2014/home');
	}


	/**
	 * 读取并尝试更新缓存
	 * @param string $appkey 应用key
	 * @param string $variable 应用缓存的数据变量名
	 * @return mixed
	 */
	private function __appkey_data($appkey, $variable = null) {

		// 尝试读取缓存
		$source = $this->_d_year->get_by_uid_appkey($this->__uid, $appkey);
		if ($source !== false && !empty($source)) {

			// 读取全部
			if ($variable === null) {
				return $source;
			}

			// 读取指定的
			if (!isset($source[$variable])) {
				return $source[$variable];
			}
		}

		// 确定使用哪个方法读取
		$appkey_data_method = '__get_'.$appkey.'_data';
		// 读取的是指定页面的数据
		if (!method_exists($this, $appkey_data_method)) {
			exit($appkey.' not exists');
		}

		// 输出的数据结果
		$result = array();
		$this->$appkey_data_method($result);

		// 更新缓存
		$db_data = array(
			'appkey' => $appkey,
			'uid' => $this->__uid,
			'data' => serialize($result)
		);
		$this->_d_year->insert($db_data);

		// 输出全部
		if ($variable === null) {
			return $result;
		}

		// 输出某个变量
		return isset($result[$variable]) ? $result[$variable] : '';
	}

	/**
	 * 获取汇总数据
	 * @param array $data
	 * + days 公司注册总天数
	 * + regtime 公司注册日期
	 * + membercount 公司员工总数
	 * + jointime “我”注册的时间
	 * + endtime 数据统计截止时间
	 * + realname “我”的名字
	 * + companyname 公司名称
	 * @return boolean
	 */
	private function __get_summary_data(array &$data) {

		$data = array();

		// 公司注册时间戳
		$regiser_time = $this->_get_common('registertime');
		// 公司总员工数
		$member_count = $this->_get_common('membercount');

		// 读取我的信息
		$join_time = $this->__member['m_created'];

		$data = array(
			'days' => ceil(($this->_endtime - $regiser_time) / 86400),// 公司注册天数
			'regtime' => rgmdate($regiser_time, 'Y年n月j日'),// 公司注册日期
			'membercount' => $member_count,// 员工总数
			'jointime' => rgmdate($join_time, 'Y年n月j日'),// “我”的注册日期
			'mydays' => $join_time > 0 ? ceil(($this->_endtime - $join_time) / 86400) : 0,// “我”注册的天数
			'realname' => $this->__member['m_username'],// “我”的名称
			'endtime' => rgmdate($this->_endtime, 'Y年n月j日 H时'),// 数据统计截止日期
			'companyname' => $this->_setting['sitename']// 公司名称
		);

		return true;
	}

	/**
	 * 获取签到应用数据
	 * @param array $data
	 * + ranknum 排名
	 * + days 签到天数
	 * + usertotal 总人数
	 * + rate 超过百分比的人
	 * @return boolean
	 */
	private function __get_sign_data(array &$data) {

		// 全员签到次数排行列表
		$count_rank_list = $this->_get_common('signcountlist');
		// 签到人员总数
		$total = count($count_rank_list);
		// 排行数
		// 默认如果无此人数据，则排行最后一位
		$ranknum = $total + 1;
		// 员工总数
		$usertotal = $this->_get_common('membercount');
		if ($usertotal < $total) {
			$usertotal = $total + 1;
		}
		// 签到天数
		$days = 1;
		// 遍历以确认其排行数
		$i = 0;
		foreach ($count_rank_list as $_uid => $_count) {
			if ($this->__uid == $_uid) {
				$i++;
				$days = $_count;
				break;
			}
		}
		unset($i, $_uid, $_count);

		$data = array(
			'ranknum' => $ranknum,
			'days' => $days,
			'usertotal' => $usertotal,
			'rate' => ($usertotal > 0 && $usertotal >= $ranknum) ? $this->_rround(($usertotal - $ranknum)/$usertotal, 4) * 100 : 0
		);

		return true;
	}

	/**
	 * 获取任务应用数据
	 * @param array $data
	 * + complete 完成数
	 * + total 总数
	 * + uncomplete 未完成数
	 * + complete_rate 完成百分比
	 * + uncomplte_rate 未完成百分比
	 * @return boolean
	 */
	private function __get_project_data(array &$data) {

		$serv_project = &service::factory('voa_s_oa_project_proc');
		// 完成数
		$complete = $serv_project->count_by_uid($this->__uid, true, $this->_starttime, $this->_endtime);
		// 总数
		$total = $serv_project->count_by_uid($this->__uid, 'all', $this->_starttime, $this->_endtime);
		// 未完成数
		$uncomplete = $total - $complete;

		$data = array(
			'complete' => $complete,// 完成数
			'total' => $total,// 总数
			'uncomplete' => $uncomplete,// 未完成数
			'complete_rate' => $total > 0 ? $this->_rround($complete / $total, 4) * 100 : 0,// 完成百分比
			'uncomplete_rate' => $total > 0 ? $this->_rround($uncomplete / $total, 4) * 100 : 0// 未完成百分比
		);

		return true;
	}

	/**
	 * 获取日报应用数据
	 * @param array $data
	 * + total 全员日报总数
	 * + count “我”的日报总数
	 * + ranknum “我”发布的日报排名
	 * + rate “我”发的日报占全员百分比
	 * @return boolean
	 */
	private function __get_dailyreport_data(array &$data) {

		// 全员日报数排行列表
		$count_rank_list = $this->_get_common('dailyreportcountlist');
		// 日报总数
		$total = array_sum($count_rank_list);
		// 排行数
		// 默认如果无此人数据，则排行最后一位
		$ranknum = count($count_rank_list) + 1;
		// 日报数
		$count = 0;
		// 遍历以确认其排行数
		$i = 0;
		foreach ($count_rank_list as $_uid => $_count) {
			if ($this->__uid == $_uid) {
				$i++;
				$count = $_count;
				break;
			}
		}
		unset($i, $_uid, $_count);

		$data = array(
			'ranknum' => $ranknum,
			'count' => $count,
			'total' => $total,
			'rate' => $total > 0 ? $this->_rround($count/$total, 4) * 100 : 0
		);

		return true;
	}

	/**
	 * 获取会议应用数据
	 * @param array $data
	 * + count “我”确认参会次数
	 * + daily 日均参会次数
	 * @return boolean
	 */
	private function __get_meeting_data(array &$data) {

		// 统计“我”的参会次数
		$serv_meeting = &service::factory('voa_s_oa_meeting_mem');
		$count = $serv_meeting->count_by_m_uid($this->__uid, $this->_starttime, $this->_endtime);

		// “我”注册的天数
		$mydays = $this->__appkey_data('summary', 'mydays');
		$mydays < 0 && $mydays = 1;

		$data = array(
			'count' => $count,// “我”确认参会次数
			'daily' => $this->_rround($count/$mydays, 2)// 日均参会次数
		);

		return true;
	}

}
