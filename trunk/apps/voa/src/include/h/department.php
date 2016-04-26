<?php
/**
 * voa_h_department
 * 读取部门的公共静态类
 * $Author$
 * $Id$
 */

class voa_h_department {

	/** 已读取的部门列表 */
	public static $_dp_list = array();

	/**
	 * 把部门信息推入数组
	 * @param array $dp 部门信息数组
	 */
	public static function push($dp) {
		// 如果不是数组, 则
		if (!is_array($dp)) {
			return false;
		}

		// 如果有 cd_id 下标, 则说明当前数组为部门信息数组
		if (!empty($dp['cd_id'])) {
			self::$_dp_list[$dp['cd_id']] = $dp;
			return true;
		}

		// 有可能是部门信息集合, 遍历重新进行推入操作
		foreach ($dp as $_dp) {
			if (!is_array($_dp)) {
				continue;
			}

			self::push($_dp);
		}
		unset($_dp);

		return true;
	}

	/**
	 * 根据 cd_id 获取指定部门信息
	 * @param int $cd_id
	 */
	public static function get($cd_id) {
		if (!empty(self::$_dp_list[$cd_id])) {
			return (array)self::$_dp_list[$cd_id];
		}

		// 部门数据缓存
		$caches = voa_h_cache::get_instance()->get('department', 'oa');
		if (!empty($caches[$cd_id])) {
			// 存在缓存则直接使用缓存
			$dp = $caches[$cd_id];
		} else {
			// 缓存没有则读取数据库，避免缓存意外不同步的问题
			$serv = &service::factory('voa_s_oa_common_department');
			$dp = $serv->fetch($cd_id);
		}

		if (!empty($dp)) {
			self::$_dp_list[$cd_id] = $dp;
		}

		return $dp;
	}

	/**
	 * 根据一组cd_id获取部门信息
	 * @param array $cd_ids
	 * @return array
	 */
	public static function get_multi($cd_ids) {

		// 要输出的部门信息列表
		$dp_list = array();
		// 需要重新读取部门信息的cd_id列表
		$get_cd_ids = array();
		// 遍历给定的cd_id列表查找未曾读取过的部门信息
		foreach ($cd_ids as $_cd_id) {
			if (!isset(self::$_dp_list[$_cd_id])) {
				$get_cd_ids[$_cd_id] = $_cd_id;
			} else {
				$dp_list[$_cd_id] = self::$_dp_list[$_cd_id];
			}
		}
		unset($_cd_id);

		// 不存在未曾读取过的部门cd_id，则直接输出
		if (empty($get_cd_ids)) {
			return $dp_list;
		}

		// 下面处理未曾处理过的部门 self::_dp_list不存在的

		// 部门数据缓存
		$caches = voa_h_cache::get_instance()->get('department', 'oa');

		// 需要从数据库来读取的id
		$get_db_cd_ids = array();
		// 遍历要读取的部门id，提取自缓存读取的和从数据库读取的id
		foreach ($get_cd_ids as $_cd_id) {

			// 缓存内不存在此id，则标记从数据库读取
			if (!isset($caches[$_cd_id])) {
				$get_db_cd_ids[$_cd_id] = $_cd_id;
			}
			// 当前进程的部门信息
			$_dp = $caches[$_cd_id];
			// 推送到成员内
			self::push($_dp);
			// 推送到本次输出
			$dp_list[$_dp['cd_id']] = $_dp;
		}
		unset($_cd_id);

		// 需要从数据库读取的部门
		if (!empty($get_db_cd_ids)) {
			$serv = &service::factory('voa_s_oa_common_department');
			$_dp_list = $serv->fetch_all_by_key($get_db_cd_ids);
			if ($_dp_list) {
				foreach ($_dp_list as $_dp) {
					self::push($_dp);
					$dp_list[$_dp['cd_id']] = $_dp;
				}
			}
			unset($_dp_list, $_dp);
		}

		return $dp_list;
	}

	/**
	 * 获取顶级部门（一般即时公司）的信息
	 * @return array
	 */
	public static function get_top() {

		// 顶级部门（公司）信息
		$top_department = array();
		// 部门数据缓存
		$caches = voa_h_cache::get_instance()->get('department', 'oa');
		foreach ($caches as $_dp) {
			if (!$_dp['cd_upid']) {
				$top_department = $_dp;
				break;
			}
		}

		// 如果自缓存数据读取不到，则自数据库读取
		if (empty($top_department)) {
			$serv = &service::factory('voa_s_oa_common_department');
			$tmp = (array)$serv->fetch_all_by_upid(0);
			$tmp = array_slice($tmp, 0, 1);
			$top_department = isset($tmp[0]) ? $tmp[0] : array();
			unset($tmp);
		}

		return $top_department;
	}

}

