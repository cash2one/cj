<?php
/**
 * @Author: ppker
 * @Date:   2015-10-19 18:10:53
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 14:13:43
 */

class voa_uda_cyadmin_enterprise_overdue extends voa_uda_cyadmin_base {

	
	/**
	 *
	 * 列表分页
	 *
	 * @param unknown_type $page
	 * @param unknown_type $over_list
	 * @param unknown_type $multi
	 * @param unknown_type $total
	 * @param unknown_type $uid
	 */
	public function getlist( $page = '', &$over_list, &$multi, &$total, $uid, $act) {
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_overdue' );
		//获取总页数
		$perpage = 10;

		// 权限判断
		if (in_array($act, array(0,1))) {

		
			$total   = $serv->count();

			//  获取当前用户已读的消息提醒
			$dueread = &service::factory('voa_s_cyadmin_enterprise_dueread');
			$dueread->get_dueread_data($uid, $read_data);

			if (!empty($read_data)) {
				$read_data = array_column($read_data, 'ovid');
				$read_data = array_unique($read_data); // 进行过滤去重
				$total -= count($read_data);	
			}

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
				$conds = array('ovid NOT IN (?)' => $read_data);
				$over_list = $serv->list_by_conds( $conds, $page_option, $orderby );

				// $over_list = $serv->list_all( $page_option, $orderby );
			}
		} elseif (2 == $act) {

			// 获取当前的epid 企业ids
			$epids = $this->list_epids($uid);

			//  获取当前用户已读的消息提醒
			$dueread = &service::factory('voa_s_cyadmin_enterprise_dueread');
			$dueread->get_dueread_data($uid, $read_data);

			if (!empty($read_data)) {
				$read_data = array_column($read_data, 'ovid');
				$read_data = array_unique($read_data); // 进行过滤去重
				// $total -= count($read_data);	
			}
			$conds = array('ovid NOT IN (?)' => $read_data, 'epid IN (?)' =>$epids );
			$total   = $serv->count_by_conds($conds); // 获取真正的总数
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
				
				$over_list = $serv->list_by_conds( $conds, $page_option, $orderby );

				// $over_list = $serv->list_all( $page_option, $orderby );
			}


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
	 * @param $list
	 * @param $out
	 */
	public function format( $list, &$out, $tao_data) {

		$epids = array_column($list, 'epid'); // 企业ID数组
		if (empty($epids)) {
			return true;
		}

		$serv_com = &service::factory( 'voa_s_cyadmin_enterprise_newprofile' );
		// 获取企业数据
		$com_list = $serv_com->list_by_conds(array('ep_id IN (?)' => $epids));
		$ep_list  = array();
		$appset = $this->_appset_status; // 到期的各种状态
		array_unshift($tao_data, '试用期无套件信息');
		foreach( $com_list as $_epid => $_val ) {
			$ep_list[] = $_epid;
		}

		foreach( $list as &$val ) {
			$val['_created'] = rgmdate( $val['created'], 'Y-m-d H:i' );
			
			//匹配公司名称
			if( in_array( $val['epid'], $ep_list ) ) {
				$val['_epid'] = $com_list[ $val['epid'] ]['ep_name'];
			}
			if (!empty($val['overdue_status'])) { // 匹配到期状态设置
				$val['overdue_status'] = $appset[$val['overdue_status']];
			}
			// suid
			if (isset($val['suid'])) {
				$val['suid'] = $tao_data[$val['suid']];
			}

		}

		$out = $list;
	}

	/**
	 * [list_epids 根据用户ca_id 获取epids]
	 * @param  [type] $uid [用户ca_id]
	 * @return [type]      [返回的数据]
	 */
	public function list_epids($uid) {
		
		$profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$cond = array('ca_id = ?' => $uid);
		$epids = $profile->list_by_conds($cond);
		if (!empty($epids)) {
			$epids = array_column($epids, 'ep_id');
		} else {
			$epids = array();
		}
		return $epids;
	}


}
