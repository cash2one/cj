<?php

/**
 * @Author: ppker
 * @Date:   2015-07-27 09:49:17
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-22 13:22:24
 */
class voa_uda_cyadmin_enterprise_msglist extends voa_uda_cyadmin_base {
	public $uid = null;

	public function message( $in, &$out ) {
		$this->uid = $in['uid'];

		$serv_message      = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		$condition['epid'] = $in['ep_id'];

		$list_ep     = $serv_message->list_by_conds( $condition );
		$list_common = $serv_message->list_by_conds( array( 'type' => 0 ) );
		if( $list_ep || $list_common ) {

			if( empty( $list_ep ) ) {
				$list = $list_common;
			} elseif( empty( $list_common ) ) {
				$list = $list_ep;
			} else {
				$list = array_merge( $list_ep, $list_common );
			}
			$data = array();
			$this->formdata( $list, $data );
		} else {
			$data = array();
		}
		$out = $data;

	}

	/**
	 *
	 * api详情获取
	 *
	 * @param $in
	 * @param $out
	 */
	public function getview( $in, &$out ) {
		$serv     = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		$serv_att = &service::factory( 'voa_s_cyadmin_attachment' );
		$conds    = array( 'meid' => $in['meid'] );
		$notice   = $serv->get_by_conds( $conds );
		if( $notice['atid'] ) {
			$notice['imgurl'] = config::get( 'voa.main_url' ) . 'attachment/read/' . $notice['atid'];
		}
		//$notice['_created'] = date( 'Y-m-d  H:i:s', $notice['created'] );
		// 作者统一为 畅移云工作
		$notice['author'] = '畅移云工作';
		$out              = $notice;

		return true;
	}


	/**
	 *
	 * api数据列表分页
	 *
	 * @param $page
	 * @param $uid
	 * @param $epid
	 * @param $message
	 * @param $all_count
	 * @param $multi
	 */
	public function list_page( $page, $uid, $epid, &$message, &$total, &$multi, $title = '' ) {
		//查询消息列表
		$serv_messagelog = &service::factory( 'voa_s_cyadmin_enterprise_message_log' );
		$serv_read       = &service::factory( 'voa_s_cyadmin_enterprise_message_read' ); //已读记录
		$settings        = voa_h_cache::get_instance()->get( 'setting', 'oa' );


		$read     = $serv_read->list_by_conds( array( 'uid' => $uid ) );
		$re_array = array();
		if( $read ) {
			$num      = count( $read );
			$re_array = array_column( $read, 'logid' );
		} else {
			$num = 0;
		}
		$totl_conds['epid IN (?)'] = array( $epid, 0 );

		$totl_conds['logid NOT IN (?)'] = $re_array;
		if( ! empty( $title ) ) {
			$totl_conds['title like ?'] = $title;
		}
		$total = $serv_messagelog->count_by_conds( $totl_conds ); // 真正的总数哇

		//$re_array = implode(',', $re_array);
		//var_dump($re_array);die;

		//每页显示条数
		$perpage = 10;

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
			$orderby['created'] = 'DESC';

			$message = $serv_messagelog->list_by_complex( array( array( $epid, 0 ), $re_array, $title ), $page_option );

		}

	}


	/**
	 * [list_old 已读消息查询]
	 *
	 * @param  [type] $page     [description]
	 * @param  [type] $uid      [description]
	 * @param  [type] $epid     [description]
	 * @param  [type] &$message [description]
	 * @param  [type] &$total   [description]
	 * @param  [type] &$multi   [description]
	 * @param  [type] $title    [标题查找]
	 *
	 * @return [type]           [description]
	 */
	public function list_old( $page, $uid, $epid, &$message, &$total, &$multi, $title = '' ) {
		//查询消息列表
		$serv_messagelog = &service::factory( 'voa_s_cyadmin_enterprise_message_log' );
		$serv_read       = &service::factory( 'voa_s_cyadmin_enterprise_message_read' ); //已读记录
		$settings        = voa_h_cache::get_instance()->get( 'setting', 'oa' );

		$orderby['created'] = 'DESC';
		$read               = $serv_read->list_all( null, $orderby );
		if( $read ) {
			$logids = array_column( $read, 'logid' );
		} else {
			$logids = array();
		}

		if( ! empty( $title ) && ! empty( $logids ) ) {
			$old_array_conds = array(
				'logid IN (?)' => $logids,
				'title like ?' => "%" . $title . "%"
			);
			$total           = $serv_messagelog->count_by_conds( $old_array_conds );
		} else {
			$total = $serv_read->count(); // 已读总数
		}


		//每页显示条数
		$perpage = 10;

		if( $total > 0 ) {
			$pagerOptions = array(
				'total_items'      => $total,
				'per_page'         => $perpage,
				'current_page'     => $page,
				'show_total_items' => true,
			);
			$multi        = pager::make_links( $pagerOptions );
			pager::resolve_options( $pagerOptions );

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;


			if( ! empty( $title ) ) { // 若标题有值，则进行标题的搜索

				if( $logids ) {
					// 进行分页查询
					$array_conds['logid IN (?)'] = $logids;
					$array_conds['title like ?'] = "%" . $title . "%";
					$message                     = $serv_messagelog->list_by_conds( $array_conds, $page_option, array( 'created' => 'DESC' ) );

					return true;
				}
			}

			$read = $serv_read->list_all( $page_option, $orderby );
			if( $read ) {
				$logids = array_column( $read, 'logid' );
				// 读取各主键的信息
				$message = $serv_messagelog->list_by_pks( $logids, array( 'created' => 'DESC' ) );
			} else {
				$message = array();
			}

		}
	}

	/**
	 *
	 * api数据格式化
	 *
	 * @param $in
	 * @param $out
	 */
	public function _formdata( $in, &$out ) {
		foreach( $in as &$_val ) {
			$_val['created'] = date( 'Y-m-d  H:i', $_val['created'] );
		}
		$out = $in;
	}

	/**
	 *
	 * api获取未读消息数
	 *
	 * @param $uid
	 * @param $epid
	 * @param $msg_count
	 */
	public function msg_count( $uid, $epid, &$msg_count ) {
		//查询未读信息
		$serv_message = &service::factory( 'voa_s_cyadmin_enterprise_message' );
		$serv_read    = &service::factory( 'voa_s_cyadmin_enterprise_read' );
		$settings     = voa_h_cache::get_instance()->get( 'setting', 'oa' );
		$serv_cem     = new voa_s_cyadmin_enterprise_message();
		$message      = $serv_cem->list_by_complex( array( 0, $epid ) );
		$all_count    = $serv_cem->count_by_complex( array( 0, $epid ) );

		$conds_read['uid'] = $uid;

		$read      = $serv_read->list_by_conds( $conds_read );
		$msg_count = 0;
		if( ! empty( $message ) && ! empty( $read ) ) {
			foreach( $message as $_message ) {
				foreach( $read as $_read ) {

					if( $_message['meid'] == $_read['meid'] && $_read['uid'] == $uid ) {
						$msg_count = $msg_count + 1;
					}
				}
			}
			$msg_count = $all_count - $msg_count;
		} elseif( ! empty( $message ) && empty( $read ) ) {
			$msg_count = $all_count;
		} else {
			$msg_count = 0;
		}
	}

	/**
	 * api获取公司付款状态方法
	 *
	 * @param $epid
	 * @param $paystatus
	 */
	public function get_paystatus( $epid, &$paystatus ) {
		$serv_profile = &service::factory( 'voa_s_cyadmin_enterprise_profile' );

		$info_com  = $serv_profile->fetch( $epid );
		$paystatus = ( $info_com['ep_paystatus'] == 0 ) ? '未付款' : '已付款';
	}


}
