<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/16
 * Time: 下午10:14
 */

class voa_d_oa_askfor_ormproc extends voa_d_abstruct {

	/** 审批中 */
	const STATUS_NORMAL = 1;
	/** 已通过 */
	const STATUS_APPROVE = 2;
	/** 通过并转审批 */
	const STATUS_APPROVE_APPLY = 3;
	/** 审批不通过 */
	const STATUS_REFUSE = 4;
	/** 抄送 */
	const STATUS_CARBON_COPY = 5;
	/** 已催办 */
	const STATUS_REMINDER = 6;
	/** 已撤销 */
	const STATUS_CANCEL = 7;
	/** 已删除 */
	const STATUS_REMOVE = 8;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.askfor_proc';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 表前缀 */
		$this->_prefield = 'afp_';
		/** 主键 */
		$this->_pk = 'afp_id';

		parent::__construct(null);
	}
}

