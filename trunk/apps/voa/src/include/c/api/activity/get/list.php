<?php

/**
 * voa_c_api_activity_get_list
 * 活动列表
 * $Author$
 * $Id$
 */
class voa_c_api_activity_get_list extends voa_c_api_activity_base {

	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 审批表 */
	protected $uda_get;
	/** 最后更新时间 */
	protected $_updated = 0;

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'page'   => array( 'type' => 'int', 'required' => false ),
			// 每页显示数据数
			'limit'  => array( 'type' => 'int', 'required' => false ),
			// 读取的列表类型
			'action' => array( 'type' => 'string', 'required' => false )
		);
		if ( ! $this->_check_params( $fields ) ) {
			// 检查参数
			return false;
		}

		if ( $this->_params['page'] < 1 ) {
			// 设定当前页码的默认值
			$this->_params['page'] = 1;
		}

		if ( $this->_params['limit'] < 20 ) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 20;
		}

		if ( empty( $this->_params['action'] ) ) {
			// 设置当前的动作
			$this->_params['action'] = 'mine';
		}

		// 获取分页参数
		list( $this->_start, $this->_params['limit'], $this->_params['page'] ) = voa_h_func::get_limit( $this->_params['page'], $this->_params['limit'], 100 );

		// 更新时间
		$this->_updated = startup_env::get( 'timestamp' );

		// list获取
		$this->uda_get     = &uda::factory( 'voa_uda_frontend_activity_get' );
		$reslut            = array();
		$request['action'] = $this->_params['action'];
		$request['start']  = $this->_start;
		$request['limit']  = $this->_params['limit'];
		$this->uda_get->doit( $request, $reslut );
		$this->_list = $reslut;

		//整理数据
		$data = array();
		$this->uda_get->listformat( $this->_list, $data );

		// 输出结果
		$this->_result = array(
			'limit' => $this->_params['limit'],
			'page'  => $this->_params['page'],
			'data'  => $data
		);

		return true;
	}
}
