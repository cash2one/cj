<?php
/**
 * 数据服务层基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_s_abstract extends service {
	// dao class name
	protected $_d_classname = '';

	public function __construct() {

		// 获取 service class name
		if (empty($this->_s_classname)) {
			$this->_s_classname = get_class($this);
		}

		// 如果 dao 类名为空
		if (empty($this->_d_classname)) {
			$this->_d_classname = str_replace('_s_', '_d_', $this->_s_classname);
		}

		// 如果类存在
		if (class_exists($this->_d_classname)) {
			$this->add_extension($this->_d_classname);
		}
	}

	/**
	 * begin 开始一个事务
	 *
	 * @return void
	 */
	public function begin($table = '') {

		$this->__call('beginTransaction', func_get_args());
	}

	/**
	 * commit 提交
	 *
	 * @return void
	 */
	public function commit($table = '') {

		$this->__call('commit', func_get_args());
	}

	/**
	 * rollback 回滚
	 *
	 * @return void
	 */
	public function rollback($table = '') {

		$this->__call('rollBack', func_get_args());
	}

}
