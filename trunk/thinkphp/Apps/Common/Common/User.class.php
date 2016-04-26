<?php
/**
 * User.class.php
 * 用户操作
 * $Author$
 */

namespace Common\Common;
use Think\Log;
use Common\Common\Cache;

class User {

	// 用户列表
	protected $_list = array();

	// 实例化
	public static function &instance() {

		static $instance;
		if (empty($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	public function __construct() {

		// do nothing.
	}

	/**
	 * 获取指定用户的头像
	 * @param int $uid 用户UID
	 * @param array $user 用户信息详情
	 * @return string|Ambigous <>
	 */
	public function avatar($uid, $user = array()) {

		// 如果用户信息为空, 则根据uid读取
		if ($uid && empty($user)) {
			$user = $this->get($uid);
		}

		// 如果头像信息存在
		if (!empty($user['m_face'])) {
			// 如果后两个字符为 // 则重新取
			if ('//' == substr($user['m_face'], -2)) {
				return substr($user['m_face'], 0, -1).'64';
			} elseif ('/' == substr($user['m_face'], -1)) { // 以 / 结尾时
				return $user['m_face'].'64';
			} elseif ('/64' != substr($user['m_face'], -3)) { // 如果不是以 /64 结尾
				return $user['m_face'].'/64';
			}

			return $user['m_face'];
		}

		// 读取缓存
		$cache = &Cache::instance();
		$sets = $cache->get('Common.setting');
		// 头像的基础url
		$avatar_base_url = cfg('PROTOCAL') . $sets['domain'] . cfg('IMGDIR');

		// 如果性别信息为空
		if (!isset($user['m_gender'])) {
			$user['m_gender'] = 0;
		}

		// 如果性别为女
		if (\Common\Model\MemberModel::GENDER_FEMALE == $user['m_gender']) {
			$avatar = $avatar_base_url . 'female.png';
		} else {
			$avatar = $avatar_base_url . 'male.png';
		}

		return $avatar;
	}

	/**
	 * 把用户信息推入数组
	 * @param array $user 用户信息数组
	 */
	public function push($users) {

		// 如果不是数组, 则
		if (!is_array($users)) {
			return false;
		}

		// 如果有 m_uid 下标, 则说明当前数组为用户信息数组
		if (!empty($users['m_uid'])) {
			$this->_list[$users['m_uid']] = $users;
			return true;
		}

		// 有可能是用户信息集合, 遍历重新进行推入操作
		foreach ($users as $_u) {
			if (!is_array($_u)) {
				continue;
			}

			$this->push($_u);
		}

		return true;
	}

	/**
	 * 根据 uid 获取指定用户
	 * @param int $uid 用户uid
	 */
	public function get($uid) {
		// 如果用户已存在
		if (!empty($this->_list[$uid])) {
			return (array)$this->_list[$uid];
		}
		// 从数据库里重新读取
		$serv = D('Common/Member', 'Service');
		$user = $serv->get($uid);
		if (!empty($user)) {
			$this->_list[$uid] = $user;
		}
		return $user;
	}

	/**
	 * 根据用户uid读取用户信息
	 * @param array $uids uid数组
	 * @return multitype:|unknown
	 */
	public function list_by_uid($uids) {

		$uids = (array)$uids;
		// 如果用户 uid 为空
		if (empty($uids)) {
			return array();
		}

		// 剔除已存在的用户
		$exist_users = array(); // 已存在的用户信息
		$left_uids = array(); // 缺失的uid
		foreach ($uids as $_uid) {
			// 如果用户已存在
			if (!empty($this->_list[$_uid])) {
				$exist_users[] = $this->_list[$_uid];
				continue;
			}

			$left_uids[] = $_uid;
		}

		// 如果没有需要读取的用户
		if (empty($left_uids)) {
			return $exist_users;
		}

		// 从数据库里读取用户信息
		$serv = D('Common/Member', 'Service');
		$users = $serv->list_by_pks($left_uids);
		$this->push($users);

		return array_merge($exist_users, $users);
	}

}
