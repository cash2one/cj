<?php
/**
 * voa_h_user
 * $Author$
 * $Id$
 */

class voa_h_user {

	/** 已读取的用户列表 */
	public static $_user_list = array();

	/**
	 * 把用户信息推入数组
	 * @param array $user 用户信息数组
	 */
	public static function push($user) {
		/** 如果不是数组, 则 */
		if (!is_array($user)) {
			return false;
		}

		/** 如果有 m_uid 下标, 则说明当前数组为用户信息数组 */
		if (!empty($user['m_uid'])) {
			self::$_user_list[$user['m_uid']] = $user;
			return true;
		}

		/** 有可能是用户信息集合, 遍历重新进行推入操作 */
		foreach ($user as $u) {
			if (!is_array($u)) {
				continue;
			}

			self::push($u);
		}

		return true;
	}

	/**
	 * 根据 uid 获取指定用户
	 * @param int $uid
	 */
	public static function get($uid) {
		if (!empty(self::$_user_list[$uid])) {
			return (array)self::$_user_list[$uid];
		}

		$serv = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $serv->fetch_by_uid($uid);
		if (!empty($user)) {
			self::$_user_list[$uid] = $user;
		}

		return $user;
	}

	/**
	 * 获取用户头像
	 * @param number $uid
	 * @param array $user 用户信息
	 */
	public static function avatar($uid, $user = array()) {
		if ($uid && empty($user)) {
			$user = self::get($uid);
		}

		if (!empty($user['m_face'])) {
			if ('//' == substr($user['m_face'], -2)) {
				return substr($user['m_face'], 0, -1).'64';
			} elseif ('/' == substr($user['m_face'], -1)) {
				return $user['m_face'].'64';
			} elseif ('/64' != substr($user['m_face'], -3)) {
				return $user['m_face'].'/64';
			}
			return $user['m_face'];
		}

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$face_base_url = config::get(startup_env::get('app_name').'.oa_http_scheme').$sets['domain'];
		!isset($user['m_gender']) && $user['m_gender'] = 0;
		if (voa_d_oa_member::GENDER_MALE == $user['m_gender']) {
			return $face_base_url.config::get('voa.imgdir').'male.png';
		} elseif (voa_d_oa_member::GENDER_FEMALE == $user['m_gender']) {
			return $face_base_url.config::get('voa.imgdir').'female.png';
		} else {
			return $face_base_url.config::get('voa.imgdir').'male.png';
		}
	}

	/**
	 * 根据一组uid获取用户
	 * @param array $m_uids
	 * @return array
	 */
	public static function get_multi($m_uids) {

		$user_list = array();
		$get_m_uids = array();
		foreach ($m_uids as $_m_uid) {
			if (!isset(self::$_user_list[$_m_uid])) {
				$get_m_uids[$_m_uid] = $_m_uid;
			} else {
				$user_list[$_m_uid] = self::$_user_list[$_m_uid];
			}
		}

		if (!empty($get_m_uids)) {

			// 相关部门ID列表
			$cd_ids = array();
			// 取得指定用户信息
			$serv = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$_user_list = $serv->fetch_all_by_ids($get_m_uids);
			// 遍历以获取相关部门id
			foreach ($_user_list as $_m_uid => $_user) {
				if ($_user['cd_id'] && !isset($cd_ids[$_user['cd_id']])) {
					$cd_ids[$_user['cd_id']] = $_user['cd_id'];
				}
			}
			unset($_m_uid, $_user);
			// 相关部门信息
			$departments = array();
			if (!empty($cd_ids)) {
				$serv = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
				$departments = $serv->fetch_all_by_key($cd_ids);
			}
			unset($cd_ids);

			// 取得用户信息
			foreach ($_user_list as $_m_uid => $_user) {

				// 获取用户所在部门名称
				if ($_user['cd_id'] && !empty($departments[$_user['cd_id']])) {
					$_user['_department'] = $departments[$_user['cd_id']]['cd_name'];
				} else {
					$top = voa_h_department::get_top();
					$_user['_department'] = !empty($top) ? $top['cd_name'] : '';
				}
				if (empty($_user['m_face'])) {
					$_user['m_face'] = self::avatar($_m_uid, $_user);
				}
				self::push($_user);
				$user_list[$_m_uid] = $_user;
			}
			unset($_m_uid, $_user);
		}

		return $user_list;
	}

	/**
	 * 判断一个人员（或部门）是否在指定的人员列表或者部门列表范围内
	 * 某个参数设置为null，则不考虑该参数
	 * @param mixed $m_uid 指定的人员m_uid，可以是数组也可以是逗号分隔的字符串
	 * @param mixed $cd_id 指定的部门cd_id，可以是数组也可以是逗号分隔的字符串
	 * @param array $m_uids 人员范围列表
	 * @param array $cd_ids 部门范围列表
	 * @return boolean|null
	 * 人员列表和部门列表均为空，则返回null。否则：true=在指定范围，false=不在指定范围
	 */
	public static function in_area($m_uid = 0, $cd_id = 0, $m_uids = array(), $cd_ids = array()) {

		// 未设置则返回null
		if (empty($m_uids) && empty($cd_ids)) {
			return null;
		}
		if ($m_uid === null) {
			$m_uid = array();
		}
		if ($cd_id === null) {
			$cd_id = array();
		}
		if ($m_uids === null) {
			$m_uids = array();
		}
		if ($cd_ids === null) {
			$cd_ids = array();
		}
		// 转换给定的人员为数组
		if (!is_array($m_uid)) {
			$m_uid = explode(',', $m_uid);
		}
		// 设置了人员且当前人员在此范围，则在
		if (!empty($m_uids) && $m_uid && count(array_intersect($m_uid, $m_uids)) > 0) {
			return true;
		}
		// 如果没给定部门列表，则肯定不在
		if (empty($cd_ids)) {
			return false;
		}
		// 转换给定的部门为数组
		if (!is_array($cd_id)) {
			$cd_id = explode(',', $cd_id);
		}
		// 所有部门
		$all_cds = voa_h_cache::get_instance()->get('department', 'oa');
		// 部门为空，肯定不在
		if (empty($all_cds)) {
			return false;
		}
		// 遍历所有部门，以确定给定范围是否存在顶级部门，若存在，肯定在范围内
		foreach ($all_cds as $_cd) {
			// 给定的部门范围存在顶级部门，肯定在范围内
			if ($_cd['cd_upid'] == 0 && in_array($_cd['cd_id'], $cd_ids)) {
				return true;
			}
		}
		unset($_cd);
		// 确定当前人员所在的部门id
		$serv = new voa_s_oa_member_department();
		$tmp = $serv->fetch_all_by_uid($m_uid);
		if (!empty($tmp)) {
			foreach ($tmp as $_md) {
				if (!in_array($_md, $cd_id)) {
					$cd_id[] = $_md;
				}
			}
			unset($tmp, $_md);
		}
		// 指定人员所在部门为空，则不在
		if (empty($cd_id)) {
			return false;
		}
		// 在此范围
		if (count(array_intersect($cd_id, $cd_ids)) > 0) {
			return true;
		}
		// 上面均校验不过，则找到给定的部门范围下的所有部门
		foreach ($all_cds as $_cd) {
			if (!$_cd['cd_upid']) {
				// 忽略顶级
				continue;
			}
			// 当前部门的父级在给定的部门范围，且当前部门不在给定范围内，则追加
			if (in_array($_cd['cd_upid'], $cd_ids) && !in_array($_cd['cd_id'], $cd_ids)) {
				$cd_ids[] = $_cd['cd_id'];
			}
		}
		// 给定的部门在部门全部集合范围内，则在
		if (count(array_intersect($cd_id, $cd_ids)) > 0) {
			return true;
		}

		return false;
	}

}
