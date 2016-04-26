<?php
/**
 * voa_errcode_oa_sign
 * OA sign相关 呼叫错误码
 * 使用7位编码
 * $Author$
 * $Id$
 */
class voa_errcode_oa_sign {

	const TYPE_ERROR = '1001000:类型错误, 请联系管理员';
	const QRCODE_EXPIRED = '1001001:二维码已过期, 请刷新二维码';
	const LOCATION_EXPIRED = '1001002:地理位置信息已过期, 请重新打开应用并确保微信已授权获取地理位置信息';
	const SIGN_ON_EARLY_SIGN_BEGIN_HI = '1001003:还未到签到时间, 请稍候';
	const SIGN_FINISHED = '1001004:今日签到已完成';
	const SIGN_OFF_EARLY_WORK_BEGIN_HI = '1001005:还未开始上班不能签退';
	const DISTANCE_TOO_LONG = '1001006:位置错误, 请稍候重试';
	const LOCATION_ERROR = '1001007:位置获得失败, 请稍候重试';

	const LOCATION_TOO_SHORT = '1001008:两次上报时间间隔过短，请 %s分钟后再试';
	const LOCATION_SUBMIT_FAILED = '1001009:地理位置上报失败';
	
	const IS_NOT_WORK_DAY = '1001010:非工作日不需要签到签退';
	const OVER_SIGN_RANGE = '1001011:超出签到范围';
	const BATCH_UNDEFINED = '1001012:未找到对应班次';
	const SING_END = '1001013:今日打卡时间已结束'; 
}
