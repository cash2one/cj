<?php
/**
 * voa_c_admincp_manage_member_base
 * 员工操作基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_base extends voa_c_admincp_manage_base{

	/** voa_uda_frontend_member_get
	protected $_uda_member_get = null;
	protected $_uda_member_format = null;
	protected $_uda_member_delete = null;
	protected $_uda_member_insert = null; */

    protected $_settings = null;

    protected $_departments = null;
	/**
	 * excel 的列名定义，注意排序顺序
	 * @var array
     * deprecated

	public $_excel_fields = array(
		'#' => array('name'=>'#', 'width'=>'5',),
		'm_username' => array('name'=>'姓名*', 'width'=>16,),
		'cd_name' => array('name'=>'部门*', 'width'=>18,),
		'm_mobilephone' => array('name'=>'手机号码*', 'width'=>14,),
		'm_email' => array('name'=>'邮箱*', 'width'=>30,),
		'mf_qq' => array('name'=>'QQ', 'width'=>14,),
		'mf_weixinid' => array('name'=>'微信号', 'width'=>14,),
		'm_number' => array('name'=>'工号', 'width'=>12,),
		'm_active' => array('name'=>'在职状态', 'width'=>11,),
		'cj_name' => array('name'=>'职位', 'width'=>16,),
		'mf_idcard' => array('name'=>'身份证号码', 'width'=>20,),
		'm_gender' => array('name'=>'性别', 'width'=>8,),
		'mf_telephone' => array('name'=>'电话号码', 'width'=>14,),
		'mf_birthday' => array('name'=>'生日', 'width'=>11,),
		'mf_address' => array('name'=>'住址', 'width'=>30,),
		'mf_remark' => array('name'=>'备注', 'width'=>100,),
	);*/

	/** 性别描述 deprecated
	public $gender_list = array(
		voa_d_oa_member::GENDER_UNKNOWN => '未登记',
		voa_d_oa_member::GENDER_MALE => '男',
		voa_d_oa_member::GENDER_FEMALE => '女'
	);*/

	/** 在职状态描述 deprecated
	public $active_list = array(
		voa_d_oa_member::ACTIVE_YES => '在职',
		voa_d_oa_member::ACTIVE_NO => '离职',
	);*/

	protected function _before_action($action){

		if (!parent::_before_action($action)) {
			return false;
		}

		if ($this->_settings === null) {
            //deprecated
			//$this->_uda_member_get = &uda::factory('voa_uda_frontend_member_get');
			//$this->_uda_member_format = &uda::factory('voa_uda_frontend_member_format');
			//$this->_uda_member_delete = &uda::factory('voa_uda_frontend_member_delete');
			//$this->_uda_member_insert = &uda::factory('voa_uda_frontend_member_insert');

            $this->_settings = voa_h_cache::get_instance()->get('plugin.member.setting', 'oa');
            $this->_departments = voa_h_cache::get_instance()->get('department', 'oa');
		}

		return true;
	}

	/**
	 * 找到指定m_uid的信息
	 * @param number $m_uid
	 * @param boolean $getAddressbook
	 * @return array
     * deprecated
	 */
	protected function _get_member($m_uid, $getAddressbook = TRUE) {
return ;;
		if ($m_uid) {
			return $this->_service_single('member', 'fetch', $m_uid);
			/**if ( $getAddressbook !== TRUE ) {
				return $member;
			}
			$addressbook	=	$this->_service_single('common_addressbook', 'fetch', $member['m_uid']);
			return array_merge($addressbook, $member);*/
		}

		return array();
	}

	/**
	 * 输出 phpExcel 需要用到的已格式化了的数据
	 * @param array $data
	 * @param string $departments
	 * @param string $jobs
	 * @return array
     * deprecated
	 */
	protected function _excel_data($data, $departments = false, $jobs = false){return false;
		$init_fields = $this->_excel_fields;
		$field2colnum = array();//字段与excel列字母对应关系
		$titleString = array();//excel 标题栏文字
		$titleWidth = array();//excel 标题栏宽度
		$excelData = array();//excel 行数据
		$ord = 65;//第一列字母A的ASCII码值
		foreach ($init_fields AS $key=>$arr) {
			$colCode = chr($ord);
			$field2colnum[$key] = $colCode;
			$titleString[$colCode] = $arr['name'];
			$titleWidth[$colCode] = $arr['width'];
			$ord++;
		}

		$gender_list = array_flip($this->gender_list);
		$active_list = array_flip($this->active_list);

		$i = 0;
		$departments = false;
		$jobs = false;
		foreach ($data AS $row) {
			foreach ($field2colnum AS $k => $col) {
				if ($k == 'cd_name' && !isset($row[$k]) && isset($row['cd_id'])) {
					if ($departments === false) {
						/** 全部部门数据 */
						$departments= $this->_department_list();
					}
					$excelData[$i][$col] = isset($departments[$row['cd_id']]) ? $departments[$row['cd_id']]['cd_name'] : '';
				} elseif ($k == 'cj_name' && !isset($row[$k]) && isset($row['cj_id'])) {
					if ($jobs === false) {
						/** 全部职位数据 */
						$jobs = $this->_job_list();
					}
					$excelData[$i][$col] = isset($jobs[$row['cj_id']]) ? $jobs[$row['cj_id']]['cj_name'] : '';
				} elseif ($k == 'm_gender') {
					$excelData[$i][$col] = $row[$k] ? (isset($gender_list[$row[$k]]) ? $gender_list[$row[$k]] : (in_array($row[$k], $gender_list) ? $row[$k] : '')) : '';
				} elseif ($k == 'm_active') {
					logger::error($row[$k].var_export($active_list, true));
					$excelData[$i][$col] = isset($active_list[$row[$k]]) ? $active_list[$row[$k]] : (in_array($row[$k], $active_list) ? $row[$k] : '');
				} else {
					$excelData[$i][$col] = isset($row[$k]) ? $row[$k] : '';
				}
			}
			$i++;
		}
		return array($titleString,$titleWidth,$excelData);
	}
}
