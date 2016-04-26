<?php
/**
 * SignSettingController.class.php
 * 考勤设置
 * @author: anything
 * @createTime: 2016/3/7 17:05
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace Sign\Controller\Api;

class SignSettingController extends AbstractController{

    /**
     * 获取外出考勤是否必须上传图片
     */
    public function out_must_upload_img_get(){

        $sign_setting_service = D('Sign/SignSetting', 'Service');
        $result = $sign_setting_service->get_upload_img_flag();
        $this->_result = array(
            'upload_img' => $result
        );

    }
}