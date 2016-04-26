<?php
/**
 * EnterpriseAdminerService.class.php
 * $author$
 */

namespace PubApi\Service;

use Com\Validator;
class EnterpriseAdminerService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/EnterpriseAdminer");
	}

	/**
	 * 读取所有相关列表
	 * @param array $list 企业管理员列表
	 * @param array $params 输入参数
	 * + ep_id 企业ID
	 * + page 页码
	 * + perpage 每页数量
	 */
	public function list_adminer(&$list, $params) {

		$conds = array();
		// 获取参数
		extract_field($conds, array(
			'ep_id' => array('ep_id', 'array', true)
		), $params);

		// 企业ID不能为空
		if (empty($conds['ep_id'])) {
			E('_ERR_EP_ID_EMPTY');
			return false;
		}

		// 分页参数
		$page = (int)$params['page'];
		$perpage = (int)$params['perpage'];
		list($start, $perpage, $page) = page_limit($page, $perpage);

		// 读取记录
		$list = $this->_d->list_by_conds($conds, array($start, $perpage), array('ca_id' => 'ASC'));
		$this->format_adminer($list);
		return true;
	}

	/**
	 * 根据查询条件获取企业管理员信息
	 * @param array $adminer 企业管理员信息
	 * @param array $params 输入参数
	 * + int ep_id 企业ID
	 * + string mobile 手机号码
	 */
	public function get_adminer(&$adminer, $params) {

		$conds = array();
		// 获取参数
		extract_field($conds, array(
			'ep_id' => array('ep_id', 'int', true),
			'mobile' => array('mobilephone', 'string', true)
		), $params);

		// 企业ID为空
		if (empty($conds['ep_id'])) {
			E('_ERR_EP_ID_EMPTY');
			return false;
		}

		// 手机号码为空
		if (empty($conds['mobilephone'])) {
			E('_ERR_MOBILE_EMPTY');
			return false;
		}

		// 读取记录
		$adminer = $this->_d->get_by_conds($conds);
		$this->format_adminer($adminer);
		return true;
	}

	/**
	 * 删除管理员
	 * @param array $params 请求参数
	 * @return boolean
	 */
	public function del_adminer($params) {

		$conds = array();
		// 获取参数
		extract_field($conds, array(
			'ep_id' => array('ep_id', 'int', true),
			'mobile' => array('mobilephone', 'string', true)
		), $params);

		if (empty($conds['ep_id']) || empty($conds['mobilephone'])) {
			E('_ERR_CONDS_EMPTY');
			return false;
		}

		$this->_d->delete_by_conds($conds);
		return true;
	}

	/**
	 * 更新企业管理员信息
	 * @param array $params 输入参数
	 * + int ep_id 企业ID
	 * + string mobile 手机号码
	 * @return boolean
	 */
	public function update_adminer($params) {

		$adminer = array();
		// 获取参数
		extract_field($adminer, array(
			'ep_id' => array('ep_id', 'int', true),
			'cur_mobile' => array('cur_mobile', 'string', true),
			'mobile' => array('mobilephone', 'string', true),
			'password' => array('password', 'string', true),
			'realname' => array('realname', 'string', true),
			'userstatus' => array('userstatus', 'int', true)
		), $params);

		// 获取企业ID
		$ep_id = (int)$adminer['ep_id'];
		if (0 >= $ep_id) {
			E('_ERR_EP_ID_EMPTY');
			return false;
		}

		unset($adminer['ep_id']);
		// 待更新数据不能为空
		if (empty($adminer)) {
			E('_ERR_UPDATE_DATA_EMPTY');
			return false;
		}

		// 检查用户名称
		if (empty($adminer['realname'])) {
			unset($adminer['realname']);
		}

		// 用户状态
		if (empty($adminer['userstatus'])) {
			unset($adminer['userstatus']);
		} elseif (!in_array($adminer['userstatus'], $this->_d->list_user_status())) {
			E('_ERR_USER_STATUS_INVALID');
			return false;
		}

		// 如果需要修改密码
		if (!empty($adminer['password'])) {
			list($adminer['password'], $adminer['salt']) = generate_password($adminer['password']);
		} else {
			unset($adminer['password']);
		}

		// 判断用户是否存在
		$conds = array('ep_id' => $ep_id, 'mobilephone' => $adminer['cur_mobile']);
		unset($adminer['cur_mobile']);
		$old_adminer = $this->_d->get_by_conds($conds);
		if (empty($old_adminer)) {
			E('_ERR_ADMINER_IS_NOT_EXIST');
			return false;
		}

		// 读取管理员, 判断是否重复
		if ($this->_mobile_is_exist($mobile, $ep_id)) {
			E('_ERR_MOBILE_EXIST');
			return false;
		}

		$this->_d->update($old_adminer['ad_id'], $adminer);
		return true;
	}

	/**
	 * 获取 CODE
	 * @param unknown $result
	 * @param unknown $params
	 */
	public function get_code(&$result, $params) {

		$ep_id = (int)$params['ep_id'];
		$mobile = (string)$params['username'];
		// 根据手机号码读取用户信息
		$conds = array('mobilephone' => $mobile, 'ep_id' => $ep_id);
		$adminer = $this->_d->get_by_conds($conds);
		if (empty($adminer)) {
			E('_ERR_ADMINER_IS_NOT_EXIST');
			return false;
		}

		// 登录操作入库, 生成临时code值
		$result['code'] = generate_code($mobile, $adminer['salt']);
		// code 入库
		$serv_code = D('Common/LoginCode');
		$serv_code->insert(array(
			'ep_id' => $ep_id,
			'lc_code' => $result['code'],
			'ad_id' => $adminer['ad_id']
		));

		return true;
	}

	/**
	 * 用户登录操作
	 * @param array $result 登录接口返回值
	 * @param array $params 输入参数
	 * @return boolean
	 */
	public function login(&$result, $params) {

		$ep_id = (int)$params['ep_id'];
		$mobile = (string)$params['username'];
		$passwd = (string)$params['passwd'];
		// 根据手机号码读取用户信息
		$conds = array('mobilephone' => $mobile, 'ep_id' => $ep_id);
		$adminer = $this->_d->get_by_conds($conds);
		if (empty($adminer)) {
			E('_ERR_ADMINER_IS_NOT_EXIST');
			return false;
		}

		// 判断是否原始密码
		if (!\Com\Validator::is_md5($passwd)) {
			$origin = true;
		} else {
			$origin = false;
		}
		// 密码生成
		list($passwd, ) = generate_password($passwd, $adminer['salt'], $origin);
		// 判断密码是否正确
		if (empty($adminer['password']) || $passwd != $adminer['password']) {
			E('_ERR_PASSWD_ERROR');
			return false;
		}

		// 登录操作入库, 生成临时code值
		$result['code'] = generate_code($mobile, $adminer['salt']);
		// code 入库
		$serv_code = D('Common/LoginCode');
		$serv_code->insert(array(
			'ep_id' => $ep_id,
			'lc_code' => $result['code'],
			'ad_id' => $adminer['ad_id']
		));

		return true;
	}

	/**
	 * 获取用户信息
	 * @param array $result 接口返回值
	 * @param array $params 请求参数
	 * @return boolean
	 */
	public function get_user(&$result, $params) {

		$expires = cfg('CODE_EXPIRES');
		$ep_id = $params['ep_id'];
		$code = $params['code'];
		// 读取登录记录
		$model_code = D('Common/LoginCode');
		$log = $model_code->get_by_ep_id_code($ep_id, $code);
		if (empty($log) || $log['lc_created'] + $expires < NOW_TIME) {
			E('_ERR_CODE_EXPIRED');
			return false;
		}

		// 读取管理者
		$model_adminer = D('Common/EnterpriseAdminer');
		$adminer = $model_adminer->get($log['ad_id']);
		if (empty($adminer)) {
			E('_ERR_ADMINER_IS_NOT_EXIST');
			return false;
		}

		$this->format_adminer($adminer);
		$result['adminer'] = $adminer;
		return true;
	}

	public function add_adminer(&$adminer, $params) {

		$adminer = array();
		// 获取参数
		extract_field($adminer, array(
			'ep_id' => array('ep_id', 'int', true),
			'mobile' => array('mobilephone', 'string', true),
			'ca_id' => array('ca_id', 'int', true),
			'password' => array('password', 'string', true),
			'realname' => array('realname', 'string', true),
			'userstatus' => array('userstatus', 'int', true)
		), $params);

		// 检查 ep_id
		if (empty($adminer['ep_id']) || 0 >= $adminer['ep_id']) {
			E('_ERR_EP_ID_ERROR');
			return false;
		}

		// 检查用户名称
		if (empty($adminer['realname'])) {
			E('_ERR_ADMINER_NAME_EMPTY');
			return false;
		}

		// 检查手机格式
		if (empty($adminer['mobilephone']) || !Validator::is_mobile($adminer['mobilephone'])) {
			E('_ERR_MOBILE_INVALID');
			return false;
		}

		// 密码不能为空
		if (empty($adminer['password']) || !Validator::is_md5($adminer['password'])) {
			E('_ERR_PASSWORD_INVALID');
			return false;
		}

		// 用户状态
		if (empty($adminer['userstatus']) || !in_array($adminer['userstatus'], $this->_d->list_user_status())) {
			E('_ERR_USER_STATUS_INVALID');
			return false;
		}

		// 如果干扰码为空, 则重新生成
		list($adminer['password'], $adminer['salt']) = generate_password($adminer['password']);

		// 读取管理员, 判断是否重复
		if ($this->_mobile_is_exist($mobile)) {
			E('_ERR_MOBILE_EXIST');
			return false;
		}

		$this->_d->insert($adminer);
		return true;
	}

	/**
	 * 判断手机号码是否重复
	 * @param string $mobile 手机号码
	 * @return boolean
	 */
	protected function _mobile_is_exist($mobile, $ep_id = 0) {

		$adminer = $this->_d->get_by_conds(array('mobilephone' => $mobile));
		if (empty($adminer) || (0 < $ep_id && $ep_id == $adminer['ep_id'])) {
			return false;
		}

		return true;
	}

	/**
	 * 格式化管理员
	 * @param array $adminer 管理员信息
	 */
	public function format_adminer(&$adminer) {

		// 如果是单个管理员
		if (isset($adminer['ep_id'])) {
			$adminer = array(
				'ep_id' => $adminer['ep_id'],
				'ca_id' => $adminer['ca_id'],
				'realname' => $adminer['realname'],
				'mobile' => $adminer['mobilephone'],
				'ad_id' => $adminer['ad_id'],
				'userstatus' => $adminer['userstatus'],
				'created' => $adminer['created']
			);
			return true;
		}

		// 格式化列表
		foreach ($adminer as &$_adminer) {
			$this->format_adminer($_adminer);
		}

		return true;
	}

}
