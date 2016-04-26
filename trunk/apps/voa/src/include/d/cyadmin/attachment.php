<?php
	/**
	 * 	voa_d_cyadmin_attachment
	 * 	附件上传
	 * */
	class voa_d_cyadmin_attachment extends voa_d_abstruct{
		/** 初始化 */
		public function __construct($cfg = null) {
		
			/** 表名 */
			$this->_table = 'orm_cyadmin.attachment';
			/** 允许的字段 */
			$this->_allowed_fields = array();
			/** 必须的字段 */
			$this->_required_fields = array();
			/** 主键 */
			$this->_pk = 'atid';
		
			parent::__construct(null);
		}
	}
