<?php
/**
 * base.php
 * 【活动页专题】畅移云工作用户2014年年终总结，基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_frontend_year2014_base extends voa_c_frontend_base {

	/** 公共缓存数据 */
	protected $_common = null;
	/** 数据访问对象 */
	protected $_d_year = null;
	/** 需要更新系统setting表的数据 */
	private $__update_setting = array();
	/** 需要更新的公共数据缓存 */
	private $__appkey_data = array();
	/** 数据统计开始时间 */
	protected $_starttime = 0;
	/** 数据统计结束时间 */
	protected $_endtime = 0;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		if ($this->_d_year === null) {
			$this->_d_year = new voa_d_oa_year2014();
		}

		$this->_starttime = 0;
		$this->_endtime = rstrtotime('2014-12-31 18:00:00');

		return true;
	}

	protected function _after_action($action) {

		if (!empty($this->__update_setting)) {
			$serv_setting = &service::factory('voa_s_oa_common_setting');
			$serv_setting->update_setting($this->__update_setting);
			voa_h_cache::get_instance()->get('setting', 'oa', true);
		}

		return true;
	}

	/**
	 * 内部的返回结果数值的方法
	 * @param string $floor 浮点值
	 * @param number $precision 小数点位数
	 */
	protected function _rround($floor = '0.00', $precision = 0) {

		$value = round($floor, 2) * 100;
		if ($value < 1) {
			$value = 1;
		}
		return ($value/100);
	}

	/**
	 * 公共数据缓存
	 * @param string $appkey
	 * @return mixed
	 */
	protected function _get_common($appkey = '') {

		if ($this->_common === null) {
			$this->_common = $this->_d_year->list_common();
		}

		if (empty($appkey)) {
			return '';
		}

		if (!isset($this->_common[$appkey])) {
			$data = '';
			$method = '__get_common_'.$appkey;
			$r = $this->$method($data);
			// 更新缓存
			if (!empty($this->__appkey_data)) {
				foreach ($this->__appkey_data as $_appkey => $_data) {
					$this->_d_year->set_common($_appkey, $_data);
				}
				if (isset($this->__appkey_data[$appkey])) {
					return $this->__appkey_data[$appkey];
				}
			}
			return $r;
		} else {
			return $this->_common[$appkey];
		}
	}

	/**
	 * 获取企业员工总数
	 * @return number
	 */
	private function __get_common_membercount() {

		$serv_member = &service::factory('voa_s_oa_member');
		$count = (int)$serv_member->count_all();

		$this->__appkey_data['membercount'] = $count;

		return $count;
	}

	/**
	 * 获取公司注册日期
	 * @return number
	 */
	private function __get_common_registertime() {

		// 注册时间戳
		$regiser_time = 0;
		// 自管理最高权限组获取企业注册开通日期，如果没有设置开启时间，并更新系统配置
		if (empty($this->_setting['register_time']) || !is_numeric($this->_setting['register_time'])) {

			$enterprise = array();
			$url = config::get('voa.uc_url') . 'OaRpc/Rpc/Enterprise';
			if (voa_h_rpc::query($enterprise, $url, 'get_by_ep_id', $this->_setting['ep_id'])) {
				$regiser_time = $enterprise['ep_created'];
			}

			// 避免无法获取的意外补救
			if ($regiser_time < 1) {
				$server_adminer = &service::factory('voa_s_oa_common_adminer');
				$regiser_time = $this->_endtime;
				foreach ($server_adminer->fetch_all() as $_ca) {
					if ($_ca['ca_created'] <= 0) {
						continue;
					}
					if ($_ca['ca_created'] < $regiser_time) {
						$regiser_time = $_ca['ca_created'];
					}
				}
				unset($_ca);
			}

			$this->__update_setting['register_time'] = $regiser_time;
			$this->__appkey_data['registertime'] = $regiser_time;
			$this->_setting['register_time'] = $regiser_time;
		}

		return $this->_setting['register_time'];
	}

	/**
	 * 日报全员报告数排行
	 * @return array
	 */
	private function __get_common_dailyreportcountlist() {

		$serv_dailyreport = &service::factory('voa_s_oa_dailyreport');
		$data = $serv_dailyreport->count_rank($this->_starttime, $this->_endtime);
		$this->__appkey_data['dailyreportcountlist'] = (array)$data;
		return $data;
	}

	/**
	 * 签到全员正常签到数排行
	 * @return array
	 */
	private function __get_common_signcountlist() {

		$serv_sign_record = &service::factory('voa_s_oa_sign_record');
		$data = $serv_sign_record->count_rank_by_status($this->_starttime, $this->_endtime
				, array(voa_d_oa_sign_record::STATUS_UNKNOWN, voa_d_oa_sign_record::STATUS_WORK));
		$this->__appkey_data['signcountlist'] = (array)$data;
		return $data;
	}

}
