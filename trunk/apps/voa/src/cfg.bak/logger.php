<?php
/**
 * 日志配置文件
 *
 * $Author$
 * $Id$
 */

/**
 * 输出方式
 * + file 文件
 * + stdout 标准输出（屏幕）
 */
$conf['output'] = 'file';

/**
 * 输出文件的目录
 *  (仅output为file时有效)
 */
$conf['output_dir'] = APP_PATH. '/logs/';

/**
 * 是否使用第二个参数记录文件
 *  (仅output为file时有效)
 */
$conf['use_arg'] = true;

/**
 * 级别
 *  + Logger::LOGGER_LEVEL_TRACE
 *  + Logger::LOGGER_LEVEL_DEBUG
 *  + Logger::LOGGER_LEVEL_WARNING
 *  + Logger::LOGGER_LEVEL_ERROR
 *  + Logger::LOGGER_LEVEL_FATAL
 */
$conf['level'] = Logger::LOGGER_LEVEL_DEBUG;
