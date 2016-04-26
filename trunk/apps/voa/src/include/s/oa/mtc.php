<?php
/**
 * voa_s_mtc
 * mysql 数据系统缓存读取
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_mtc extends service {

	/** 分库/分表的信息 */
	private $__shard_key = array();
	protected static $_caches = null;

	/**
	 * __construct
	 *
	 * @param  array $shard_key
	 * @return void
	 */
	public function __construct($shard_key = array()) {

		$this->__shard_key = $shard_key;
	}

	public function list_syscache() {

		if (null != self::$_caches) {
			return true;
		}

		$serv_cache = Service::factory('voa_s_oa_common_syscache');
		self::$_caches = $serv_cache->list_all();
		if (empty(self::$_caches)) {
			self::$_caches = array();
		}

		return true;
	}

	public function insert_syscache($cachename) {

		if (!empty(self::$_caches[$cachename])) {
			return true;
		}

		$serv_cache = Service::factory('voa_s_oa_common_syscache');
		$syscache = array(
			'csc_name' => $cachename,
			'csc_type' => 1,
			'csc_data' => ''
		);
		$id = 0;
		$serv_cache->insert($syscache, $id, false);
		self::$_caches[$cachename] = $syscache;
		return true;
	}

	/** 读取指定数据 */
	public function fetch($cachename, $group) {

		try {
			// 更新缓存名称
			$this->list_syscache();
			/** 判断是否有专门的处理函数 */
			$func = 'fetch_'.$cachename;
			if (method_exists($this, $func)) {
				$this->insert_syscache($cachename);
				return $this->$func();
			}

			/** 判断是否为模块缓存 */
			$cns = explode('.', $cachename);
			// 指定后台权限组的后台操作菜单缓存
			if ('adminergroupcpmenu' == $cns[0] && isset($cns[1]) && is_numeric($cns[1])) {
				$this->insert_syscache($cachename);
				return voa_h_cpmenu::adminer_group_cpmenu((int)$cns[1]);
			}

			if ('plugin' == $cns[0] && 2 < count($cns)) {
				$class = 'voa_c_frontend_'.$cns[1].'_base';
				array_shift($cns);
				$func = 'fetch_cache_'.implode('_', $cns);
				if (!method_exists($class, $func)) {
					logger::error('method is not exists('.$class.'::'.$func.').');
					return false;
				}

				$this->insert_syscache($cachename);
				return call_user_func(array($class, $func));
			}

			// uc 缓存
			if ('uc' == $cns[0] && 1 < count($cns)) {
				return array();
			}

			/** 其它缓存 */
			$data = voa_d_oa_common_syscache::fetch($cachename);
			if (voa_d_oa_common_syscache::TYPE_ARRAY == $data['csc_type']) {
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

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/OaRpc/Rpc/EnterpriseAppset');
		$probation_time = $rpc->get_appset();

		// 增加创建时间
		$probation_time['created'] = startup_env::get('timestamp');

		return $probation_time;
	}

	/**
	 * 获取类型缓存
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function fetch_columntype() {

		try {
			// 取字段类型数据
			$t = new voa_d_oa_common_columntype();
			$list = $t->list_all();

			// 重新组织数据
			$rets = array();
			if (is_array($list)) {
				foreach ($list as $_v) {
					$rets[$_v['ct_type']] = $_v;
				}
			}

			return $rets;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取商品表格缓存
	 * @throws service_exception
	 * @return Ambigous <unknown, multitype:multitype: unknown >
	 */
	public function fetch_goodstable() {

		try {
			// 取字段类型数据
			$t = new voa_d_oa_goods_table();
			$list = $t->list_all();
			// 格式化表格数据, 建立一个 tid => tunique 的关系数组
			$ret = array(
				'tid2tunique' => array()
			);
			// 遍历
			if (is_array($list)) {
				foreach ($list as $_v) {
					$ret['tid2tunique'][$_v['tid']] = $_v['tunique'];
					$ret[$_v['tunique']] = $_v;
				}
			}

			return $ret;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取表格缓存
	 * @throws service_exception
	 * @return Ambigous <unknown, multitype:multitype: unknown >
	 */
	public function fetch_diytable() {

		try {
			// 取字段类型数据
			$t = new voa_d_oa_diy_table();
			$list = $t->list_all();
			// 格式化表格数据, 建立一个 tid => tunique 的关系数组
			$ret = array(
				'tid2tunique' => array()
			);
			// 遍历
			if (is_array($list)) {
				foreach ($list as $_v) {
					$ret['tid2tunique'][$_v['tid']] = $_v['tunique'];
					$ret[$_v['tunique']] = $_v;
				}
			}

			return $ret;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取客户表格缓存
	 * @throws service_exception
	 * @return Ambigous <unknown, multitype:multitype: unknown >
	 */
	public function fetch_customertable() {

		try {
			// 取字段类型数据
			$t = new voa_d_oa_customer_table();
			$list = $t->list_all();
			// 格式化表格数据, 建立一个 tid => tunique 的关系数组
			$ret = array(
				'tid2tunique' => array()
			);
			// 遍历
			if (is_array($list)) {
				foreach ($list as $_v) {
					$ret['tid2tunique'][$_v['tid']] = $_v['tunique'];
					$ret[$_v['tunique']] = $_v;
				}
			}

			return $ret;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	// 获取公众号配置
	public function fetch_wxmp() {

		try {
			$serv = &service::factory('voa_s_oa_wxmp_setting');
			$list = $serv->list_all();
			$arr = array();
			if (is_array($list)) {
				foreach ($list as $_v) {
					if (voa_d_oa_wxmp_setting::TYPE_ARRAY == $_v['type']) {
						$arr[$_v['key']] = unserialize($_v['value']);
					} else {
						$arr[$_v['key']] = $_v['value'];
					}
				}
			}

			return $arr;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取微信配置(缓存) */
	public function fetch_weixin() {

		try {
			$serv = &service::factory('voa_s_oa_weixin_setting', array('pluginid' => 0));
			$data = $serv->fetch_all();
			/** 重新整理数据 */
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_oa_weixin_setting::TYPE_ARRAY == $v['ws_type']) {
					$arr[$v['ws_key']] = unserialize($v['ws_value']);
				} else {
					$arr[$v['ws_key']] = $v['ws_value'];
				}
			}

			return $arr;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取配置信息 */
	public function fetch_setting() {

		try {
			$serv = &service::factory('voa_s_oa_common_setting', array('pluginid' => 0));
			$data = $serv->fetch_all();
			/** 重新整理数据 */
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_oa_common_setting::TYPE_ARRAY == $v['cs_type']) {
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

	/** 获取职位列表 */
	public function fetch_job() {

		try {
			$serv = &service::factory('voa_s_oa_common_job', array('pluginid' => 0));
			return $serv->fetch_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取部门列表 */
	public function fetch_department() {

		try {
			$serv = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			return $serv->fetch_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取后台管理菜单 */
	public function fetch_cpmenu() {

		try {
			$serv = &service::factory('voa_s_oa_common_cpmenu', array('pluginid' => 0));
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

	/** 读取已开通应用信息 */
	public function fetch_plugin() {

		try {
			$serv = &service::factory('voa_s_oa_common_plugin', array('pluginid' => 0));
			return $serv->fetch_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取地区信息 */
	public function fetch_region() {

		try {
			$serv = &service::factory('voa_s_oa_common_region');
			$list = $serv->list_all();

			/** 组织数据 */
			$regions = array(
				'level' => array(),
				'data' => array()
			);
			if (is_array($list)) {
				foreach ($list as $_id => $_r) {
					if (!array_key_exists($_r['cr_parent_id'], $regions['level'])) {
						$regions['level'][$_r['cr_parent_id']] = array();
					}

					$regions['level'][$_r['cr_parent_id']][$_r['cr_id']] = $_r['cr_id'];
					$regions['data'][$_r['cr_id']] = $_r;
				}
			}

			return $regions;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取店铺信息 */
	public function fetch_shop() {

		try {
			$serv = &service::factory('voa_s_oa_common_shop');
			return $serv->list_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 获取插件分组 */
	public function fetch_plugin_group() {

		try {
			$serv = &service::factory('voa_s_oa_common_plugin_group', array('pluginid' => 0));
			return $serv->fetch_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 读取所有数据 */
	public function fetch_all($cachenames) {

		try {
			$serv = &service::factory('voa_d_oa_common_syscache', array('pluginid' => 0));
			$data = $serv->fetch_all($cachenames);
			/** 重新整理数据 */
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_oa_common_syscache::TYPE_ARRAY == $v['csc_type']) {
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

	/**
	 * 场所设置表缓存
	 * @throws service_exception
	 * @return multitype:mixed unknown
	 */
	public function fetch_common_place_setting() {
		try {
			$s = microtime(true);
			$d_place_setting = new voa_d_oa_common_place_setting();
			$data = $d_place_setting->list_all();
			// 重新整理数据
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_oa_common_place_setting::TYPE_ARRAY == $v['type']) {
					$arr[$v['key']] = unserialize($v['value']);
				} else {
					$arr[$v['key']] = $v['value'];
				}
			}

			return $arr;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 返回 场所 类型数据
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_common_place_type() {
		try {
			// 取所有类型数据
			$t = new voa_d_oa_common_place_type();
			$ret = (array)$t->list_all();

			return $ret;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 返回 场所 分区数据
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_common_place_region() {
		try {
			// 载入场所区域表
			$t = new voa_d_oa_common_place_region();
			$list = $t->list_all();
			// 初始化数据
			$ret = array(
				// 未“删除”的
				voa_d_oa_common_place_region::REMOVE_NO => array(
					'level' => array(),// 以类型为组的分级关系
					'data' => array(),// 完整区域数据
					'type' => array(),// 类型下的所有区域ID
				),
				// 已“删除”的
				voa_d_oa_common_place_region::REMOVE_YES => array(
					'level' => array(),// 以类型为组的分级关系
					'data' => array(),// 完整区域数据
					'type' => array(),// 类型下的所有区域ID
				)
			);
			if (empty($list)) {
				return $ret;
			}
			// 遍历并按类别以分区级别关系输出
			foreach ($list as $_v) {
				// 以上级分区ID为键名列出对应分区ID数组
				$ret[$_v['remove']]['level'][$_v['parentid']][$_v['placeregionid']] = $_v['placeregionid'];
				// 类型下的所有分区ID
				$ret[$_v['remove']]['type'][$_v['placetypeid']][$_v['placeregionid']] = $_v['placeregionid'];
				// 所有分区信息
				$ret[$_v['remove']]['data'][$_v['placeregionid']] = $_v;
			}

			return $ret;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 返回 场地 数据
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_common_place() {
		try {
			// 载入场所表
			$t = new voa_d_oa_common_place();
			$list = $t->list_all();
			// 初始化返回数据
			$ret = array(
				'data' => array(),// 场地所有数据
				'type' => array(),// 类型分组下的场地数据ID
				'remove' => array(),// 已经移除的场所数据
			);
			if (empty($list)) {
				return $ret;
			}
			// 遍历并按类别分组所有的场地数据
			foreach ($list as $_p) {

				// 已“移除”
				if ($_p['remove'] == voa_d_oa_common_place::REMOVE_YES) {
					$ret['remove'][$_p['placeid']] = $_p;
					continue;
				}
				// 按类型分组场地数据ID
				$ret['type'][$_p['placetypeid']][$_p['placeid']] = $_p['placeid'];
				// 所有场地数据
				$ret['data'][$_p['placeid']] = $_p;
			}

			return $ret;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
