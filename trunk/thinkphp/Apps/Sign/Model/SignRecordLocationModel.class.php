<?php
/**
 * SignRecordLocationModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignRecordLocationModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据经纬度查询
	 * @param $lng 经度
	 * @param $lat 纬度
	 * @return array
	 */
	public function get_location($lng, $lat) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'longitude = ?',
			'latitude = ?'
		);
		$where_params = array(
			$lng,
			$lat
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	}
