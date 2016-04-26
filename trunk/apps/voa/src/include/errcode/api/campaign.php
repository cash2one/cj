<?php
/**
 * voa_errcode_api_campaign
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * $Author$
 * $Id$
 */
class voa_errcode_api_campaign {
    const SUBJECT_NULL = '905010:活动主题不能空';
    const MESSAGE_NULL = '905011:活动内容不能空';
    const START_NULL = '905012:开始时间不能空';
    const END_NULL = '905013:截止时间不能空';
    const ADDRESS_NULL = '905023:活动地点不能为空';
    const TIME_CUT_END = '905014:截止时间不能小于开始时间';
    const TIME_CUT_DATA = '905015:截止时间不能小于当前时间';
    const MUID_NULL = '905016:预览人不能为空';
    const TYPE_NULL = '905017:活动类型不能为空';
    const PIC_NULL  = '905018:图片不能为空';
    const DATA_NULL = '905019:数据不能为空';
    const SEND_OUT = '905019:发送次数过多';
	const OTIME_CUT_BITME = '905020:抢单开始时间不得大于活动截至时间';
	const ETIME_CUT_BITM = '905021:抢单结束时间不得大于活动截至时间';
	const OTIME_NULL = '905022:抢单时间不能空';
	const COLS_NOT_NULL = '905023:自定义字段不能为空';
	const CONTENT_NULL ='905024:内容不能为空';
}