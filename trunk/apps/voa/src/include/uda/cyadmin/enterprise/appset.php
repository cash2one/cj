<?php

/**
 * @Author: ppker
 * @Date:   2015-07-30 14:59:47
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-19 11:11:12
 */
class voa_uda_cyadmin_enterprise_appset extends voa_uda_cyadmin_enterprise_base {
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if( $this->__service == null ) {
			$this->__service = new voa_s_cyadmin_enterprise_appset ();
		}
	}

	public function update( $in, &$out ) {

		//var_dump($in);die;
		// 对数据进行处理
		$this->filter_data( $in );
		if( $in ) {
			foreach( $in as $_k => $_v ) {
				$v  = array( 'value' => $_v );
				$re = $this->__service->update( $_k, $v );
				if( ! $re ) {
					$this->errmsg = '保存数据出现失败！';

					return false;
				}
			}
			$out = $in;

			return true;
		}

		return false;
	}

	/**
	 * [list_all description]
	 * @return [type] [description]
	 */
	public function list_all() {
		return $this->__service->list_all();
	}

	public function list_by_conds($conds) {
		return $this->__service->list_by_conds($conds);
	}

	/**
	 * [filter_data 数据安全性处理]
	 *
	 * @param  [type] &$data [description]
	 *
	 * @return [type]        [description]
	 */
	public function filter_data( &$data ) {

		//var_dump($data);die;
		if( empty( $data['trydate'] ) ) {
			$this->errmsg( 10001, '试用期限不能为空' );

			return false;
		}

		if( ! is_numeric( $data['trydate'] ) || ( is_numeric( $data['trydate'] ) && $data['trydate'] < 0 ) ) {
			$this->errmsg( 10005, '试用期限 只能填写合法的数字' );

			return false;
		}

		if( ! is_numeric( $data['syq_jjdq_set'] ) || ( is_numeric( $data['syq_jjdq_set'] ) && $data['syq_jjdq_set'] < 0 ) ) {
			$this->errmsg( 10006, '试用期-即将到期 只能填写合法的数字' );

			return false;
		}

		if( ! is_numeric( $data['yff_jjdq_set'] ) || ( is_numeric( $data['yff_jjdq_set'] ) && $data['yff_jjdq_set'] < 0 ) ) {
			$this->errmsg( 10007, '已付费-即将到期 只能填写合法的数字' );

			return false;
		}

		// 判断设置的数据是否为数字


		if( ! empty( $data['notice'] ) ) {
			foreach( $data['notice']['agodate'] as $k => $v ) {
				if( ! is_numeric( $v ) || ( is_numeric( $v ) && $v < 0 ) ) {
					$this->errmsg( 10008, '应用设置的天数只能填写合法的数字！' );

					return false;
				}
			}


			// 期限前多少天
			$agodate = $data['notice']['agodate'];
			if( in_array( '', $agodate, true ) ) {
				$this->errmsg( 10003, '消息期限前多少天不可为空，请认真填写!' );

				return false;
			}

			// 消息模板
			$meid = $data['notice']['meid'];
			if( in_array( '', $meid ) ) {
				$this->errmsg( 10004, '消息模板必须选择，请认真选择!' );

				return false;
			}


			$data['notice'] = serialize( $data['notice'] );
		} else {
			$data['notice'] = '';
		}
	}
}
