<?php
/**
 * voa_uda_frontend_addressbook_get
 * 统一数据访问/通讯录/获取数据
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_addressbook_get extends voa_uda_frontend_addressbook_base {
	/** 允许分享的字段信息 */
	protected $_allow_share_fields = array(
		'm_face'   => 'face',
		'm_username' => 'realname',
		'm_mobilephone' => 'mobilephone',
		'cj_id' => 'jobid',
		'cd_id' => 'departmentid'
	);



	public function __construct() {
		parent::__construct();
	}

	/**
	 * 分享用户通讯录操作
	 * @param string $url 分享url
	 * @param int $uid
	 * @param string $fieldstr 字段信息
	 */
	public function share(&$url, $uid, $fieldstr) {

		$fields = explode(',', $fieldstr);
		/** 过滤 */
		foreach ($fields as $_k => $_f) {
			$_f = trim($_f);
			if (!empty($_f) && in_array($_f, $this->_allow_share_fields)) {
				continue;
			}

			unset($fields[$_k]);
		}

		/** 读取用户 */
		$serv_m = &service::factory('voa_s_oa_member');
		$user = $serv_m->fetch_by_uid($uid);
		if (empty($user)) {
			$this->errmsg(150, 'member_is_not_exist');
			return false;
		}

		/** 入库 */
		$serv_msh = &service::factory('voa_s_oa_member_share');
		try {
			$serv_m->begin();

			$share = array(
				'm_uid' => $uid,
				'msh_fields' => implode(',', $fields)
			);
			$msh_id = $serv_msh->insert($share, true);

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(152, 'addressbook_share_new_failed');
			return false;
		}

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$params = array(
			'ts' => startup_env::get('timestamp'),
			'sig' => voa_h_func::sig_create($msh_id)
		);
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.$sets['domain'].'/frontend/addressbook/share/id/'.$msh_id.'?'.http_build_query($params);

		return true;
	}

	/**
	 * 获取指定通讯录cab_id的数据，如果不存在则返回通讯录字段默认值
	 * @param number $cab_id
	 * @param array $addressbook<strong style="color:red">(引用返回)</strong>通讯录数据
	 * @return boolean
	 */
	public function addressbook($cab_id, & $addressbook) {
		$cab_id = rintval($cab_id, false);
		if ($cab_id > 0) {
			$addressbook = $this->serv->fetch($cab_id);
		}

		if (empty($addressbook)) {
			$addressbook = $this->serv->fetch_all_field();
		}

		// 格式化通讯录字段
		//$uda_format = &uda::factory('voa_uda_frontend_addressbook_format');
		//$uda_format->format($addressbook);

		return true;
	}

	/**
	 * 获取上传的通讯录excel文件绝对路径
	 * @param string $file_var 上传控件名
	 * @param string $filepath <strong style="color:red">引用结果</strong>绝对路径
	 * @return boolean
	 */
	public function get_uploadfile($file_var, &$filepath){
		$upload = isset($_FILES[$file_var]) ? $_FILES[$file_var] : array();
		if (empty($upload) || !isset($upload['error'])) {
			$this->errmsg(1001, '对不起，请正确上传通讯录  Excel 文件。');
			return false;
		}
		$errMsg = '';
		switch ($upload['error']) {
			case 0:
				$errMsg = false;
				break;
			case 1:
			case 2:
				$upload_max_filesize = @ini_get('upload_max_filesize');
				if (!$upload_max_filesize) {
					$upload_max_filesize = 1048576*2;
				}
				$upload_max_filesize = size_count($upload_max_filesize);
				$errMsg = '您只能上传大小不超过 '.$upload_max_filesize.' 的通讯录 Excel 文件';
				break;
			case 3:
				$errMsg = '通讯录文件上传失败，请返回重试。';
				break;
			case 4:
				$errMsg = '请上传通讯录 Excel 文件。';
				break;
			case 6:
				$errMsg = '服务器临时目录错误，上传失败。';
				break;
			case 7:
				$errMsg = '服务器文件写入错误，上传失败。';
				break;
			default:
				$errMsg = '上传通讯录文件发生未知错误。';
				break;
		}
		if ($errMsg) {
			$this->errmsg(1002, $errMsg);
			return false;
		}
		if (!is_readable($upload['tmp_name'])) {
			$this->errmsg(1003, '上传的通讯录文件读取失败，请返回重试。');
			return false;
		}
		$filepath = $upload['tmp_name'];

		return true;
	}

	/**
	 * 解析Excel文件并以数组输出
	 * @param string $filepath
	 * @param array $result <strong style="color:red">引用结果</strong>
	 * @return boolean
	 */
	public function parse_excel_data($filepath, &$result){
		if (!is_readable($filepath)) {
			$this->errmsg(1001, '上传的通讯录文件读取失败，请返回重试。');
			return false;
		}

		$data = file_get_contents($filepath, FALSE, NULL, 0, 8);
		if ($data != pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1)) {
			$this->errmsg(1002, '上传的文件不是标准的通讯录模板格式，请使用下载的模板');
			return false;
		}

		$error_reporting = error_reporting();
		error_reporting(0);
		$excel = new excel();
		$dataList = $excel->read_from_xsl($filepath, 0, array());
		error_reporting($error_reporting);

		if (empty($dataList)) {
			$this->errmsg(1003, '没有读取到可用的通讯录数据，请确认上传的 Excel 文件正确');
			return false;
		}

		/** 字段中文名与表字段名对应关系 */
		$name2field = array();
		foreach ($this->excel_fields as $k => $arr) {
			$name2field[rstrtolower($arr['name'])] = $k;
		}

		/** 自数据第一行（标题栏）获取标记与字段名之间对应关系 */
		$col2field = array();
		foreach ($dataList[0] as $colNum => $colName) {
			$colName = rstrtolower($colName);
			if (isset($name2field[$colName]) && strpos($name2field[$colName],'#') === false) {
				$col2field[$colNum] = $name2field[$colName];
			}
		}
		if (isset($dataList[0])) {
			unset($dataList[0]);
		}
		$result = array($col2field, $dataList);

		return true;
	}



}
