<?php

/**
 * voa_uda_frontend_cinvite_base
 * 邀请人员/uda基类
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:18
 */
class voa_uda_frontend_cinvite_base extends voa_uda_frontend_base {

	protected $_serv_setting = null;
	protected $_serv_personnel = null;

	//是否需要审批
	protected $is_approval = array(
		voa_d_oa_cinvite_personnel::NOT_APPROVAL => '直接邀请',
		voa_d_oa_cinvite_personnel::IS_APPROVAL  => '审批邀请',
	);

	//性别状态
	protected $_gender = array(
		voa_d_oa_cinvite_personnel::GENDER_UNKNOWN => '未知',
		voa_d_oa_cinvite_personnel::GENDER_MAN     => '男',
		voa_d_oa_cinvite_personnel::GENDER_WOMAN   => '女'
	);
	//关注状态
	protected $_qywxstatus = array(
		voa_d_oa_cinvite_personnel::STATUS_YES  => '已关注',
		voa_d_oa_cinvite_personnel::STATUS_NO   => '未关注',
		voa_d_oa_cinvite_personnel::STATUS_NON  => '未关注',
		voa_d_oa_cinvite_personnel::STATUS_NONO => '未关注'
	);
	//微信是否存在
	protected $_weixin_id = array(
		voa_d_oa_cinvite_personnel::WEIXINID_KNOW   => '存在',
		voa_d_oa_cinvite_personnel::WEIXINID_UNKNOW => '未知'
	);
	//审核状态
	protected $_status = array(
		voa_d_oa_cinvite_personnel::CHECK_ING => '审批中',
		voa_d_oa_cinvite_personnel::CHECK_END => '已通过',
		voa_d_oa_cinvite_personnel::CHECK_NO  => '未通过',
		voa_d_oa_cinvite_personnel::NO_CHECK  => '无需核审'
	);


	public function __construct() {
		parent::__construct();
		//$this->_status[voa_d_oa_cinvite_personnel::CHECK_ING];
		if( $this->_serv_setting == null ) {
			$this->_serv_personnel = &service::factory( 'voa_s_oa_cinvite_personnel' );
			$this->_serv_setting   = &service::factory( 'voa_s_oa_cinvite_setting' );
		}
	}

}
