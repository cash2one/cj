<?php
/**
 * personnel.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:05
 */

class voa_d_oa_invite_personnel extends voa_d_abstruct {

	/** 性别字段 */
	const GENDER_UNKNOWN = 0;//未知
	const GENDER_MAN = 1;	//男
	const GENDER_WOMAN = 2;	//女
	/** 数据状态字段 */
	const STATUS_INITIALIZATION = 0;//初始化
	const STATUS_UPDATE = 1;	//更新
	const GENDER_DEL = 2;	//删除
	/** 关注状态 */
	const STATUS_YES = 1;	//已关注
	const STATUS_NO = 0;	//未关注（没有选择表示为未关注）
	const STATUS_NON = 2;	//已冻结（已冻结标记为未关注）
	const STATUS_NONO = 4;	//未关注
	/** 微信是否存在 */
	const WEIXINID_KNOW = 1;	//存在
	const WEIXINID_UNKNOW = 0;	//未知
	/**
	 * 配置字段，审批字段
	 */
	const CHECK_NOTHING = -1; //不限
	const CHECK_ING = 0; //审批中
	const CHECK_END = 1; //已审批
	const CHECK_NO = 2; //未通过
	const NO_CHECK = 3; //无需核审
	/** 是否需要审批  */
	const NOT_APPROVAL = 0; //直接邀请：不需审批
	const IS_APPROVAL = 1; //审批邀请：需要审批

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.invite_personnel';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'per_id';

		parent::__construct(null);
	}

}
