<?php

/**
 *
 * 入库
 * @author Burce
 *
 */
class voa_uda_cyadmin_enterprise_news extends voa_uda_cyadmin_base {


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

		// 入message库
		$serv           = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		$data['type']   = 0;
		$data['epid']   = 0;
		$data['author'] = $in['author'];

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

		//获取数据
		if( ! empty( $in ) ) {
			if( empty( $in['title'] ) ) {
				$this->errmsg( 9007, '标题不能为空' );

				return false;
			}
			if( empty( $in['content'] ) ) {
				$this->errmsg( 9006, '内容不能为空' );

				return false;
			}
			// 作者

			$data['content'] = $in['content'];
			$data['title']   = $in['title'];
		} else {
			return false;
		}

		$fields = array(
			'content' => array( 'content', parent::VAR_STR, null, null, false ),
			'title'   => array( 'title', parent::VAR_STR, null, null, false ),


		);

		// 检查过滤，参数
		if( ! $this->extract_field( $this->__request, $fields, $data ) ) {
			return false;
		}
		if( ! empty( $in['atid'] ) ) {
			$data['atid'] = $in['atid'];
		}


		$out = $data;

		return true;
	}

	/**
	 *
	 * 列表分页
	 *
	 * @param unknown_type $page
	 * @param unknown_type $msg_list
	 * @param unknown_type $multi
	 * @param unknown_type $total
	 */
	public function getlist( $page = '', &$msg_list, &$multi, &$total ) {
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		//获取总页数
		$perpage = 10;
		$total   = $serv->count();
		if( $total > 0 ) {
			$pagerOptions = array(
				'total_items'      => $total,
				'per_page'         => $perpage,
				'current_page'     => $page,
				'show_total_items' => true,
			);
			$multi        = pager::make_links( $pagerOptions );
			pager::resolve_options( $pagerOptions );

			$page_option[0]     = $pagerOptions['start'];
			$page_option[1]     = $perpage;
			$orderby['updated'] = 'DESC';

			$msg_list = $serv->list_all( $page_option, $orderby );
		}

	}

	/**
	 *
	 * 搜索列表分页
	 *
	 * @param unknown_type $page
	 * @param unknown_type $msg_list
	 * @param unknown_type $multi
	 * @param unknown_type $total
	 */
	public function getlist_search( $page = '', &$msg_list, &$multi, &$total, $search = '' ) {
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		//获取总页数
		$perpage = 10;
		$conds   = array( 'title like ?' => "%" . $search . "%" ); //搜索条件
		$total   = $serv->count_by_conds( $conds );
		if( $total > 0 ) {
			$pagerOptions = array(
				'total_items'      => $total,
				'per_page'         => $perpage,
				'current_page'     => $page,
				'show_total_items' => true,
			);
			$multi        = pager::make_links( $pagerOptions );
			pager::resolve_options( $pagerOptions );

			$page_option[0]     = $pagerOptions['start'];
			$page_option[1]     = $perpage;
			$orderby['updated'] = 'DESC';

			$msg_list = $serv->list_by_conds( $conds, $page_option, $orderby );
		}

	}


	/**
	 *
	 * 列表数据格式
	 *
	 * @param $list
	 * @param $out
	 */
	public function format( $list, &$out ) {

		// 查找相关的 企业数据
		$serv_com = &service::factory( 'voa_s_cyadmin_enterprise_newprofile' );
		$epids = array_unique(array_column($list, 'epid'));
		$conds = array('ep_id IN (?)' => $epids);
		$ep_list = $serv_com->list_by_conds($conds);

		if (!empty($ep_list)) {
			// 匹配 企业名称
			foreach( $list as &$val ) {
				//匹配公司名称
				if( in_array( $val['epid'], $ep_list ) ) {
					$val['_epid'] = $ep_list[ $val['epid'] ]['ep_name'];
				}
			}
		}

		// 格式化
		foreach ($list as &$val) {
			$val['_created'] = rgmdate( $val['created'], 'Y-m-d H:i' );
			// 标题的html标签过滤
			$val['title'] = rhtmlspecialchars( $val['title'] );
		}

		$out = $list;
	}

	/**
	 *
	 * 获取详情
	 *
	 * @param unknown_type $meid
	 * @param unknown_type $info
	 */
	public function getview( $meid, &$info ) {
		if( empty( $meid ) ) {
			$this->errmsg = '获取详情失败';

			return false;
		}

		$serv_message = &Service::factory( 'voa_s_cyadmin_enterprise_message' );
		$serv_me      = $serv_message->get( $meid );
		if( $serv_me ) {
			$info = $serv_me;
		} else {
			$this->errmsg = '没有获取到该数据';

			return false;
		}

		if( ! empty( $info['atid'] ) ) {
			$info['imgurl'] = config::get( 'voa.main_url' ) . 'attachment/read/' . $info['atid'];
		}
	}

	/**
	 * [list_by_pks description]
	 *
	 * @param  [type] $medis [description]
	 *
	 * @return [type]        [description]
	 */
	public function list_by_pks( $medis ) {
		$serv_message = &Service::factory( 'voa_s_cyadmin_enterprise_message' );
		$re           = $serv_message->list_by_pks( $medis );
		if( ! $re ) {
			$this->errmsg = '数据出现错误，请联系管理员!';

			return false;
		}

		return $re;
	}


	/**
	 * [list_all description]
	 * @return [type] [description]
	 */
	public function list_all() {
		$serv_message = &Service::factory( 'voa_s_cyadmin_enterprise_message' );
		$re           = $serv_message->list_all( $page_option = null, $orderby = array() );
		if( ! $re ) {
			$this->errmsg = '数据出现错误，请联系管理员!';

			return false;
		}

		return $re;
	}


}
