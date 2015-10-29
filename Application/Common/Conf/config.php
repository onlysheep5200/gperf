<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common','User','Install','Script','Probedir'),
    //'MODULE_ALLOW_LIST'  => array('Home','Admin'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => '&/Osd6V2L|nGqc;Y=^(F[oyRAK?E~IuD}x`43hz{', //默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => true,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    'LOG_RECORD' =>true,
    'LOG_RECORD_LEVEL'       =>   array('EMERG','ALERT','CRIT','ERR','WARN','NOTIC','INFO','DEBUG','SQL'),

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 3, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'gperf', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'gperf_', // 数据库表前缀

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),

    /* 绝对路径 */
    'WEBDIR' => APP_PATH,
    'RRDFILE_DIR' => WEBDIR.'rdd',
    'RDD_IMG_TYPE' => 'SVG',
    'PROBE_SCRIPT_DIR' => APP_PATH.'Script/',
    'WEB_DIR' => APP_PATH,

    /* Probe相关 */
        'PROBE_SERVER_LIST' => array('gperf.edu.cn','166.111.9.229'),

    /* 任务参数路径 */
    'TASK_ARGS_DIR' => APP_PATH.'Args/',

    /* 积分相关  */
    'INCREASING_SCORE' => 10,
    'DECREASING_SCORE' =>10,
);
