<?php
/**
 * @Author: ppker
 * @Date:   2015-07-16 19:14:43
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-07-23 20:29:00
 */

class voa_errcode_api_invite {

    const NAME_NULL = '10001:姓名不能为空';
    const NAME_ERR = '10002:姓名格式出错';
    const NULL_ERR = '10003:你还没有输入任何信息，不能提交';
    const NULL_THREE = '10004:邮箱不可为空';
    const PHONE_ERR = '10005:手机格式不对';
    const EMAIL_ERR = '10006:邮箱格式不对';
    const WEI_ERR = '10007:微信号格式不对';
    const UNKNOW = '10010:未知错误';
    const ERR_UID = '10011:非法用户';
    const NAME_LEN = '10012:用户名字过长';
    const ERROR_DEPARARTEMT = '10013:请联系管理员设置默认部门';
}
