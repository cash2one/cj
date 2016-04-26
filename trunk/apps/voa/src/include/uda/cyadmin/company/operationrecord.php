<?php
/**
 * @Author: ppker
 * @Date:   2015-10-19 18:10:53
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 23:04:10
 */

class voa_uda_cyadmin_company_operationrecord extends voa_uda_cyadmin_base {

	
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
	public function getlist( $page = '', &$record_list, &$multi, &$total, $uid) {

		$serv = &service::factory('voa_s_cyadmin_company_operationrecord');
		//获取总页数
		$perpage = 10;

		$sql = "(ca_id_h=? OR ca_id_q=?) AND status < ?";

		$total = $serv->count_by_complex($sql, array($uid, $uid, 3), 'op_id');

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
			$record_list = $serv->list_by_complex($sql, array($uid, $uid, 3), $page_option, $orderby);

		}

	}


	/**
	 * [format 对数据进行过滤]
	 * @param  [type] $list [传递的数据]
	 * @param  [type] &$out [过滤的数据]
	 * @return [type]       [description]
	 */
	public function format($list, &$out, $adminer) {

		$serv_com = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		$com_list = $com_list = $serv_com->fetch_all();
		
		$company_data = array_column($com_list, 'ep_name', 'ep_id');
		$adminer_name = array_column($adminer, 'ca_realname', 'ca_id');

		foreach( $list as &$val ) {
			$val['created'] = rgmdate( $val['created'], 'Y-m-d H:i' );
			$val['ep_name'] = $company_data[$val['ep_id']]; // 公司名称
			$val['front_man'] = $adminer_name[$val['ca_id_q']];
			$val['back_man'] = $adminer_name[$val['ca_id_h']];
		}

		$out = $list;
	}
	
}
