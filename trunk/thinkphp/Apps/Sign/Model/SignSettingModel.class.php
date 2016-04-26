<?php
/**
 * GuestbookSettingModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignSettingModel extends \Common\Model\AbstractSettingModel {

    //签到设置
    const CONFIG_TYPE_SIGN = 1;

    //签退设置
    const CONFIG_TYPE_SIGN_OUT = 2;

    //微信菜单设置
    const CONFIG_TYPE_WXCPMENU = 3;

    //开关设置
    const CONFIG_TYPE_SWITCH = 4;

    //外出考勤必须上传图片
    const OUT_SIGN_UPLOAD_IMG = 2;

    //外出考勤不用必须上传图片
    const OUT_SIGN_NOt_UPLOAD_IMG = 1;

    //休息日是否允许考勤
    const REST_DAY_SIGN = 2;

    //休息日不允许考勤
    const REST_DAY_SIGN_OFF = 1;

    //开关类型  外出考勤上传图片
    const OUT_UPLOAD_IMG = 1;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'ss_';
	}
}
