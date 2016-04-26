<?php
/**
 * voa_s_uc_mtc
 * mysql 数据系统缓存读取
 * $Author$
 * $Id$
 */

class voa_s_uc_mtc extends service {

	public function __construct() {
		// do nothing.
	}

	/**
	 * 读取指定数据
	 */
	public function fetch($cachename, $group) {
		try {
			// 判断是否有专门的处理函数
			$func = 'fetch_'.$cachename;
			if (method_exists($this, $func)) {
				return $this->$func();
			}

			// 判断是否为模块缓存
			$cns = explode('.', $cachename);
			if ('plugin' == $cns[0] && 2 < count($cns)) {
				$class = 'voa_c_frontend_'.$cns[1].'_base';
				array_shift($cns);
				$func = 'fetch_cache_'.implode('_', $cns);
				return call_user_func(array($class, $func));
			}

			// 其它缓存
			$data = voa_d_uc_common_syscache::fetch($cachename);
			if (voa_d_uc_common_syscache::TYPE_ARRAY == $data['csc_type']) {
				$data['csc_data'] = unserialize($data['csc_data']);
			}

			return $data['csc_data'];
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 试用期时间 */
	public function fetch_probation_time() {

		// 主后台地址
		$domain = config::get('voa.cyadmin_domain.domain_url');

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/UcRpc/Rpc/EnterpriseAppset');
		$probation_time = $rpc->get_appset();

		// 增加创建时间
		$probation_time['created'] = startup_env::get('timestamp');

		return $probation_time;
	}

	/**
	 * 获取配置信息
	 */
	public function fetch_setting() {
		try {
			$serv = &service::factory('voa_s_uc_setting', array('pluginid' => 0));
			$data = $serv->fetch_all();
			// 重新整理数据
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_uc_setting::TYPE_ARRAY == $v['cs_type']) {
					$arr[$v['cs_key']] = unserialize($v['cs_value']);
				} else {
					$arr[$v['cs_key']] = $v['cs_value'];
				}
			}

			return $arr;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*
	 * 读取后台管理菜单
	 */
	public function fetch_cpmenu() {
		try {
			$serv = &service::factory('voa_s_uc_common_cpmenu', array('pluginid' => 0));
			$data = array();
			foreach ($serv->fetch_all() AS $pk => $v) {
				unset($v['ccm_created'], $v['ccm_updated'], $v['ccm_deleted']);
				$data[$pk] = $this->trim_field($v, 'ccm_');
			}
			return $data;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/*
	 * 读取所有数据
	 */
	public function fetch_all($cachenames) {
		try {
			$data = voa_d_uc_common_syscache::fetch_all($cachenames);
			// 重新整理数据
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_uc_common_syscache::TYPE_ARRAY == $v['csc_type']) {
					$arr[$v['csc_name']] = unserialize($v['csc_data']);
				} else {
					$arr[$v['csc_name']] = $v['csc_data'];
				}
			}

			return $arr;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
