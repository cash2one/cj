<?php
/**
 * EnterpriseService.class.php
 * $author$
 */

namespace Common\Service;

class EnterpriseService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Enterprise");
	}

	/**
	 * 根据手机号码统计记录数
	 * @param string $mobile 手机号码
	 * @return Ambigous <multitype:, number, mixed>
	 */
	public function count_by_mobile($mobile) {

		return $this->_d->count_by_mobile($mobile);
	}

	/**
	 * 检查企业账号是否有效
	 * @param array $enterprise 企业信息
	 * @param string $enumber 企业账号
	 */
	public function check_enumber(&$enterprise, $enumber) {

		// 判断企业账号的格式
		if (!$enumber || !preg_match('/^[a-z]([a-z0-9]{4,19})$/i', $enumber)) {
			$this->_set_error('_ERR_ENUMBER_INVALID');
			return false;
		}

		// 禁止注册的企业号（二级域名），多个之间使用半角逗号分隔
		$black_list = "dcapi,admin,testing,changyi,vchangyi,redmine";
		if (in_array(rstrtolower($enumber), explode(',', $black_list))) {
			$this->_set_error('_ERR_ENUMBER_BLACKLIST');
			return false;
		}

		// 根据企业账号读取数据
		if ($enterprise = $this->_d->get_by_enumber($enumber)) {
			$this->_set_error('_ERR_ENUMBER_EXISTS');
			return false;
		}

		return true;
	}

	/**
	 * 套件安装时, 信息检查
	 * @param array $params 提交的数据
	 * @return boolean
	 */
	public function check_install_params(&$params) {

		// 套件ID
		$params['suiteid'] = (string)$params['suiteid'];
		// 企业号的 corpid
		$params['corpid'] = (string)$params['corpid'];
		// 企业账号(二级域名)
		$params['enumber'] = (string)$params['enumber'];
		// 手机号码
		$params['mobilephone'] = (string)$params['mobilephone'];
		// 新密码
		$params['newpw'] = (string)$params['newpw'];
		// 真实名称
		$params['realname'] = (string)$params['realname'];
		// 邮箱
		$params['email'] = (string)$params['email'];
		// 企业名称
		$params['ename'] = (string)$params['ename'];
		// 行业
		$params['industry'] = (string)$params['industry'];
		// 公司规模
		$params['companysize'] = (string)$params['companysize'];

		// 如果企业账号(域名)为空
		if (empty($params['enumber'])) {
			$params['enumber'] = 'z' . NOW_TIME . random(5);
		}

		// 套件ID格式检查
		if (!\Com\Validator::is_suiteid($params['suiteid'])) {
			$this->_set_error('_ERR_SUITEID_INVALID');
			return false;
		}

		// 检查 corpid 格式
		if (!\Com\Validator::is_corpid($params['corpid'])) {
			$this->_set_error('_ERR_CORPID_INVALID');
			return false;
		}

		// 检查企业账号格式
		if (!preg_match('/^[a-z][0-9a-z]{4,}/i', $params['enumber'])) {
			$this->_set_error('_ERR_ENUMBER_INVALID');
			return false;
		}

		// 检查手机号码
		if (!\Com\Validator::is_mobile($params['mobilephone'])) {
			$this->_set_error('_ERR_MOBILE_INVALID');
			return false;
		}

		// 判断企业名称是否为空
		if (empty($params['ename'])) {
			$this->_set_error('_ERR_ENTERPRISE_NAME_EMPTY');
			return false;
		}

		// 检查邮箱格式
		if (!\Com\Validator::is_email($params['email'])) {
			$this->_set_error('_ERR_EMAIL_INVALID');
			return false;
		}

		// 企业账号只能为小写
		$params['enumber'] = rstrtolower($params['enumber']);
		return true;
	}

	/**
	 * 通过 ep_id 更新企业信息(初期只让改企业名称)
	 * @param int $ep_id 企业ID
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id($ep_id, $enterprise = array()) {

		// 更新的企业信息不能为空(初期只让改企业名称)
		if (empty($enterprise) || empty($enterprise['ep_name'])) {
			E('_ERR_ENTERPRISE_PROFILE_EMPTY');
			return false;
		}

		$ep_id = (int)$ep_id;
		// 企业ID不能为空
		if (empty($ep_id)) {
			E('_ERR_EP_ID_INVALID');
			return false;
		}

		return $this->_d->update($ep_id, array('ep_name' => $enterprise['ep_name']));
	}

}
