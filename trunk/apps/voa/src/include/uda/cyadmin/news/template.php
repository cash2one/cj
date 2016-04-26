<?php

/**
 * voa_uda_cyadmin_news_template
 * uda/畅移后台/企业/信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_cyadmin_news_template extends voa_uda_cyadmin_news_base {
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if( $this->__service == null ) {
			$this->__service = new voa_s_cyadmin_news_template ();
		}
	}

	/**
	 * 获取指定的公告
	 *
	 * @param number $ep_id
	 * @param array $result
	 *
	 * @return boolean
	 */
	public function get_by_id( $request = array(), &$result = array() ) {
		$result = array();
		$res    = $this->__service->get( $request ['ne_id'] );
		$this->__templateformt( $res, $result );
		if( empty ( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * 列出所有的公告
	 *
	 * @param array $reques 分页参数 array => limit $page_option[0], $page_option[1]
	 * @param array $result
	 *
	 * @return boolean
	 */
	public function list_all( $request = array(), &$result = array() ) {
		$data = array();
		if( $request ) {
			$page  = ( $request ['page'] - 1 ) * $request ['limit'];
			$limit = $request ['limit'];
			$data  = array(
				$page,
				$limit
			);
		}
		$result = $this->__service->list_all( $data );

		return true;
	}

	/**
	 * 获取分页列表
	 *
	 * @param $page
	 * @param $list
	 * @param $multi
	 * @param $total
	 */
	public function getlist( $page, &$list, &$multi, &$total ) {
		if( empty ( $page ) ) {
			$page = 1;
		}
		$perpage = 10;
		$serv    = &service::factory( 'voa_s_cyadmin_news_template' );
		$total   = $serv->count();

		if( $total > 0 ) {
			$pagerOptions = array(
				'total_items'      => $total,
				'per_page'         => $perpage,
				'current_page'     => $page,
				'show_total_items' => true
			);
			$multi        = pager::make_links( $pagerOptions );
			pager::resolve_options( $pagerOptions );

			$page_option [0]     = $pagerOptions ['start'];
			$page_option [1]     = $perpage;
			$orderby ['updated'] = 'DESC';

			$list = $serv->list_all( $page_option, $orderby );

			$list = $this->_format( $list );
		}
	}

	/**
	 *
	 * 格式化数据
	 *
	 * @param $list
	 */
	public function _format( $list ) {
		if( ! empty ( $list ) ) {
			foreach( $list as &$val ) {
				$val ['_created'] = date( 'Y-m-d', $val ['created'] );
			}
		}

		return $list;
	}

	public function getview( $neid, &$data ) {
		if( empty ( $neid ) ) {
			$this->errmsg( 10008, '获取详情失败' );

			return false;
		}

		$serv     = &service::factory( 'voa_s_cyadmin_news_template' );
		$data     = $serv->get( $neid );
		$list [0] = $data;
		$data     = $this->_format( $list );
		$data     = $data [0];
	}

	/**
	 * 入库操作
	 *
	 * @param $in
	 * @param $out
	 *
	 * @return bool
	 */
	public function add( $in, &$out ) {

		// 提交的值进行过滤
		$data = array();
		if( ! $this->getact( $in, $data ) ) {
			return false;
		}
		// 查图片路径及名称
		$data ['cover_url'] = config::get( 'voa.main_url' ) . 'attachment/read/' . $data ['atid'];
		unset ( $data ['atid'] );
		// 入message库
		$serv = &service::factory( 'voa_s_cyadmin_news_template' );
		$data = $serv->insert( $data );

		if( ! $data ) {
			return false;
		}
		$out = $data;

		return true;
	}

	/**
	 * 处理提交的数据
	 *
	 * @param $in
	 * @param $out
	 *
	 * @return bool
	 */
	public function getact( $in, &$out ) {
		// 获取数据
		if( ! empty ( $in ) ) {
			if( empty ( $in ['title'] ) ) {
				$this->errmsg( 9007, '标题不能为空' );

				return false;
			}
			if( empty ( $in ['content'] ) ) {
				$this->errmsg( 9006, '内容不能为空' );

				return false;
			}
			if( empty ( $in ['ne_id'] ) ) {
				if( empty ( $in ['atid'] ) ) {
					$this->errmsg( 9002, '封面图片不能为空' );

					return false;
				}
			}
			if( empty ( $in ['summary'] ) ) {
				$this->errmsg( 9000, '摘要不能为空' );

				return false;
			}
			if( ! empty ( $in ['ne_id'] ) ) {
				$data ['ne_id'] = $in ['ne_id'];
			}
			$data ['content'] = $in ['content'];
			$data ['title']   = $in ['title'];
			$data ['summary'] = $in ['summary'];
			$data ['icon']    = $in ['icon'];
		} else {
			return false;
		}

		$fields = array(
			'content' => array(
				'content',
				parent::VAR_STR,
				null,
				null,
				false
			),
			'title'   => array(
				'title',
				parent::VAR_STR,
				null,
				null,
				false
			),
			'summary' => array(
				'summary',
				parent::VAR_STR,
				null,
				null,
				false
			),
			'icon'    => array(
				'icon',
				parent::VAR_STR,
				null,
				null,
				false
			)
		);

		// 检查过滤，参数
		if( ! $this->extract_field( $this->__request, $fields, $data ) ) {
			return false;
		}
		if( ! empty ( $in ['atid'] ) ) {
			$data ['atid'] = $in ['atid'];
		}

		$out = $data;

		return true;
	}

	/**
	 *
	 * 列表数据格式
	 *
	 * @param $list
	 * @param $out
	 */
	public function format( $list, &$out ) {
		$serv_com = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		$com_list = $com_list = $serv_com->fetch_all();
		$ep_list  = array();
		foreach( $com_list as $_epid => $_val ) {
			$ep_list [] = $_epid;
		}

		foreach( $list as &$val ) {
			$val ['_created'] = date( 'Y-m-d H:i', $val ['created'] );
			// 匹配公司名称
			if( in_array( $val ['epid'], $ep_list ) ) {
				$val ['_epid'] = $com_list [ $val ['epid'] ] ['ep_name'];
			}
		}

		$out = $list;
	}

	public function edit( $in, &$out ) {
		// 提交的值进行过滤
		$data = array();
		if( ! $this->getact( $in, $data ) ) {
			return false;
		}

		// 查图片路径及名称
		if( ! empty ( $in ['atid'] ) ) {
			$data ['cover_url'] = config::get( 'voa.main_url' ) . 'attachment/read/' . $data ['atid'];
			unset ( $data ['atid'] );
		}
		// 入message库
		$serv = &service::factory( 'voa_s_cyadmin_news_template' );
		$data = $serv->update( $data ['ne_id'], $data );

		if( ! $data ) {
			return false;
		}
		$out = $data;

		return true;
	}

	private function __templateformt( $request, &$result ) {
		$request['content'] = str_replace( '&nbsp;', ' ', $request['content'] );

		$request['content'] = strip_tags( $request['content'] );
		$result             = $request;

		return true;
	}
}
