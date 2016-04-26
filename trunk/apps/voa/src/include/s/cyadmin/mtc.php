<?php
/**
 * voa_s_cyadmin_mtc
 * mysql 数据系统缓存读取
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_s_cyadmin_mtc extends service {

	/** 分库/分表的信息 */
	private $__shard_key = array();

	/**
	 * __construct
	 *
	 * @param  array $shard_key
	 * @return void
	 */
	public function __construct($shard_key = array()) {

		$this->__shard_key = $shard_key;
	}

	/** 读取指定数据 */
	public function fetch($cachename, $group) {
		try {
			/** 判断是否有专门的处理函数 */
			$func = 'fetch_'.$cachename;
			if (method_exists($this, $func)) {
				return $this->$func();
			}

			/** 判断是否为模块缓存 */
			$cns = explode('.', $cachename);
			if ('plugin' == $cns[0] && 2 < count($cns)) {
				$class = 'voa_c_frontend_'.$cns[1].'_base';
				array_shift($cns);
				$func = 'fetch_cache_'.implode('_', $cns);
				return call_user_func(array($class, $func));
			}

			/** 其它缓存 */
			$serv = &service::factory('voa_s_cyadmin_common_syscache');
			$data = $serv->fetch($cachename);
			if (voa_d_cyadmin_common_syscache::TYPE_ARRAY == $data['csc_type']) {
				$data['csc_data'] = unserialize($data['csc_data']);
			}

			return $data['csc_data'];
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取配置信息 */
	public function fetch_setting() {
		try {
			$serv = &service::factory('voa_s_cyadmin_common_setting', array('pluginid' => 0));
			$data = $serv->fetch_all();
			/** 重新整理数据 */
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_cyadmin_common_setting::TYPE_ARRAY == $v['cs_type']) {
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

	/** 读取后台管理菜单 */
	public function fetch_cpmenu() {
		try {
			$serv = &service::factory('voa_s_cyadmin_common_cpmenu', array('pluginid' => 0));
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

//	/** 读取所有应用 */
//	public function fetch_domain_app () {
//		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . config::get('voa.cyadmin_domain.domain_app') . '/CaRpc/Rpc/Plugin');
//		$plugins = $rpc->list_all();
//
//		return $plugins;
//	}

	/** 读取所有管理员信息 */
	public function fetch_adminer() {
		$serv = &service::factory('voa_s_cyadmin_common_adminer');
		$data = $serv->fetch_all();

		return $data;
	}

	/** 读取应用列表 */
	public function fetch_domain_applist () {

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . config::get('voa.cyadmin_domain.uc_domain') . '/CaRpc/Rpc/PluginGroup');

		return $rpc->list_all();
	}

	/** 读取所有数据 */
	public function fetch_all($cachenames) {
		try {
			$serv = &service::factory('voa_s_cyadmin_common_syscache', array('pluginid' => 0));
			$data = $serv->fetch_all($cachenames);
			/** 重新整理数据 */
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_cyadmin_common_syscache::TYPE_ARRAY == $v['csc_type']) {
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
