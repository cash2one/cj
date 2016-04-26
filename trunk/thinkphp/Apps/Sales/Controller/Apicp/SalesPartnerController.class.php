<?php
/**
 * SalesPartnerController.class.php
 * $author$
 */
namespace Sales\Controller\Apicp;

class SalesPartnerController extends AbstractController {

	/**
	 * 添加联合跟进人
	 * @param int $sc_id 客户ID
	 * @param array $m_uids 联合跟进人ID(数组)
	 * @return Array $sp_ids 联合跟进人表主键ID(数组)
	 * $author zhubeihai
	 */
	public function Add_partner_post() {

		// 联合跟人信息
		$partner = array ();
		// 用户提交的参数
		$params = I('request.');
		// 非用户提交的扩展参数
		$extend = array (
			'uid'      => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);

		// 如果新增操作失败
		$serv_partner = D('Sales/SalesPartner', 'Service');
		if (!$serv_partner->add_partner($partner, $params, $extend)) {
			E($serv_partner->get_errcode().':'.$serv_partner->get_errmsg());
			return false;
		}

		// 格式化
		$serv_fmt = D('Sales/Format', 'Service');
		$serv_fmt->partner_format($partner);
		$this->_result = $partner;
		return true;
	}
}
