<?php
/**
 * AbstractController.class.php
 * $author$
 */
namespace PubApi\Controller\Apicp;
use Common\Common\Plugin;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController {

	const WX_STATUS_UNFOLLOW = 4; // 微信 未关注状态

	// 部门缓存
	protected $_departments = array();
	// 部门人数
	protected $_dep_count = array();
	// 规则开启 关闭范围
	protected $_array_range = array();
	// 职位
	protected $_jobs = array();

	// 规则 (开启)
	const ALLOW = 1;
	// 规则 (关闭)
	const UNALLOW = 0;

	// 人员性别
	const MALE = 1;
	const G_UNKNOWN= 0;
	const FMALE = 2;
	const C_MALE = '男';
	const C_G_UNKNOWN = '未知';
	const C_FMALE = '女';

	// 人员关注状态
	const QYWXST_FOLLOW = 1;
	const QYWXST_UNFOLLOW = 4;
	const QYWXST_FROZEN = 2;
	const C_FOLLOW = '已关注';
	const C_UNFOLLOW = '未关注';
	const C_FROZEN = '已禁用';


	public function before_action($action = '') {

		if (!parent::before_action($action)) {
			return false;
		}

		$cache = &\Common\Common\Cache::instance();
		$this->_departments = $cache->get('Common.department');

		$cache = &\Common\Common\Cache::instance();
		$this->_jobs = $cache->get('Common.job');
		return true;
	}

	// 获取插件配置
	protected function _get_plugin() {

		// 获取插件信息
		/**$this->_plugin = &Plugin::instance('addressbook');
		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());*/

		return true;
	}

	/**
	 * 从某一个部门中删除 单个或多个 用户id
	 * @param array $uids 要删除的用户id
	 * + int m_uid
	 * @param int $cd_id
	 * @return bool
	 */
	protected function _delete_uids_in_department($uids, $cd_id) {

		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$serv_mem_dep->delete_by_conds(array('m_uid IN (?)' => $uids, 'cd_id' => $cd_id));

		return true;
	}

	/**
	 * 写入单个或多个 用户id 部门关联 数据
	 * @param $uids_data
	 * @param $cd_id
	 */
	protected function _insert_uids_to_department($uids_data, $cd_id) {

		// 拼凑 用户id 部门id 入库数据
		$insert_data = array();
		foreach ($uids_data as $_uid) {
			$insert_data[] = array(
				'm_uid' => $_uid,
				'cd_id' => $cd_id,
			);
		}

		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$serv_mem_dep->insert_all($insert_data);
	}

	/**
	 * 更新部门人数
	 * @return bool
	 */
	protected function _update_department_num() {

		// 获取所有部门ID
		$cd_ids = array_column($this->_departments, 'cd_id');
		if (!empty($cd_ids)) {
			$serv_com_dep = D('Common/CommonDepartment', 'Service');

			// 部门关联树数组
			$counts = array();
			$serv_mem_dep = D('Common/MemberDepartment', 'Service');
			foreach ($cd_ids as $_dep) {
				$temp = $serv_mem_dep->count_dep_member($_dep);
				$counts[] = array(
					'cd_id' => $_dep,
					'ct' => empty($temp) ? 0 : (int)$temp,
				);
			}

			// 更新有变化的部门
			foreach ($this->_departments as $_dep_data) {
				foreach ($counts as $_ct) {
					// 如果部门id相同 并且 部门人数不相同 则更新
					if ($_dep_data['cd_id'] == $_ct['cd_id'] && $_dep_data['cd_usernum'] != $_ct['ct']) {
						$serv_com_dep->update_by_conds(array('cd_id' => $_ct['cd_id']), array('cd_usernum' => $_ct['ct']));
						continue;
					}
				}
			}
		}

		// 更新缓存
		clear_cache();
		return true;
	}

	/**
	 * 验证字符串长度是否介于某个范围
	 * @param string $string
	 * @param array $rule 验证规则
	 * @uses $rule = array(unit, min, max)<br />
	 * unit 长度单位，byte使用字节长，count使用字符数<br />
	 * min 最小长度<br />
	 * max 最大长度
	 * @return boolean
	 */
	public function validator_length(&$string, $rule) {

		$string = (string)$string;
		list($unit_type, $min, $max) = $rule;

		if (stripos($unit_type, 'byte') !== false) {
			// 使用字节长验证
			$length = strlen($string);
			$error_msg = '长度应该介于 ' . $min . '到' . $max . ' 字节之间';
		} else {
			// 使用字符数验证
			$length = mb_strlen($string, 'utf-8');
			$error_msg = '长度应该介于 ' . $min . '到' . $max . ' 个字符之间';
		}

		if ($length >= $min && $length <= $max) {
			return true;
		} else {
			$this->errmsg('9001', $error_msg);
			return false;
		}
	}

	/**
	 * 查询部门下的人
	 * @param $deps
	 * @param $uids
	 * @return bool
	 */
	protected function _member_in_dep($deps, &$uids) {

		$serv_mp = D('Common/MemberDepartment', 'Service');
		$dep_data = $serv_mp->list_by_conds(array('cd_id'=> $deps));

		$uids = array_unique(array_column($dep_data, 'm_uid'));

		return true;
	}

	/**
	 * 人员列表数据处理
	 * @param $members
	 * @return bool
	 */
	protected function _list_fm(&$members) {

		if (empty($members)) {
			return true;
		}

		$list = array();
		foreach ($members as &$_mem) {
			// 性别
			switch ($_mem['m_gender']) {
				case self::G_UNKNOWN :
					$_mem['m_gender'] = self::C_G_UNKNOWN;
					break;
				case self::MALE :
					$_mem['m_gender'] = self::C_MALE;
					break;
				case self::FMALE :
					$_mem['m_gender'] = self::C_FMALE;
					break;
			}
			// 关注状态
			switch ($_mem['m_qywxstatus']) {
				case self::QYWXST_FOLLOW:
					$_mem['m_qywxstatus'] = self::C_FOLLOW;
					break;
				case self::QYWXST_UNFOLLOW:
					$_mem['m_qywxstatus'] = self::C_UNFOLLOW;
					break;
			}
			if ($_mem['m_active'] == self::UNALLOW) {
				$_mem['m_qywxstatus'] = self::C_FROZEN;
			}
			// 职位
			if (!empty($_mem['cj_id'])) {
				$_mem['cj_name'] = $this->_get_cj_name($_mem['cj_id']);
			}

			$_mem = array(
				'm_uid' => $_mem['m_uid'],
				'm_username' => $_mem['m_username'],
				'm_mobilephone' => $_mem['m_mobilephone'],
				'm_email' => $_mem['m_email'],
				'm_gender' => $_mem['m_gender'],
				'm_face' => $_mem['m_face'],
				'cj_name' => empty($_mem['cj_name']) ? '' : $_mem['cj_name'],
				'm_qywxstatus' => $_mem['m_qywxstatus'],
			);
		}

		return true;
	}

	/**
	 * 人员详情数据处理
	 * @param $user_data
	 * @return bool
	 */
	protected function _format_user_data($user_data) {

		if (empty($user_data)) {
			return true;
		}

		// 性别
		switch ($user_data['m_gender']) {
			case self::G_UNKNOWN :
				$user_data['m_gender'] = self::C_G_UNKNOWN;
				break;
			case self::MALE :
				$user_data['m_gender'] = self::C_MALE;
				break;
			case self::FMALE :
				$user_data['m_gender'] = self::C_FMALE;
				break;
		}
		// 关注状态
		switch ($user_data['m_qywxstatus']) {
			case self::QYWXST_FOLLOW:
				$user_data['m_qywxstatus_name'] = self::C_FOLLOW;
				break;
			case self::QYWXST_UNFOLLOW:
				$user_data['m_qywxstatus_name'] = self::C_UNFOLLOW;
				break;
		}
		if ($user_data['m_active'] == self::UNALLOW) {
			$user_data['m_qywxstatus_name'] = self::C_FROZEN;
		}

		// 职位
		if (!empty($user_data['cj_id'])) {
			$user_data['cj_name'] = $this->_get_cj_name($user_data['cj_id']);
		}

		// 查询人员部门关联
		$serv_mem_department = D('Common/MemberDepartment', 'Service');
		$dep_list = $serv_mem_department->list_by_conds(array('m_uid' => $user_data['m_uid']));
		// 获取部门列表
		$cd_name = array();
		foreach ($dep_list as $_dep) {
			if (isset($this->_departments[$_dep['cd_id']])) {
				$cd_name[] = $this->_departments[$_dep['cd_id']]['cd_name'];
			}
		}
		$cd_name = implode('、', $cd_name);

		return array(
			'm_uid' => $user_data['m_uid'],
			'm_weixin' => empty($user_data['m_weixin']) ? '' : $user_data['m_weixin'],
			'm_username' => $user_data['m_username'],
			'm_active' => $user_data['m_active'],
			'm_mobilephone' => empty($user_data['m_mobilephone']) ? '' : $user_data['m_mobilephone'],
			'm_email' => empty($user_data['m_email']) ? '' : $user_data['m_email'],
			'm_qywxstatus_name' => $user_data['m_qywxstatus_name'],
			'm_gender' => $user_data['m_gender'],
			'cd_name' => empty($cd_name) ? '' : $cd_name,
			'cj_name' => empty($user_data['cj_name']) ? '' : $user_data['cj_name'],
			'm_face' => empty($user_data['m_face']) ? '' : $user_data['m_face'],
		);
	}

	/**
	 * 获取职位名称
	 * @param $cj_id 职位ID
	 * @return bool
	 */
	protected function _get_cj_name($cj_id) {

		return isset($this->_jobs[$cj_id]) ? $this->_jobs[$cj_id]['cj_name'] : '';
	}

	/**
	 * 获取人员属性规则
	 * @return array|mixed
	 */
	protected function _get_field() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		return $setting['fields'];
	}

	/**
	 * 获取人员自定义 字段数据
	 * @param int $uid 用户uid
	 * @param array $custom 自定义信息
	 * @return bool
	 */
	protected function _get_custom($uid, &$custom) {

		$field = $this->_get_field();
		$serv_field = D('Common/MemberField', 'Service');
		$serv_mem = D('Common/Member', 'Service');

		// 查询
		$memfield = $serv_field->get_by_conds(array('m_uid' => $uid));

		// 选出开启的自定义字段 并且 显示
		$custom = array();
		if (empty($field['custom'])) {
			return true;
		}

		foreach ($field['custom'] as $_name => $_rule) {
			// 开启的字段
			if ($_rule['open'] != self::ALLOW || $_rule['view'] != self::ALLOW) {
				continue;
			}

			// 直属领导
			if ($_name == 'leader') {
				if (empty($memfield['mf_' . $_name])) {
					continue;
				}
				$leader_id = explode(',', $memfield['mf_' . $_name]);
				$leader_list = $serv_mem->list_by_conds(array('m_uid' => $leader_id));
				$leader_name = '';
				if (!empty($leader_list)) {
					$leader_name = implode(',', array_column($leader_list, 'm_username'));
				}
				$custom[] = array(
					'name' => $_rule['name'],
					'value' => $leader_name,
				);

				continue;
			}

			// 过滤为空数据
			if (!empty($memfield['mf_' . $_name]) || $memfield['mf_' . $_name] === 0 || $memfield['mf_' . $_name] === '0') {
				$custom[] = array(
					'name' => $_rule['name'],
					'value' => $memfield['mf_' . $_name],
				);
			}
		}

		return true;
	}
}
