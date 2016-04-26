<?php

/**
 * @Author: ppker
 * @Date:   2015-08-04 15:24:12
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-26 11:53:33
 */
class voa_uda_cyadmin_enterprise_message_log extends voa_uda_cyadmin_enterprise_base {

	public function insert_multi( $data ) {
		$re = $this->serv_enterprise_message_log->insert_multi( $data );
		if( ! $re ) {
			$this->errmsg = '发送消息失败!';

			return false;
		}

		return $re;
	}

	/**
	 * [list_mesage 查找消息]
	 *
	 * @param  [type] &$result [返回结果集]
	 * @param  [type] $conds   [条件]
	 * @param  [type] $pager   [分页参数]
	 *
	 * @return [type]          [description]
	 */
	public function list_mesage( &$result, $conds, $pager ) {
		$ocnds           = array_merge( $conds, array(
			'epid'
		) );
		$result['list']  = $this->_list_news_by_conds( $conds, $pager );
		$result['total'] = $this->_count_news_by_conds( $conds );

		return true;
	}

	/**
	 * [_list_news_by_conds 条件查询]
	 *
	 * @param  [type] $conds [description]
	 * @param  [type] $pager [description]
	 *
	 * @return [type]        [description]
	 */
	public function _list_news_by_conds( $conds, $pager ) {
		$list = array();
		$list = $this->serv_enterprise_messagelog->list_by_conds( $conds, $pager, array( 'updated' => 'DESC' ) );

		//$this->__format_list($list);

		return $list;
	}

	/**
	 * [_count_news_by_conds 查询总数]
	 *
	 * @param  [type] $conds [description]
	 *
	 * @return [type]        [description]
	 */
	public function _count_news_by_conds( $conds ) {
		$total = $this->serv_enterprise_messagelog->count_by_conds( $conds );

		return $total;
	}


	/**
	 * [get_messagelog_time 获取发送消息的时间]
	 *
	 * @param  [type] $logid [主键id]
	 *
	 * @return [type]        [description]
	 */
	public function get_messagelog_time( $logid ) {
		$time = array();
		if( $logid ) {
			$data = $this->serv_enterprise_message_log->get( $logid );
			if( $data['created'] ) {
				$time = $data['created'];
			}
		}

		return $time;
	}
}
