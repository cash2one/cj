<?php
/**
 * voa_errcode_api_addressbook
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * $Author$
 * $Id$
 */
class voa_errcode_api_activity {

	const SUBJECT_NULL = '90500:活动主题不能空';
	const MESSAGE_NULL = '90501:活动内容不能空';
	const START_NULL = '90502:开始时间不能空';
	const END_NULL = '90503:结束时间不能空';
	const CUT_NULL = '90504:截止时间不能空';
	const MEM_NULL = '90505:不能创建没人参与的活动';
	const TIME_CUT_CREATED = '90506:截止时间不能小于创建时间';
	const TIME_START_END = '90507:结束时间不能小于开始时间';
	const TIME_CUT_END = '90508:截止时间不能大于结束时间';
	const TIME_CUT_DATA = '90509:截止时间不能小于当前时间';
	const TIME_CUT_STA = '90509:截止时间不能小于开始时间';
	const RETRUN_EMPTY = '90510:已操作过';
	const SUBJECT_BEYOND = '90511:标题最高15字，最低1个字';

}
