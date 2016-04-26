<?php

/**
 * voa_uda_cyadmin_enterprise_profile
 * uda/畅移后台/企业/信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_cyadmin_enterprise_profile extends voa_uda_cyadmin_enterprise_base {

	public $profile_list = array();

	public function __construct() {
		parent::__construct();
	}

	public function post_corp_to_oa( $domain = '', $corp = array() ) {
		$oa_result = array();
		// 使用企业OA站接口来完成通知
		if( $this->qyoa_api( $domain, 'enterprise', 'update_corp', $corp, $oa_result ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取指定企业ID的企业信息
	 *
	 * @param number $ep_id
	 * @param array $enterprise_profile <strong style="color:red">(引用结果)</strong>企业信息
	 *
	 * @return boolean
	 */
	public function get_by_id( $ep_id = 0, &$enterprise_profile = array() ) {
		$ep_id              = (int) $ep_id;
		$enterprise_profile = $this->serv_enterprise_profile->fetch( $ep_id );
		if( empty( $enterprise_profile ) ) {
			return false;
		}

		return true;
	}

	/**
	 * 列出所有指定的企业ID的信息
	 *
	 * @param array $ep_ids
	 * @param array $list <strong>(引用结果)</strong> 企业信息列表
	 *
	 * @return boolean
	 */
	public function list_by_ep_ids( $ep_ids, &$list ) {

		if( is_numeric( $ep_ids ) ) {
			$ep_ids = array( $ep_ids );
		}

		// 检查未读取过的ID
		$new_ep_ids = array();
		foreach( $ep_ids as $id ) {
			if( ! isset( $this->profile_list[ $id ] ) ) {
				$new_ep_ids[] = $id;
			}
		}

		$new_list = array();
		if( ! empty( $new_ep_ids ) ) {
			// 存在新ID
			$new_list = $this->serv_enterprise_profile->fetch_all_by_ids( $new_ep_ids );
		}

		// 与历史记录合并
		$this->profile_list = array_merge( $this->profile_list, $new_list );

		$list = $this->profile_list;

		return true;
	}

	/**
	 * 为一组包含企业ID信息的列表注入对应企业域名数据（键名为：_domain）
	 *
	 * @param array $list <strong style="color:red">引用结果</strong>
	 *
	 * @return boolean
	 */
	public function data_append_domain( &$list ) {

		$ep_ids = array();
		// 找到企业ID
		foreach( $list as $d ) {
			if( ! isset( $d['ep_id'] ) ) {
				continue;
			}
			if( ! isset( $ep_ids[ $d['ep_id'] ] ) ) {
				$ep_ids[ $d['ep_id'] ] = $d['ep_id'];
			}
		}

		// 找到指定的企业列表
		$ep_lists = array();
		$this->list_by_ep_ids( $ep_ids, $ep_lists );

		// 注入域名信息
		$scheme = config::get( 'voa.oa_http_scheme' );
		foreach( $list as &$data ) {
			if( ! isset( $data['ep_id'] ) || ! $ep_lists[ $data['ep_id'] ] ) {
				$data['_domain'] = '';
			}
			$data['_domain'] = $ep_lists[ $data['ep_id'] ]['ep_domain'];
			if( isset( $data['rb_pictureurl'] ) ) {
				$data['_pictureurl'] = $scheme . $data['_domain'] . $data['rb_pictureurl'];
			}
			if( isset( $data['rnc_pictureurl'] ) ) {
				$data['_pictureurl'] = $scheme . $data['_domain'] . $data['rnc_pictureurl'];
			}
		}

		return true;
	}

	/**
	 * 更新企业资料
	 *
	 * @param number $ep_id
	 * @param array $update 待更新的数据
	 *
	 * @return boolean
	 */
	public function update_profile( $ep_id, $update ) {

		if( empty( $ep_id ) ) {
			return false;
		}

		if( empty( $update ) ) {
			return false;
		}

		$this->serv_enterprise_profile->update( $update, $ep_id );

		return true;
	}

	/**
	 * [list_by_conds 根据条件获取数据]
	 *
	 * @param  [type] $conds [description]
	 *
	 * @return [type]        [description]
	 */
	public function list_by_conds( $conds ) {
		$data = $this->serv_enterprise_profile->fetch_by_conditions( $conds );
		if( ! $data ) {
			return false;
		}

		return $data;
	}

	/**
	 * 增加最后操作时间
	 * @param array $ep_ids 企业ID
	 * @return bool
	 */
	public function add_last_operation($ep_ids) {

		$serv_ep = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$timestamp = startup_env::get('timestamp');

		$serv_ep->update_by_conds(array('ep_id IN (?)' => $ep_ids), array('ep_last_operation' => $timestamp));

		return true;
	}

}
