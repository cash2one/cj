<?php
/**
 * @Author: ppker
 * @Date:   2015-09-24 14:50:01
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-24 16:47:35
 */

namespace Sign\Service;

class FormatService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}


	/**
	 * [make_data 格式化返回的数据]
	 * @param  [array] $data [传入的数据]
	 * @return [array]       [格式化后返回的数据]
	 */
	public function make_data($data) {

		// 返回数据
		return array(
			'sl_address' => $data['sl_address'],
			'sl_signtime' => $data['sl_signtime'],
			'attachs' => $data['attachs'],
			'sl_id' => $data['sl_id'],
			'sl_note' => $data['sl_note']
			/*'m_username' => $data['m_username']*/

		);
	}


}
