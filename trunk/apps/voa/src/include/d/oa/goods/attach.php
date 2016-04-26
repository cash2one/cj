<?php
/**
 * voa_d_oa_goods_attach
 * 附件信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_goods_attach extends voa_d_abstruct {
	// 封面图片
	const AT_TYPE_COVER = 1;
	// 幻灯片图片
	const AT_TYPE_SLIDE = 2;
	// 详情图片
	const AT_TYPE_PIC = 3;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.goods_attach';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'gaid';

		parent::__construct(null);
	}

	/**
	 * 根据条件读取封面图片
	 * @param array $conds 搜索条件
	 * @param string $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function list_cover($conds, $page_option = null, $orderby = array()) {

		try {
			// 解析条件
			$this->_parse_conds($conds);
			// 查询条件
			$this->_condi('attype=?', self::AT_TYPE_COVER);
			// 只查询未删除的
			$this->_condi('status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取幻灯片图片
	 * @param array $conds 搜索条件
	 * @param string $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function list_slide($conds, $page_option = null, $orderby = array()) {

		try {
			// 解析条件
			$this->_parse_conds($conds);
			// 查询条件
			$this->_condi('attype=?', self::AT_TYPE_SLIDE);
			// 只查询未删除的
			$this->_condi('status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取详情中的图片
	 * @param array $conds 搜索条件
	 * @param string $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function list_pic($conds, $page_option = null, $orderby = array()) {

		try {
			// 解析条件
			$this->_parse_conds($conds);
			// 查询条件
			$this->_condi('attype=?', self::AT_TYPE_PIC);
			// 只查询未删除的
			$this->_condi('status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}

