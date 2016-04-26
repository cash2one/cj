<?php
/**
 * voa_errcode_api_nvote
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * User: luckwang
 * Date: 15/3/11
 * Time: 下午4:23
 */

class voa_errcode_api_nvote {

    const SUBJECT_NULL = '90500:投票主题不能空';
    const ENDTIME_NULL = '90501:请选择投票结束时间';
    const IS_SINGLE_NULL = '90502:请选择投票类型是否为多选';
    const IS_SHOW_NAME_NULL = "90503:请选择投票方式是否为匿名";
    const IS_SHOW_RESULT_NULL = "90504:是否可查看结果";

    const RECEIVE_UID_NULL = "90505:请选择投票参与人";
    const OPTIONS_NULL = "90506:请添加投票选项";

    const OPTION_ID_NULL = "90507:请添加投票选项";

    const NV_ID_NULL = "90508:ID异常";

    const LIST_UNDEFINED_FUNCTION = '90509:方法不存在';

}
