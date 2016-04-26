<?php
/**
 * EnterpriseService.class.php
 * $author$
 */

namespace PubApi\Service;
use Think\Crypt\Driver\Xxtea;

class EnterpriseService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Enterprise");
	}

	/**
	 * 读取所有相关列表
	 * @param array $list 企业列表
	 * @param array $params 输入参数
	 * + int ep_id 企业ID
	 * + string cname 二级域名
	 * + string companyname 公司名称
	 * + string mobile 手机号码
	 * + string email 邮箱
	 * + int page 页码
	 * + int perpage 每页个数
	 */
	public function list_enterprise(&$list, $params) {

		$conds = array();
		// 获取参数
		extract_field($conds, array(
			'ep_id' => array('ep_id', 'array', true),
			'cname' => array('ep_enumber', 'string', true),
			'companyname' => array('ep_name', 'string', true),
			'mobile' => array('ep_adminmobilephone', 'string', true),
			'email' => array('ep_adminemail', 'string', true)
		), $params);

		// 剔除空字串
		foreach ($conds as $_key => $_val) {
			if (empty($_val)) {
				unset($conds[$_key]);
			}
		}

		// 企业名称支持模糊查询
		if (isset($conds['ep_name'])) {
			$conds['ep_name LIKE ?'] = "%{$conds['ep_name']}%";
		}

		// 分页参数
		$page = (int)$params['page'];
		$perpage = (int)$params['perpage'];
		list($start, $perpage, $page) = page_limit($page, $perpage);

		// 读取记录
		$list = $this->_d->list_by_conds($conds, array($start, $perpage), array('ep_id' => 'ASC'));
		$this->format_enterprise($list);
		return true;
	}

	/**
	 * 根据查询条件获取企业信息
	 * @param array $enterprise 企业信息
	 * @param array $params 输入参数
	 * + int ep_id 企业ID
	 * + string cname 二级域名
	 * + string mobile 手机号码
	 */
	public function get_enterprise(&$enterprise, $params) {

		$conds = array();
		// 获取参数
		extract_field($conds, array(
			'ep_id' => array('ep_id', 'int', true),
			'cname' => array('ep_enumber', 'string', true),
			'mobile' => array('ep_adminmobilephone', 'string', true)
		), $params);

		// 剔除空字串
		foreach ($conds as $_key => $_val) {
			if (empty($_val)) {
				unset($conds[$_key]);
			}
		}

		// 如果查询条件为空
		if (empty($conds)) {
			E('_ERR_SO_CONDI_EMPTY');
			return false;
		}

		// 读取记录
		$enterprise = $this->_d->get_by_conds($conds);
		$this->format_enterprise($enterprise);

		return true;
	}

	/**
	 * 更新企业信息
	 * @param array $params 输入参数
	 * + int ep_id 企业ID
	 * + string mobile 手机号码
	 * + string companyname 公司名称
	 * + string email 邮箱
	 * @return boolean
	 */
	public function update_enterprise($params) {

		$conds = array();
		// 获取参数
		extract_field($conds, array(
			'ep_id' => array('ep_id', 'int', true),
			'mobile' => array('ep_adminmobilephone', 'string', true),
			'companyname' => array('ep_name', 'string', true),
			'email' => array('ep_adminemail', 'string', true),
			'realname' => array('ep_adminrealname', 'string', true)
		), $params);

		// 获取企业ID
		$ep_id = (int)$conds['ep_id'];
		if (0 >= $ep_id) {
			E('_ERR_EP_ID_EMPTY');
			return false;
		}

		unset($conds['ep_id']);
		// 待更新数据不能为空
		if (empty($conds)) {
			E('_ERR_UPDATE_DATA_EMPTY');
			return false;
		}

		$this->_d->update($ep_id, $conds);
		return true;
	}

	/**
	 * 检查名称是否重复
	 * @param array $result 返回值
	 * @param array $params 请求参数
	 * @return boolean
	 */
	public function checkEpname(&$result, $params) {

		$epname = (string)$params['epname'];
		$enterprise = $this->_d->get_by_conds(array('ep_name' => $epname));
		if (!empty($enterprise)) {
			E('_ERR_ENTERPRISE_NAME_EXIST');
			return false;
		}

		return true;
	}

	/**
	 * 检查手机是否已经注册过
	 * @param array $result 返回值
	 * @param array $params 请求参数
	 */
	public function checkMobile(&$result, $params) {

		$mobile = (string)$params['mobile'];
		$enterprise = $this->_d->get_by_conds(array('ep_adminmobilephone' => $mobile));
		if (!empty($enterprise)) {
			E('_ERR_MOBILE_IS_EXIST');
			return false;
		}

		/**$model_adminer = D('Common/EnterpriseAdminer', 'Service');
		$adminer = $model_adminer->get_by_conds(array('mobilephone' => $mobile));
		if (!empty($adminer)) {
			E('_ERR_MOBILE_IS_EXIST');
			return false;
		}*/

		return true;
	}

	/**
	 * 检查 CorpID 是否已经授权
	 * @param array $result 返回值
	 * @param array $params 请求参数
	 */
	public function checkCorpID(&$result, $params) {

		$corpid = (string)$params['corpid'];
		$data = array();
		$url = cfg('CYADMIN_RPC_HOST') . '/UcRpc/Rpc/EnterpriseProfile';
		if (!\Com\Rpc::query($data, $url, 'get_by_corpid', $corpid)) {
			E('_ERR_CYADMIN_RPC_ERROR');
			return false;
		}

		// 如果返回值为空
		if (!empty($data)) {
			E('_ERR_CORPID_IS_EXIST');
			return false;
		}

		return true;
	}

	/**
	 * 注册新企业
	 * @param array $result 返回值
	 * @param array $params 请求参数
	 * @return boolean
	 */
	public function register(&$result, $params) {

		$enterprise = array();
		// 获取参数
		extract_field($enterprise, array(
			'mobilephone' => array('mobilephone', 'string', true),
			//'smsauth' => array('smsauth', 'string', true),
			'realname' => array('realname', 'string', true),
			'email' => array('email', 'string', true),
			'ename' => array('ename', 'string', true),
			'enumber' => array('enumber', 'string', true),
			'password' => array('password', 'string', true),
			'industry' => array('industry', 'string', true),
			'companysize' => array('companysize', 'string', true),
			'unionid' => array('unionid', 'string', true),
			'ref' => array('ref', 'string', true),
			'ref_domain' => array('ref_domain', 'string', true)
		), $params);

		$enterprise['smsauth'] = rbase64_encode(Xxtea::encrypt($enterprise['mobilephone'], ''));
		rfopen($data, 'http://uc.vchangyi.com/uc/api/post/register', $enterprise, array(), 'POST');
		// 注册操作
		if (0 < $data['errcode']) {
			E($data['errcode'] . ':' . $data['errmsg']);
			return false;
		}

		$ep_id = substr($data['result']['submitauth'], 32);
		$result['ep_id'] = $ep_id;

		return true;
	}

	/**
	 * 格式化企业信息
	 * @param array $enterprise 企业信息
	 */
	public function format_enterprise(&$enterprise) {

		if (isset($enterprise['ep_enumber'])) {
			$enterprise = array(
				'ep_id' => $enterprise['ep_id'],
				'cname' => $enterprise['ep_enumber'],
				'companyname' => $enterprise['ep_name'],
				'email' => $enterprise['ep_adminemail'],
				'mobile' => $enterprise['ep_adminmobilephone'],
				'realname' => $enterprise['ep_adminrealname'],
				'created' => $enterprise['ep_created']
			);
			return true;
		}

		foreach ($enterprise as &$_ep) {
			$this->format_enterprise($_ep);
		}

		return true;
	}

}
