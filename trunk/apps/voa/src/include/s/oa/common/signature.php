<?php
/**
 * Class voa_s_oa_common_signature
 * 新东方登录签名
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_s_oa_common_signature extends voa_s_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 签名数据入库
	 * @param $data 签名数据
	 * @param bool $return_insert_id 是否返回自增id
	 */
	public function insert($data, $return_insert_id = false) {

		try {
			return voa_d_oa_common_signature::insert($data);
		} catch (Exception $e) {
			logger::error($e);

			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据签名获取签名信息
	 * @param $code 签名
	 * @return bool
	 * @throws service_exception
	 */
	public function fetch_by_code($code) {

		try {
			return voa_d_oa_common_signature::fetch_by_code($code);
		} catch (Exception $e) {
			logger::error($e);

			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件更新签名信息
	 * @param $data 待更新签名信息
	 * @param $condition 更新条件
	 * @return mixed
	 * @throws service_exception
	 */
	public function update($data, $condition) {

		try {
			return voa_d_oa_common_signature::update($data, $condition);
		} catch (Exception $e) {
			logger::error($e);

			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件删除登录记录
	 * @param $conditions 删除条件
	 * @return mixed
	 * @throws service_exception
	 */
	public function delete_by_conditions($conditions) {

		try {
			return voa_d_oa_common_signature::delete_by_conditions($conditions);
		} catch (Exception $e) {
			logger::error($e);

			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
