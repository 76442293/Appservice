<?php
/**
 * SignAction.class.php
 * 签到考勤相关接口
 * yfb 2016-07-15
 */
import("@.Action.BaseAction");

class SignAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

    }

    /*****  考勤设置  *****/
    //考勤设置权限列表
    public function listSignRight()
    {
        $company_id = trim($_REQUEST['company']);
        $_sign_right = M('sign_right', 'oa_', 'DB_CONFIG_OA');
        $list = $_sign_right->field("srt_id, user_id, user_name, user_face, partment_name, position_name")->join("oa_users on user_id = srt_user_id")->join("oa_partments on partment_id = user_partment_id")->join("oa_positions on position_id = user_position_id")->where("srt_company_id = {$company_id} and user_status = 1")->select();
        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询失败',
                'errorSql' => $_sign_right->getlastsql(),
            );
        } else if (empty($list)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '暂无考勤设置权限人员',
                'list' => array(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'list' => $list,
            );
        }
        echo json_encode($_r);
    }

    //添加修改考勤设置权限人
    public function setSignRight()
    {
        $company_id = trim($_REQUEST['company']);
        $user_id = trim($_REQUEST['user_id']);
        $_sign_right = M('sign_right', 'oa_', 'DB_CONFIG_OA');
        $is_exist = $_sign_right->where("srt_company_id = {$company_id} and srt_user_id = {$user_id}")->find();
        if (empty($is_exist)) {
            $data = array(
                'srt_company_id' => $company_id,
                'srt_user_id' => $user_id,
            );
            $srt_id = $_sign_right->add($data);
            if ($srt_id === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '添加失败',
                    'errorSql' => $_sign_right->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '添加成功',
                    'srt_id' => strval($srt_id),
                );
            }
        } else {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '添加人员已存在',
            );
        }
        echo json_encode($_r);
    }

    //删除考勤设置权限人
    public function delSignRight()
    {
        $srt_id = trim($_REQUEST['srt_id']);
        $_sign_right = M('sign_right', 'oa_', 'DB_CONFIG_OA');
        $rs = $_sign_right->where("srt_id = {$srt_id}")->delete();
        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '删除失败',
                'errorSql' => $_sign_right->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '删除成功',
            );
        }
        echo json_encode($_r);
    }

    //考勤列表
    public function signList()
    {
        $company_id = trim($_REQUEST['company']);
        $user_id = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '0';
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $sign_list = $_sign->where("ss_company_id = {$company_id} and ss_enabled = 1")->select();
        if ($sign_list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询失败',
                'errorSql' => $_sign->getlastsql(),
            );
        } else {
            if (empty($sign_list[0])) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '暂无考勤设置',
                    'sign_list' => array(),
                );
            } else {
                //如果是统计页面，筛选出当前用户可以查看的考勤，超级管理员都可见
                if (!empty($user_id)) {
                    $is_admin = $_user->where("user_id = {$user_id}")->find();
                    if ($is_admin['user_type'] != '1') {
                        foreach ($sign_list as $k => $v) {
                            if (empty($v['ss_check_user'])) {
                                unset($sign_list[$k]);
                            } else {
                                $user_arr = explode("_", $v['ss_check_user']);
                                if (!in_array($user_id, $user_arr)) {
                                    unset($sign_list[$k]);
                                }
                            }
                        }
                    }
                }
                $list = array();
                foreach ($sign_list as $k => $v) {
                    if ($v['ss_modify_time'] == $v['ss_create_time']) {
                        $sign_list[$k]['is_modify'] = '';
                    } else {
                        if (date('Y-m-d', $v['ss_modify_time']) == date('Y-m-d')) {
                            $sign_list[$k]['is_modify'] = '修改后第二天生效';
                        } else {
                            $sign_list[$k]['is_modify'] = '';
                        }
                    }
                    if (!empty($v['ss_partments'])) {
                        $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
                        $partment_str = str_replace('_', ',', $v['ss_partments']);
                        $get_partment_name = $_partment->where("partment_id in ({$partment_str})")->select();
                        foreach ($get_partment_name as $n => $p) {
                            if ($n == '0') {
                                $sign_list[$k]['partment_name'] = $p['partment_name'];
                            } else {
                                $sign_list[$k]['partment_name'] .= '、' . $p['partment_name'];
                            }
                        }
                    }
                    if (!empty($v['ss_users'])) {
                        $user_str = str_replace('_', ',', $v['ss_users']);
                        $get_user_name = $_user->where("user_id in ({$user_str})")->select();
                        foreach ($get_user_name as $n => $u) {
                            if ($n == '0') {
                                $sign_list[$k]['user_name'] = $u['user_name'];
                            } else {
                                $sign_list[$k]['user_name'] .= '、' . $u['user_name'];
                            }
                        }
                    }
                    if (!empty($v['ss_check_user'])) {
                        $check_user_str = str_replace('_', ',', $v['ss_check_user']);
                        $get_check_user_name = $_user->where("user_id in ({$check_user_str})")->select();
                        foreach ($get_check_user_name as $n => $u) {
                            if ($n == '0') {
                                $sign_list[$k]['check_user_name'] = $u['user_name'];
                            } else {
                                $sign_list[$k]['check_user_name'] .= '、' . $u['user_name'];
                            }
                        }
                    }
                    if (!empty($v['ss_except_users'])) {
                        $except_user_str = str_replace('_', ',', $v['ss_except_users']);
                        $get_except_user_name = $_user->where("user_id in ({$except_user_str})")->select();
                        foreach ($get_except_user_name as $n => $u) {
                            if ($n == '0') {
                                $sign_list[$k]['except_user_name'] = $u['user_name'];
                            } else {
                                $sign_list[$k]['except_user_name'] .= '、' . $u['user_name'];
                            }
                        }
                    }
                }
                foreach ($sign_list as $k => $v) {
                    $list[] = $v;
                }
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'sign_list' => empty($list) ? array() : $list,
                );
            }
        }
        echo json_encode($_r);
    }

    //待选择考勤部门列表
    public function partmentList()
    {
        $company_id = trim($_REQUEST['company']);
        $sign_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '';
        $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');

        if (empty($partment_id)) {
            $partment_list = $_partment->field("partment_id, partment_name, sp_set_id")->join("oa_sign_partments on sp_partment_id = partment_id")->where("partment_company_id = {$company_id} and partment_parent_id = 0")->select();
        } else {
            $partment_list = $_partment->field("partment_id, partment_name, sp_set_id")->join("oa_sign_partments on sp_partment_id = partment_id")->where("partment_company_id = {$company_id} and partment_parent_id = {$partment_id}")->select();
        }
        if ($partment_list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询失败',
                'errorSql' => $_partment->getlastsql(),
            );
        } else {
            foreach ($partment_list as $k => $v) {
                if (empty($v['sp_set_id'])) {
                    $partment_list[$k]['status'] = '0';//该部门不属于任何考勤
                } else {
                    if (!empty($sign_id) && $v['sp_set_id'] == $sign_id) {
                        $partment_list[$k]['status'] = '1';//该部门属于当前考勤
                    } else {
                        $partment_list[$k]['status'] = '2';//该部门属于其他考勤
                    }
                }
                $partment_list[$k]['childs'] = implode('_', $this->getChildsByPartment($company_id, $v['partment_id']));
            }
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'partment_list' => $partment_list,
            );
        }
        echo json_encode($_r);
    }

    //获取员工列表
    public function getUserList()
    {
        $company_id = trim($_REQUEST['company']);
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '0';
        $sign_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        $_user = M('users', 'oa_', 'DB_CONFIG_OA');

        $user_list = $_user->field("user_id, user_name, user_face, position_name, su_set_id")->join("oa_sign_users on su_user_id = user_id")->join("oa_positions on position_id = user_position_id")->where("user_company_id = {$company_id} and user_partment_id = {$partment_id} and user_status <> 2")->order("position_order")->select();
        if ($user_list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询失败',
                'errorSql' => $_user->getlastsql(),
            );
        } else {
            foreach ($user_list as $k => $v) {
                $user_list[$k]['position_name'] = empty($v['position_name']) ? '' : $v['position_name'];
                $user_list[$k]['su_set_id'] = empty($v['su_set_id']) ? '' : $v['su_set_id'];
                if (empty($user_list[$k]['su_set_id'])) {
                    $user_list[$k]['status'] = '0';//该员工不属于任何考勤
                } else {
                    if (!empty($sign_id) && $v['su_set_id'] == $sign_id) {
                        $user_list[$k]['status'] = '1';//该员工属于当前考勤
                    } else {
                        $user_list[$k]['status'] = '2';//该员工属于其他考勤
                    }
                }
            }
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'user_list' => $user_list,
            );
        }
        echo json_encode($_r);
    }

    //获取考勤设置信息
    public function getSignInfo()
    {
        $sign_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        if ($sign_id == '0') {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '新建考勤',
            );
        } else {
            $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
            $sign_info = $_sign->where("ss_id = {$sign_id}")->find();
            if ($sign_info === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询失败',
                    'errorSql' => $_sign->getlastsql(),
                );
            } else {
                $sign_info['members'] = '';
                //获取考勤部门名单
                if (!empty($sign_info['ss_partments'])) {
                    $partments = str_replace("_", ",", $sign_info['ss_partments']);
                    $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
                    $list_partment = $_partment->where("partment_id in ({$partments})")->select();
                    foreach ($list_partment as $v) {
                        if (empty($sign_info['members'])) {
                            $sign_info['members'] = $v['partment_name'];
                        } else {
                            $sign_info['members'] .= '、' . $v['partment_name'];
                        }
                    }
                }
                //获取考勤人员名单
                if (!empty($sign_info['ss_users'])) {
                    $users = str_replace("_", ",", $sign_info['ss_users']);
                    $_user = M('users', 'oa_', 'DB_CONFIG_OA');
                    $list_user = $_user->where("user_id in ({$users})")->select();
                    foreach ($list_user as $v) {
                        if (empty($sign_info['members'])) {
                            $sign_info['members'] = $v['user_name'];
                        } else {
                            $sign_info['members'] .= '、' . $v['user_name'];
                        }
                    }
                }
                //特例考勤时间
                $_sign_date = M('sign_date', 'oa_', 'DB_CONFIG_OA');
                $sign_info['special_list'] = $_sign_date->where("sd_set_id = {$sign_id}")->select();
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'sign_info' => $sign_info,
                );
            }
        }
        echo json_encode($_r);
    }

    //验证提交的部门和员工是否属于其他考勤
    public function checkInOtherSign()
    {
        $company_id = trim($_REQUEST['company']);
        $partments = isset($_REQUEST['ss_partments']) ? trim($_REQUEST['ss_partments']) : '';
        $users = isset($_REQUEST['ss_users']) ? trim($_REQUEST['ss_users']) : '';
        $sign_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
        $repeat_partment = array();    //重复部门
        $repeat_user = array();        //重复员工
        if ($partments == '0') {
            //全体考勤，查询所有一级部门
            $get_all_partment = $_partment->field("partment_id")->where("partment_company_id = {$company_id} and partment_parent_id = 0")->select();
            if (!empty($get_all_partment)) {
                $partment_arr = array();
                foreach ($get_all_partment as $k => $v) {
                    $partment_arr[] = $v['partment_id'];
                }
                $partments = implode("_", $partment_arr);
            } else {
                //如果没有部门，查找公司下的所有员工
                $get_all_user = $_user->field("user_id")->where("user_company_id = {$company_id} and user_status = 1")->select();
                if (!empty($get_all_user)) {
                    $user_arr = array();
                    foreach ($get_all_user as $k => $v) {
                        $user_arr[] = $v['user_id'];
                    }
                    $users = implode("_", $user_arr);
                }
            }
        }
        if (!empty($partments)) {
            $partment_arr = explode('_', $partments);
            //已设置考勤的部门
            $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
            foreach ($partment_arr as $k => $v) {
                //子孙部门
                $childs = $this->getChildsByPartment($company_id, $v);
                $childs[] = $v;
                $child_str = implode(',', $childs);

                $partment_list = $_partment->field("partment_id, partment_name, sp_set_id, ss_name")->join("oa_sign_partments on sp_partment_id = partment_id")->join("oa_sign_set on ss_id = sp_set_id")->where("partment_id in ({$child_str})")->select();
                foreach ($partment_list as $k => $v) {
                    if (!empty($v['sp_set_id']) && !empty($sign_id) && $v['sp_set_id'] != $sign_id) {
                        $repeat_partment[] = $partment_list[$k];
                    }
                }

                $user_list = $_user->field("user_id, user_name, user_face, partment_name, position_name, su_set_id, ss_name")->join("oa_partments on partment_id = user_partment_id")->join("oa_positions on position_id = user_position_id")->join("oa_sign_users on su_user_id = user_id")->join("oa_sign_set on ss_id = su_set_id")->where("user_partment_id in ({$child_str}) and user_status = 1")->select();
                foreach ($user_list as $k => $v) {
                    if (!empty($v['su_set_id']) && $v['su_set_id'] != $sign_id) {
                        $repeat_user[] = $user_list[$k];
                    }
                }
            }
        }
        if (!empty($users)) {
            $user_str = str_replace('_', ',', $users);
            $user_list = $_user->field("user_id, user_name, user_face, partment_name, position_name, su_set_id, ss_name")->join("oa_partments on partment_id = user_partment_id")->join("oa_positions on position_id = user_position_id")->join("oa_sign_users on su_user_id = user_id")->join("oa_sign_set on ss_id = su_set_id")->where("user_id in ({$user_str}) and user_status = 1")->select();
            foreach ($user_list as $k => $v) {
                if (!empty($v['su_set_id']) && $v['su_set_id'] != $sign_id) {
                    $repeat_user[] = $user_list[$k];
                }
            }
        }
        if (empty($repeat_user) && empty($repeat_partment)) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '没有重复设置',
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'repeat_user' => $repeat_user,
                'repeat_partment' => $repeat_partment,
            );
        }
        echo json_encode($_r);
    }

    //创建考勤
    public function saveSign()
    {
        $sign_info = array();
        $sign_info['ss_company_id'] = trim($_REQUEST['ss_company_id']);        //公司id
        $sign_info['ss_name'] = trim($_REQUEST['ss_name']);            //考勤名称
        $sign_info['ss_outside'] = trim($_REQUEST['ss_outside']);        //是否为外勤考核，0：否、1：是，默认为0内勤
        $sign_info['ss_sign_twice'] = trim($_REQUEST['ss_sign_twice']);        //是否一天两次上下班签到
        $sign_info['ss_time_on_am'] = str_replace('-', ':', trim($_REQUEST['ss_time_on_am']));    //上午上班时间
        $sign_info['ss_time_off_pm'] = str_replace('-', ':', trim($_REQUEST['ss_time_off_pm']));    //下午下班时间
        //一天两次上下班签到
        if ($sign_info['ss_sign_twice'] == '1') {
            $sign_info['ss_time_off_am'] = str_replace('-', ':', trim($_REQUEST['ss_time_off_am']));    //上午下班时间
            $sign_info['ss_time_on_pm'] = str_replace('-', ':', trim($_REQUEST['ss_time_on_pm']));    //下午上班时间
        }
        $sign_info['ss_need_photo'] = trim($_REQUEST['ss_need_photo']);        //是否需要拍照签到0：否、1：是，默认为0
        $sign_info['ss_normal_day'] = trim($_REQUEST['ss_normal_day']);        //正常重复工作日，星期几，以 _ 分隔
        $sign_info['ss_check_user'] = trim($_REQUEST['ss_check_user']);        //允许查看考勤统计人员id，以 _ 分隔
        $sign_info['ss_except_users'] = trim($_REQUEST['ss_except_users']);    //无需考勤人员id，以 _ 分隔
        $sign_info['ss_site'] = trim($_REQUEST['ss_site']);            //考勤地点
        $sign_info['ss_lon'] = trim($_REQUEST['ss_lon']);            //考勤定位百度经度坐标
        $sign_info['ss_lat'] = trim($_REQUEST['ss_lat']);            //考勤定位百度纬度坐标
        $sign_info['ss_allow_diff'] = trim($_REQUEST['ss_allow_diff']);        //签到地点允许偏差
        $sign_info['ss_wifi_name'] = isset($_REQUEST['ss_wifi_name']) ? trim($_REQUEST['ss_wifi_name']) : '';        //签到wifi名称
        $sign_info['ss_partments'] = isset($_REQUEST['ss_partments']) ? trim($_REQUEST['ss_partments']) : '';    //考勤部门，以 _ 分隔
        $sign_info['ss_users'] = isset($_REQUEST['ss_users']) ? trim($_REQUEST['ss_users']) : '';            //单例考勤人员id，以 _ 分隔

        if (!empty($sign_info['ss_wifi_name'])) {
            $sign_info['ss_use_wifi'] = '1';        //是否开启wifi签到
        }
        $if_change_exist = trim($_REQUEST['change']);            //是否覆盖重复考勤，0：不覆盖、1：覆盖
        $sign_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';

        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_sign_user = M('sign_users', 'oa_', 'DB_CONFIG_OA');
        $_sign_partment = M('sign_partments', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');

        $act = '0'; //操作动作，0：添加、1：修改
        $need_sign_today = '1';//创建当天是否需要考勤

        if ($sign_id == '0') {
            //新建考勤
            $sign_info['ss_create_time'] = time();
            $sign_info['ss_modify_time'] = $sign_info['ss_create_time'];
            $rs = $_sign->add($sign_info);
            if ($rs !== false) {
                $sign_id = $rs;
            }
        } else {
            //修改考勤
            $sign_info['ss_modify_time'] = time();
            $rs = $_sign->where("ss_id = {$sign_id}")->save($sign_info);
            $act = '1';
        }
        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '操作失败',
                'errorSql' => $_sign->getlastsql(),
            );
        } else {
            $ss_partments = $sign_info['ss_partments'];    //考勤部门，以 _ 分隔
            $ss_users = $sign_info['ss_users'];        //单例考勤人员id，以 _ 分隔

            $sign_partments = array();    //考勤部门数组
            $sign_users = array();        //考勤人员数组
            $except_users = explode('_', $sign_info['ss_except_users']);    //无需考勤人员数组


            if ($ss_partments != '') {
                if ($ss_partments == '0') {
                    //全体考勤
                    $user_inside = $_user->field("user_id")->where("user_company_id = {$sign_info['ss_company_id']} and user_status = 1")->select();
                    foreach ($user_inside as $key => $value) {
                        //$sign_users = array_merge($sign_users, $user_inside);
                        //除去无需考勤人员
                        if (!in_array($value['user_id'], $except_users)) {
                            $sign_users[] = $value['user_id'];
                        }
                    }
                } else {
                    $partment_arr = explode('_', $ss_partments);
                    //已设置考勤的部门
                    $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
                    foreach ($partment_arr as $k => $v) {
                        //子孙部门
                        $childs = $this->getChildsByPartment($sign_info['ss_company_id'], $v);
                        $childs[] = $v;
                        $sign_partments = array_merge($sign_partments, $childs);
                        //管辖员工
                        $partment_str = implode(',', $childs);
                        $user_inside = $_user->field("user_id")->where("user_partment_id in ({$partment_str}) and user_status = 1")->select();
                        foreach ($user_inside as $key => $value) {
                            //$sign_users = array_merge($sign_users, $user_inside);
                            //除去无需考勤人员
                            if (!in_array($value['user_id'], $except_users)) {
                                $sign_users[] = $value['user_id'];
                            }
                        }
                    }
                }
            }
            if (!empty($ss_users)) {
                $sign_users = explode('_', $ss_users);
            }
            //去重
            $sign_partments = array_unique($sign_partments);
            $sign_users = array_unique($sign_users);

            //清空所属考勤id的考勤用户表和考勤部门表
            $_sign_user->where("su_set_id = {$sign_id}")->delete();
            $_sign_partment->where("sp_set_id = {$sign_id}")->delete();

            //是否替换所选择人员原有考勤
            if ($if_change_exist == '0') {    //不替换，添加时遇到已存在的用户id则跳过
                //已设置考勤的人员
                $sign_user_list = $_sign_user->field("su_user_id")->where("su_company_id = " . $sign_info['ss_company_id'])->select();
                //已设置考勤的部门
                $sign_partment_list = $_sign_partment->field("sp_partment_id")->where("sp_company_id = " . $sign_info['ss_company_id'])->select();

                //从提交的考勤用户中去除已设置考勤的人员
                foreach ($sign_user_list as $k => $v) {
                    if (in_array($v['su_user_id'], $sign_users)) {
                        unset($sign_users[array_search($v['su_user_id'], $sign_users)]);
                    }
                }
                //从提交的考勤部门中去除已设置考勤的部门
                foreach ($sign_partment_list as $k => $v) {
                    if (in_array($v['sp_user_id'], $sign_partments)) {
                        unset($sign_partments[array_search($v['sp_partment_id'], $sign_partments)]);
                    }
                }

                //添加考勤人员表oa_sign_users
                $SQLUser = "INSERT INTO oa_sign_users (su_user_id, su_company_id, su_set_id) VALUES";
                $count_user = count($sign_users);
                foreach ($sign_users as $k => $v) {
                    $SQLUser .= "({$v}, {$sign_info['ss_company_id']}, {$sign_id})";
                    if (($k + 1) < $count_user) {
                        $SQLUser .= ",";
                    }
                }
                $_sign_user->query($SQLUser);

                //添加考勤部门表oa_sign_partments
                $count_partment = count($sign_partments);
                $SQLPartment = "INSERT INTO oa_sign_partments (sp_partment_id, sp_company_id, sp_set_id) VALUES";
                foreach ($sign_partments as $k => $v) {
                    $SQLPartment .= "({$v}, {$sign_info['ss_company_id']}, {$sign_id})";
                    if (($k + 1) < $count_partment) {
                        $SQLPartment .= ",";
                    }
                }
                $_sign_partment->query($SQLPartment);

            } else {    //替换，添加时遇到已存在的用户id或者部门id则修改其考勤id

                //添加考勤人员表oa_sign_users
                $count_user = count($sign_users);
                $SQLUser = "INSERT INTO oa_sign_users (su_user_id, su_company_id, su_set_id) VALUES";
                foreach ($sign_users as $k => $v) {
                    $SQLUser .= "({$v}, {$sign_info['ss_company_id']}, {$sign_id})";
                    if (($k + 1) < $count_user) {
                        $SQLUser .= ",";
                    }
                }
                $SQLUser .= " ON DUPLICATE KEY UPDATE su_set_id={$sign_id}";
                $_sign_user->query($SQLUser);

                /*
                //删除已生成的当天考勤记录 add by yfb 2017-05-24
                $date	 = date('Y-m-d');
                $user_str = implode(",", $sign_users);
                $_sign_record->where("sr_date = '{$date}' and sr_user_id in ({$user_str})")->delete();
                */

                //添加考勤部门表oa_sign_partments
                $count_partment = count($sign_partments);
                $SQLPartment = "INSERT INTO oa_sign_partments (sp_partment_id, sp_company_id, sp_set_id) VALUES";
                foreach ($sign_partments as $k => $v) {
                    $SQLPartment .= "({$v}, {$sign_info['ss_company_id']}, {$sign_id})";
                    if (($k + 1) < $count_partment) {
                        $SQLPartment .= ",";
                    }
                }
                $SQLPartment .= " ON DUPLICATE KEY UPDATE sp_set_id={$sign_id}";
                $_sign_partment->query($SQLPartment);
            }

            // *#* 判断设置考勤当天是否需要考勤
            // *#* step1 先统计出正常的工作日，如果当天不在正常工作日内，设为不需要考勤
            $date = date('Y-m-d');    //当前日期
            $weekday = date('N');        //当前星期几，1为星期一
            $need_sign_today = '1';        //是否需要签到标记，默认为1，需要签到
            $normal_day = explode('_', $sign_info['ss_normal_day']);    //正常上班日
            $sign_time_today = array();
            if (!in_array($weekday, $normal_day)) {
                $need_sign_today = '0';    //正常休息日，无需签到
            }

            /** 特例考勤时间special_date
             *  字符串格式：年月日（星期几）_type_time1_time2_time3_time4~年月日（星期几）_type_time1_time2_time3_time4
             *  type：(特例类别，0：星期几上班时间特例、1：特例日期上班、2：特例日期休息)
             *  time1：上午上班时间
             *  time2：下午下班时间
             *  time3：上午下班时间（可选）
             *  time4：下午上班时间（可选）
             */
            $special_date = isset($_REQUEST['special_date']) ? trim($_REQUEST['special_date']) : '';
            if (!empty($special_date)) {
                $_sign_date = M('sign_date', 'oa_', 'DB_CONFIG_OA');
                $data = array();
                $data['sd_company_id'] = $sign_info['ss_company_id'];
                $data['sd_set_id'] = $sign_id;
                $_sign_date->where("sd_set_id = {$sign_id}")->delete();
                $date_list = explode('~', $special_date);
                //$special_sign_days = array();//特殊上班日期数组
                foreach ($date_list as $k => $v) {
                    $date_info = explode('_', $v);
                    $data['sd_special_date'] = $date_info[0];
                    $data['sd_special_type'] = $date_info[1];
                    if ($data['sd_special_type'] != '2') {    //特殊工作日
                        $data['sd_time_on_am'] = str_replace('-', ':', $date_info[2]);
                        $data['sd_time_off_pm'] = str_replace('-', ':', $date_info[3]);
                        if ($sign_info['ss_sign_twice'] == '1') {
                            $data['sd_time_off_am'] = str_replace('-', ':', $date_info[4]);
                            $data['sd_time_on_pm'] = str_replace('-', ':', $date_info[5]);
                        }
                        // *#* step2 如果当天的日期在特殊工作日内，设为需要考勤，并将上下班时间存入数组$sign_time_today中
                        if ($date == $date_info[0]) {
                            $need_sign_today = '1';    //特殊工作日，需要签到
                            $sign_time_today['on_am'] = $data['sd_time_on_am'];
                            $sign_time_today['off_pm'] = $data['sd_time_off_pm'];
                            if ($sign_info['ss_sign_twice'] == '1') {
                                $sign_time_today['off_am'] = $data['sd_time_off_am'];
                                $sign_time_today['on_pm'] = $data['sd_time_on_pm'];
                            }
                        }
                        /* *#* step3 如果当天的星期几在特殊工作日内，代表这天上下班时间与平时不同，但是也是上班的日期，
                         * $need_sign_today == '1' 这个条件是为了避免覆盖step4中由特殊休息日设置的无需签到变量$need_sign_today = '0'
                         * 数组$sign_time_today为空，是为了避免覆盖step2中特殊工作日期设置的$sign_time_today
                         * 满足上述条件下，将上下班时间存入数组$sign_time_today中
                        */
                        if ($weekday == $date_info[0] && $need_sign_today == '1' && empty($sign_time_today)) {
                            $sign_time_today['on_am'] = $data['sd_time_on_am'];
                            $sign_time_today['off_pm'] = $data['sd_time_off_pm'];
                            if ($sign_info['ss_sign_twice'] == '1') {
                                $sign_time_today['off_am'] = $data['sd_time_off_am'];
                                $sign_time_today['on_pm'] = $data['sd_time_on_pm'];
                            }
                        }
                        //$special_sign_days[] = $date_info[0];
                    } else {    //特殊休息日
                        // *#* step4 特殊休息日，优先级同step2，因为同一个日期只能存在一种情况，所以互斥
                        if ($date == $data['sd_special_date']) {
                            $need_sign_today = '0';    //特殊休息日，无需签到
                        }
                    }
                    $rs = $_sign_date->add($data);
                }
            }
            /* *#* step5 如果当天的星期几在正常工作日内
             * $need_sign_today == '1' 这个条件是排除了前面所有已设为休息日的步骤
             * 数组$sign_time_today为空，是为了避免覆盖前面步骤中优先级较高的时间记录
             * 满足上述条件下，将上下班时间存入数组$sign_time_today中
            */
            if (in_array($weekday, $normal_day) && $need_sign_today == '1' && empty($sign_time_today)) {
                $sign_time_today['on_am'] = $sign_info['ss_time_on_am'];
                $sign_time_today['off_pm'] = $sign_info['ss_time_off_pm'];
                if ($sign_info['ss_sign_twice'] == '1') {
                    $sign_time_today['off_am'] = $sign_info['ss_time_off_am'];
                    $sign_time_today['on_pm'] = $sign_info['ss_time_on_pm'];
                }
            }
            if ($need_sign_today == '1' && $act == '0') {
                //添加考勤当天需要签到，插入当天初始签到记录，修改考勤设置的话，需要第二天生效，因为默认每天00:00自动生成当天考勤初始数据
                //上午上班初始记录
                $SQL1 = "INSERT INTO oa_sign_record (sr_company_id, sr_sign_id, sr_partment_id, sr_user_id, sr_date, sr_std_time, sr_step) SELECT user_company_id, {$sign_id}, user_partment_id, user_id, '{$date}', '" . $sign_time_today['on_am'] . "', 1 FROM oa_sign_users LEFT JOIN oa_users ON su_user_id = user_id WHERE su_set_id = {$sign_id}";
                $_sign_record->query($SQL1);

                //下午下班初始记录
                $SQL4 = "INSERT INTO oa_sign_record (sr_company_id, sr_sign_id, sr_partment_id, sr_user_id, sr_date, sr_std_time, sr_step) SELECT user_company_id, {$sign_id}, user_partment_id, user_id, '{$date}', '" . $sign_time_today['off_pm'] . "', 4 FROM oa_sign_users LEFT JOIN oa_users ON su_user_id = user_id WHERE su_set_id = {$sign_id}";
                $_sign_record->query($SQL4);

                //一天两次上下班的情况下
                if ($sign_info['ss_sign_twice'] == '1') {
                    //上午下班初始记录
                    $SQL2 = "INSERT INTO oa_sign_record (sr_company_id, sr_sign_id, sr_partment_id, sr_user_id, sr_date, sr_std_time, sr_step) SELECT user_company_id, {$sign_id}, user_partment_id, user_id, '{$date}', '" . $sign_time_today['off_am'] . "', 2 FROM oa_sign_users LEFT JOIN oa_users ON su_user_id = user_id WHERE su_set_id = {$sign_id}";
                    $_sign_record->query($SQL2);

                    //下午上班初始记录
                    $SQL3 = "INSERT INTO oa_sign_record (sr_company_id, sr_sign_id, sr_partment_id, sr_user_id, sr_date, sr_std_time, sr_step) SELECT user_company_id, {$sign_id}, user_partment_id, user_id, '{$date}', '" . $sign_time_today['on_pm'] . "', 3 FROM oa_sign_users LEFT JOIN oa_users ON su_user_id = user_id WHERE su_set_id = {$sign_id}";
                    $_sign_record->query($SQL3);
                }
            }
            $_r = array(
                'errorCode' => '1',
                'errorName' => '操作成功',
                //'errorSql'	=> $_sign->getlastsql(),
            );
        }
        echo json_encode($_r);
    }

    //允许查看考勤统计的人员和无需考勤的人员列表
    public function allowToSee()
    {
        $sign_id = trim($_REQUEST['ss_id']);
        $type = trim($_REQUEST['type']);//1：允许查看考勤统计的人员、2：无需考勤的人员列表
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $user_info = $_sign->where("ss_id = {$sign_id}")->find();
        if ($type == '1') {
            $user_str = str_replace('_', ',', $user_info['ss_check_user']);
        } else if ($type == '2') {
            $user_str = str_replace('_', ',', $user_info['ss_except_users']);
        }
        $user_list = $_user->field("user_id, user_name, user_face, partment_name, position_name")->join("oa_partments on partment_id = user_partment_id")->join("oa_positions on position_id = user_position_id")->where("user_id in ({$user_str})")->select();
        if ($user_list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询失败',
                'errorSql' => $_user->getlastsql(),
            );
        } else {
            if (empty($user_list[0])) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '未设置查看人',
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'user_list' => $user_list,
                );
            }
        }
        echo json_encode($_r);
    }


    //删除考勤
    public function delSign()
    {
        $ss_id = trim($_REQUEST['ss_id']);
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_sign_user = M('sign_users', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');
        $_sign_partment = M('sign_partments', 'oa_', 'DB_CONFIG_OA');
        $check = $_sign->where("ss_id = {$ss_id}")->find();
        if (empty($check)) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误，指定考勤不存在',
            );
        } else {
            $rs = $_sign->where("ss_id = {$ss_id}")->setfield("ss_enabled", "0");
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除考勤设置失败',
                    'errorSql' => $_sign->getlastsql(),
                );
            } else {
                $rs = $_sign_partment->where("sp_set_id = {$ss_id}")->delete();
                if ($rs === false) {
                    $_r = array(
                        'errorCode' => '0',
                        'errorName' => '删除考勤部门失败',
                        'errorSql' => $_sign_partment->getlastsql(),
                    );
                } else {
                    $rs = $_sign_user->where("su_set_id = {$ss_id}")->delete();
                    if ($rs === false) {
                        $_r = array(
                            'errorCode' => '0',
                            'errorName' => '删除考勤人员失败',
                            'errorSql' => $_sign_user->getlastsql(),
                        );
                    } else {
                        /*$rs = $_sign_record->where("sr_sign_id = {$ss_id}")->delete();
                        if($rs === false){
                            $_r = array (
                                'errorCode' => '0',
                                'errorName' => '删除考勤记录失败',
                                'errorSql'	=> $_sign_record->getlastsql(),
                            );
                        }else{*/

                        $_r = array(
                            'errorCode' => '1',
                            'errorName' => '删除成功',
                        );
                        //}
                    }
                }
            }
        }
        echo json_encode($_r);
    }

    /*****  签到  *****/

    //获取当前系统时间
    public function getSysTime()
    {
        $time_now = date("Y-m-d H:i:s");
        echo json_encode(array("time_now" => $time_now));
    }

    //获取当前用户可进行的签到状态
    public function getSignStatus()
    {
        $user_id = trim($_REQUEST['user_id']);
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_sign_date = M('sign_date', 'oa_', 'DB_CONFIG_OA');
        $_sign_user = M('sign_users', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');
        //$_sign_partment = M('sign_partments', 'oa_', 'DB_CONFIG_OA');
        //判断用户是否有当天的考勤初始数据
        $time = time();
        $date = date('Y-m-d');
        //判断用户属于哪个考勤
        $sign_id = $_sign_user->where("su_user_id = {$user_id}")->getfield("su_set_id");
        if (empty($sign_id)) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '不属于任何考勤',
            );
        } else {
            $sign_set = $_sign->where("ss_id = {$sign_id}")->find();
            $sign_info_today = $_sign_record->where("sr_user_id = {$user_id} and sr_date = '{$date}'")->select();
            if (empty($sign_info_today)) {
                $_r = array(
                    'errorCode' => '3',
                    'errorName' => '休息时间，是否切换为加班考勤',
                    'sign_info' => $sign_set,
                );
            } else {
                $sign_info = array();
                foreach ($sign_info_today as $k => $v) {
                    $v['lon'] = $sign_set['ss_lon'];
                    $v['lat'] = $sign_set['ss_lat'];
                    $v['diff'] = $sign_set['ss_allow_diff'];//允许签到范围
                    $v['use_wifi'] = $sign_set['ss_use_wifi'];    //是否使用wifi签到
                    $v['wifi_name'] = $sign_set['ss_wifi_name'];//wifi名称，多个以_分隔
                    $v['need_photo'] = $sign_set['ss_need_photo'];//是否需要拍照
                    //以sr_step为键创建数组
                    $sign_info[$v['sr_step']] = $v;
                }
                //如果当前时间已超过下一个步骤的时间，则设置当前步骤为已过期
                if (!empty($sign_info[2]) && !empty($sign_info[3])) {
                    //一天两次上下班
                    //当前时间大于上午下班时间，并且上班未签到
                    if ($time > strtotime($sign_info[2]['sr_std_time'])) {
                        if (empty($sign_info[1]['sr_time'])) {
                            $sign_info[1]['text'] = '已过期';
                        }
                    }
                    //当前时间大于下午上班时间，并且上午上下班未签到/退
                    if ($time > strtotime($sign_info[3]['sr_std_time'])) {
                        if (empty($sign_info[1]['sr_time'])) {
                            $sign_info[1]['text'] = '已过期';
                        }
                        if (empty($sign_info[2]['sr_time'])) {
                            $sign_info[2]['text'] = '已过期';
                        }
                    }
                    //当前时间大于下午下班时间，并且上午上下班未签到/退，下午上班未签到
                    if ($time > strtotime($sign_info[4]['sr_std_time'])) {
                        if (empty($sign_info[1]['sr_time'])) {
                            $sign_info[1]['text'] = '已过期';
                        }
                        if (empty($sign_info[2]['sr_time'])) {
                            $sign_info[2]['text'] = '已过期';
                        }
                        if (empty($sign_info[3]['sr_time'])) {
                            $sign_info[3]['text'] = '已过期';
                        }
                    }
                    //下班签到后，如果未过24:00则可以转为加班模式
                    if (!empty($sign_info[4]['sr_time']) && $time < strtotime('24:00')) {
                        $change_to_add = '1';
                    } else {
                        $change_to_add = '0';
                    }
                } else {
                    //当前时间大于下午下班时间，并且上午上班未签到
                    if ($time > strtotime($sign_info[4]['standard_time']) && !empty($sign_info[4]['standard_time'])) {
                        if (empty($sign_info[1]['sr_time'])) {
                            $sign_info[1]['text'] = '已过期';
                        }
                    }
                }

                foreach ($sign_info as $k => $v) {
                    if (!empty($v['sr_time'])) {
                        if ($v['sr_step'] == '2' || $v['sr_step'] == '4' || $v['sr_step'] == '6') {
                            $sign_info[$k]['text'] = '已签退';
                        } else if ($v['sr_step'] == '1' || $v['sr_step'] == '3' || $v['sr_step'] == '5') {
                            $sign_info[$k]['text'] = '已签到';
                        }
                        if (empty($sign_info[$k]['text'])) {
                            $sign_info[$k]['text'] = '';
                        }
                        $sign_info[$k]['sign_time'] = date('H:i', $v['sr_time']);
                    }
                }

                //下班签到后，如果未过24:00则可以转为加班模式
                if (!empty($sign_info[4]['sign_time']) && $time < strtotime('24:00') && empty($sign_info[5]['sign_time'])) {
                    $_r = array(
                        'errorCode' => '4',
                        'errorName' => '下班了，是否切换为加班考勤',
                        'sign_info' => $sign_info,
                    );
                } else {
                    if (!empty($sign_info[5]['sign_time'])) {
                        $_r = array(
                            'errorCode' => '2',
                            'errorName' => '加班状态',
                            'sign_info' => $sign_info,
                        );
                    } else {
                        $_r = array(
                            'errorCode' => '1',
                            'errorName' => '查询成功',
                            'sign_info' => $sign_info,
                        );
                    }
                }
            }
        }
        echo json_encode($_r);
    }

    //签到验证，判断是否使用重复手机、是否范围外、是否使用设置的wifi、是否迟到、是否早退
    public function checkSign()
    {
        $user_id = trim($_REQUEST['user_id']);
        $sign_id = trim($_REQUEST['ss_id']);
        $sign_time = trim($_REQUEST['sign_time']);
        $wifi_name = trim($_REQUEST['wifi_name']);
        $token_id = isset($_REQUEST['token_id']) ? trim($_REQUEST['token_id']) : '';
        $distance = intval(trim($_REQUEST['distance']));
        $step = trim($_REQUEST['step']);        //1：上午上班签到、2：上午下班签退、3：下午上班签到、4：下午下班签退、5：加班签到、6：加班签退
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');
        $day = date('Y-m-d');
        //判断token_id当天是否用于其他user_id签到，如果是未上线的打包用户，则不需要判断，因为未上线的打包app无法开启推送。
        $company_info = $_user->join("oa_companys on user_company_id = company_id")->where("user_id = {$user_id}")->find();
        $need_token = '0';
        if ($company_info['company_app'] == '888') {
            $need_token = '1';
        } else {
            if ($company_info['company_is_online'] == '1') {
                $need_token = '1';
            } else {
                $need_token = '0';
            }
        }
        if ($need_token == '1') {
            if (!empty($token_id)) {
                $check_token = $_sign_record->where("sr_date = '{$day}' and sr_token_id = '{$token_id}'")->find();
            }
        }
        if (!empty($check_token) && $check_token['sr_user_id'] != $user_id) {
            //今天已经有其他账号使用本手机签到过啦
            $_r = array(
                'errorCode' => '1',
                'errorName' => '今天已经有其他账号使用本手机签到过啦',
            );
        } else {
            $sign_set = $_sign->where("ss_id = {$sign_id}")->find();
            if ($sign_set === false) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '查询签到设置失败',
                    'errorSql' => $_sign->getlastsql(),
                );
            } else if (empty($sign_set)) {
                $_r = array(
                    'errorCode' => '3',
                    'errorName' => '查询签到设置错误，指定签到id不存在',
                );
            } else {
                if ($distance > $sign_set['ss_allow_diff']) {
                    //签到距离超出设置的允许偏差范围
                    $_r = array(
                        'errorCode' => '4',
                        'errorName' => '范围外，选择理由',
                        'status' => '4',
                    );
                } else if ($sign_set['ss_use_wifi'] == '1' && !in_array($wifi_name, explode('-', $sign_set['ss_wifi_name']))) {
                    //如果设置了wifi签到，且当前登录的wifi名与设置的不同
                    $_r = array(
                        'errorCode' => '5',
                        'errorName' => '未连接指定wifi，选择理由',
                        'wifiName' => $sign_set['ss_wifi_name'],
                        'status' => '4',
                    );
                } else if ($step == '1' && $sign_time > strtotime(str_replace('-', ':', $sign_set['ss_time_on_am']))) {
                    $_r = array(
                        'errorCode' => '6',
                        'errorName' => '迟到，填理由',
                        'status' => '2',
                    );
                } else if ($sign_set['ss_sign_twice'] == '1' && $step == '2' && $sign_time < strtotime(str_replace('-', ':', $sign_set['ss_time_off_am']))) {
                    $_r = array(
                        'errorCode' => '7',
                        'errorName' => '早退，填理由',
                        'status' => '3',
                    );
                } else if ($sign_set['ss_sign_twice'] == '1' && $step == '3' && $sign_time > strtotime(str_replace('-', ':', $sign_set['ss_time_on_pm']))) {
                    $_r = array(
                        'errorCode' => '6',
                        'errorName' => '迟到，填理由',
                        'status' => '2',
                    );
                } else if ($step == '4' && $sign_time < strtotime(str_replace('-', ':', $sign_set['ss_time_off_pm']))) {
                    $_r = array(
                        'errorCode' => '7',
                        'errorName' => '早退，填理由',
                        'status' => '3',
                    );
                } else {
                    $_r = array(
                        'errorCode' => '0',
                        'errorName' => '可以提交',
                        'status' => '1',
                    );
                }
            }
        }
        echo json_encode($_r);
    }

    //签到
    public function doSign()
    {
        $sign_data = array();
        $sr_id = isset($_REQUEST['sr_id']) ? trim($_REQUEST['sr_id']) : '0';//考勤记录id
        $ss_id = trim($_REQUEST['ss_id']);//考勤设置id
        $step = isset($_REQUEST['step']) ? trim($_REQUEST['step']) : '0';//考勤步骤（考勤节点）
        $user_id = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '0';

        $sign_data['sr_time'] = trim($_REQUEST['sign_time']);
        $sign_data['sr_status'] = trim($_REQUEST['status']);
        $sign_data['sr_lon'] = trim($_REQUEST['lon']);
        $sign_data['sr_lat'] = trim($_REQUEST['lat']);
        $sign_data['sr_distance'] = intval(trim($_REQUEST['distance']));
        $sign_data['sr_token_id'] = isset($_REQUEST['token_id']) ? trim($_REQUEST['token_id']) : '';
        $sign_data['sr_reason'] = isset($_REQUEST['reason']) ? trim($_REQUEST['reason']) : '';

        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');

        //内勤、外勤、是否需要拍照上传
        $sign_info = $_sign->where("ss_id = {$ss_id}")->find();
        $sign_data['sr_outside'] = $sign_info['ss_outside'];
        if ($sign_info['ss_need_photo'] == '1') {
            $sign_data['sr_photo'] = trim($_REQUEST['photo']);
        } else {
            $sign_data['sr_photo'] = '';
        }
        if ($step == '5') {
            $date = date("Y-m-d");
            $SQL1 = "INSERT INTO oa_sign_record (sr_company_id, sr_sign_id, sr_partment_id, sr_user_id, sr_date, sr_step, sr_time, sr_status, sr_lon, sr_lat, sr_distance, sr_token_id, sr_reason, sr_photo) SELECT user_company_id, {$ss_id}, user_partment_id, user_id, '{$date}', 5, {$sign_data['sr_time']}, {$sign_data['sr_status']}, {$sign_data['sr_lon']}, {$sign_data['sr_lat']}, {$sign_data['sr_distance']}, '{$sign_data['sr_token_id']}', '{$sign_data['sr_reason']}', '{$sign_data['sr_photo']}' FROM oa_users WHERE user_id = {$user_id}";
            $_sign_record->query($SQL1);
            //echo json_encode($SQL1);

            //插入加班签退初始数据
            $SQL2 = "INSERT INTO oa_sign_record (sr_company_id, sr_sign_id, sr_partment_id, sr_user_id, sr_date, sr_step) SELECT user_company_id, {$ss_id}, user_partment_id, user_id, '{$date}', 6 FROM oa_users WHERE user_id = {$user_id}";
            //echo json_encode($SQL2);
            $_sign_record->query($SQL2);
        } else {
            $rs = $_sign_record->where("sr_id = {$sr_id}")->save($sign_data);
        }
        //echo $_sign_record->getlastsql();
        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '签到失败',
                'errorSql' => $_sign_record->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '签到成功',
            );
        }
        echo json_encode($_r);
    }

    /*****  考勤统计  *****/
    //考勤步骤列表
    public function getStepList()
    {
        $ss_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '0';
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        if ($partment_id != '0') {
            $company_id = trim($_REQUEST['company']);
            //子孙部门
            $childs = $this->getChildsByPartment($company_id, $partment_id);
            $childs[] = $partment_id;
            $partment_str = implode(',', $childs);
            //判断该部门及其子孙部门所属考勤是否有一天两次上下班
            $check_twice = $_sign->field("MAX(ss_sign_twice) as is_twice")->join("oa_sign_partments on ss_id = sp_set_id")->where("sp_partment_id in ({$partment_str})")->find();
            if (empty($check_twice)) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '指定部门没有设置考勤',
                );
                echo json_encode($_r);
                exit;
            } else {
                $is_twice = $check_twice['is_twice'];
            }
        }
        if ($ss_id != '0') {

            $is_twice = $_sign->where("ss_id = {$ss_id}")->getfield("ss_sign_twice");
        }
        if ($is_twice == '1') {
            $list = array(
                '1' => '上午上班',
                '2' => '上午下班',
                '3' => '下午上班',
                '4' => '下午下班',
                '5' => '加班签到',
                '6' => '加班签退',
            );
        } else {
            $list = array(
                '1' => '上班',
                '4' => '下班',
                '5' => '加班签到',
                '6' => '加班签退',
            );
        }
        $_r = array(
            'errorCode' => '1',
            'errorName' => '查询成功',
            'list' => $list,
        );
        echo json_encode($_r);
    }

    //月份列表
    public function getMonthList()
    {
        $ss_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '0';
        $_sign = M('sign_set', 'oa_', 'DB_CONFIG_OA');

        if ($partment_id != '0') {
            $company_id = trim($_REQUEST['company']);
            //子孙部门
            $childs = $this->getChildsByPartment($company_id, $partment_id);
            $childs[] = $partment_id;
            $partment_str = implode(',', $childs);
            //查询该部门及其子孙部门所属考勤的最早设置时间
            $create_time = $_sign->field("MIN(ss_create_time) as min_time")->join("oa_sign_partments on ss_id = sp_set_id")->where("sp_partment_id in ({$partment_str})")->find();
            if (empty($create_time)) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '指定部门没有设置考勤',
                );
            } else {
                $year = date('Y', $create_time['min_time']);
                $month = date('m', $create_time['min_time']);
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'year' => $year,
                    'month' => $month,
                );
            }
        }
        if ($ss_id != '0') {
            $create_time = $_sign->where("ss_id = {$ss_id}")->getfield("ss_create_time");
            $year = date('Y', $create_time);
            $month = date('m', $create_time);
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'year' => $year,
                'month' => $month,
            );
        }
        echo json_encode($_r);
    }


    //查看指定部门、考勤设置的签到记录统计
    public function signStatistic()
    {
        $company_id = trim($_REQUEST['company']);
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '0';
        $ss_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        //$user_id	 = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '0';
        $step = isset($_REQUEST['step']) ? trim($_REQUEST['step']) : '1';
        $year = !empty($_REQUEST['year']) ? trim($_REQUEST['year']) : date('Y');
        $month = !empty($_REQUEST['month']) ? intval(trim($_REQUEST['month'])) : intval(date('m'));

        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_sign_set = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_sign_user = M('sign_users', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');

        if ($month < 12) {
            $month_end = $month + 1;
            $year_end = $year;
            if ($month < '10') {
                $month = '0' . $month;
            }
            if ($month_end < '10') {
                $month_end = '0' . $month_end;
            }
        } else if ($month == '12') {
            $year_end = $year + 1;
            $month_end = '01';
        }

        $date_begin = $year . "-" . $month . "-01";
        $date_end = date('Y-m-d', (strtotime($year_end . "-" . $month_end . "-01") - 1));

        $SQL = "SELECT sr_status, count(*) as cnt FROM oa_sign_record WHERE sr_date BETWEEN '{$date_begin}' AND '{$date_end}' AND sr_step = {$step}";

        //按部门查看考勤统计
        if ($partment_id != '0') {
            //子孙部门
            $childs = $this->getChildsByPartment($company_id, $partment_id);
            $childs[] = $partment_id;
            $partment_str = implode(',', $childs);

            $SQL .= " AND sr_partment_id in ({$partment_str})";
        }
        //按考勤查看考勤统计
        if ($ss_id != '0') {
            $SQL .= " AND sr_sign_id = {$ss_id}";
        }
        $SQL .= " GROUP BY sr_status ORDER BY sr_status";
        $list = $_sign_record->query($SQL);

        if ($step == '1' || $step == '3') {//上班
            $rs_list = array(
                array('status' => '1', 'list_name' => '准时', 'count' => '0'),
                array('status' => '2', 'list_name' => '迟到', 'count' => '0'),
                array('status' => '4', 'list_name' => '范围外', 'count' => '0'),
                array('status' => '0', 'list_name' => '未签到', 'count' => '0')
            );
        } else if ($step == '2' || $step == '4') {//下班
            $rs_list = array(
                array('status' => '1', 'list_name' => '准时', 'count' => '0'),
                array('status' => '3', 'list_name' => '早退', 'count' => '0'),
                array('status' => '4', 'list_name' => '范围外', 'count' => '0'),
                array('status' => '0', 'list_name' => '未签退', 'count' => '0')
            );
        } else if ($step == '5' || $step == '6') {//加班
            $rs_list = array(
                array('status' => '1', 'list_name' => '范围内', 'count' => '0'),
                array('status' => '4', 'list_name' => '范围外', 'count' => '0'),
                array('status' => '0', 'list_name' => '加班未签退', 'count' => '0')
            );
        }

        foreach ($list as $k => $v) {
            switch ($v['sr_status']) {
                case '1':
                    $rs_list['0']['count'] = $v['cnt'];
                    break;
                case '2':
                    $rs_list['1']['count'] = $v['cnt'];
                    break;
                case '3':
                    $rs_list['1']['count'] = $v['cnt'];
                    break;
                case '4':
                    $rs_list['2']['count'] = $v['cnt'];
                    break;
                case '0':
                    if ($step == '5' || $step == '6') {
                        $rs_list['2']['count'] = $v['cnt'];
                    } else {
                        $rs_list['3']['count'] = $v['cnt'];
                    }
                    break;
            }
        }
        $_r = array(
            'errorCode' => '1',
            'errorName' => '查询成功',
            'list' => $rs_list,
            //'sql' => $_sign_record->getlastsql()
        );
        echo json_encode($_r);
    }

    //签到状态详细人员列表
    public function getStepUserList()
    {
        $company_id = trim($_REQUEST['company']);
        $status = trim($_REQUEST['status']);
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '0';
        $ss_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        //$user_id	 = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '0';
        $step = isset($_REQUEST['step']) ? trim($_REQUEST['step']) : '1';
        $year = isset($_REQUEST['year']) ? trim($_REQUEST['year']) : date('Y');
        $month = isset($_REQUEST['month']) ? intval(trim($_REQUEST['month'])) : intval(date('m'));

        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_sign_set = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_sign_user = M('sign_users', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');

        if ($month < 12) {
            $month_end = $month + 1;
            $year_end = $year;
            if ($month < '10') {
                $month = '0' . $month;
            }
            if ($month_end < '10') {
                $month_end = '0' . $month_end;
            }
        } else if ($month == '12') {
            $year_end = $year + 1;
            $month_end = '01';
        }

        $date_begin = $year . "-" . $month . "-01";
        $date_end = date('Y-m-d', (strtotime($year_end . "-" . $month_end . "-01") - 1));

        $SQL = "SELECT sr_user_id, user_name, user_face, partment_name, position_name, count(*) as cnt FROM oa_sign_record LEFT JOIN oa_users ON user_id = sr_user_id LEFT JOIN oa_partments ON partment_id = user_partment_id LEFT JOIN oa_positions ON position_id = user_position_id WHERE sr_date BETWEEN '{$date_begin}' AND '{$date_end}' AND sr_step = {$step} AND sr_status = {$status}";
        if ($partment_id != '0') {
            //子孙部门
            $childs = $this->getChildsByPartment($company_id, $partment_id);
            $childs[] = $partment_id;
            $partment_str = implode(',', $childs);
            //按部门查看考勤统计
            $SQL .= " AND sr_partment_id in ({$partment_str})";
        }
        if ($ss_id != '0') {
            //按考勤查看考勤统计
            $SQL .= " AND sr_sign_id = {$ss_id}";
        }
        $SQL .= " GROUP BY sr_user_id ORDER BY cnt DESC";
        $list = $_sign_record->query($SQL);

        $_r = array(
            'errorCode' => '1',
            'errorName' => '查询成功',
            'list' => $list,
        );
        echo json_encode($_r);
    }

    //指定员工详细签到状态日期列表
    public function getUserDateList()
    {
        $user_id = trim($_REQUEST['sr_user_id']);//要查看的用户id
        $status = trim($_REQUEST['status']);
        $step = isset($_REQUEST['step']) ? trim($_REQUEST['step']) : '1';
        $year = isset($_REQUEST['year']) ? trim($_REQUEST['year']) : date('Y');
        $month = isset($_REQUEST['month']) ? intval(trim($_REQUEST['month'])) : intval(date('m'));

        if ($month < 12) {
            $month_end = $month + 1;
            $year_end = $year;
            if ($month < '10') {
                $month = '0' . $month;
            }
            if ($month_end < '10') {
                $month_end = '0' . $month_end;
            }
        } else if ($month == '12') {
            $year_end = $year + 1;
            $month_end = '01';
        }

        $date_begin = $year . "-" . $month . "-01";
        $date_end = date('Y-m-d', (strtotime($year_end . "-" . $month_end . "-01") - 1));
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');
        $date_list = $_sign_record->field("sr_date")->where("sr_user_id = {$user_id} and sr_step = {$step} and sr_status = {$status} and sr_date between '{$date_begin}' and '{$date_end}'")->order("sr_date desc")->select();

        foreach ($date_list as $k => $v) {
            $date_list[$k]['sign_record'] = $_sign_record->field("sr_step, sr_time, sr_status, sr_reason, sr_photo")->where("sr_user_id = {$user_id} and sr_date = '" . $v['sr_date'] . "'")->order("sr_step asc")->select();
            foreach ($date_list[$k]['sign_record'] as $key => $value) {
                switch ($value['sr_step']) {
                    case '1':
                        $date_list[$k]['sign_record'][$key]['step'] = '上午上班';
                        break;
                    case '2':
                        $date_list[$k]['sign_record'][$key]['step'] = '上午下班';
                        break;
                    case '3':
                        $date_list[$k]['sign_record'][$key]['step'] = '下午上班';
                        break;
                    case '4':
                        $date_list[$k]['sign_record'][$key]['step'] = '下午下班';
                        break;
                    case '5':
                        $date_list[$k]['sign_record'][$key]['step'] = '加班签到';
                        break;
                    case '6':
                        $date_list[$k]['sign_record'][$key]['step'] = '加班签退';
                        break;
                }
                switch ($value['sr_status']) {
                    case '0':
                        if ($value['sr_step'] == '1' || $value['sr_step'] == '3') {
                            $date_list[$k]['sign_record'][$key]['status'] = '未签到';
                        } else if ($value['sr_step'] == '2' || $value['sr_step'] == '4') {
                            $date_list[$k]['sign_record'][$key]['status'] = '未签退';
                        }
                        break;
                    case '1':
                        $date_list[$k]['sign_record'][$key]['status'] = '准时';
                        break;
                    case '2':
                        $date_list[$k]['sign_record'][$key]['status'] = '迟到';
                        break;
                    case '3':
                        $date_list[$k]['sign_record'][$key]['status'] = '早退';
                        break;
                    case '4':
                        $date_list[$k]['sign_record'][$key]['status'] = '范围外';
                        break;
                }
                $date_list[$k]['sign_record'][$key]['time'] = empty($value['sr_time']) ? '-' : date("H:i", $value['sr_time']);
                $date_list[$k]['sign_record'][$key]['sr_reason'] = empty($value['sr_reason']) ? '' : $value['sr_reason'];
                $date_list[$k]['sign_record'][$key]['sr_photo'] = empty($value['sr_photo']) ? '' : $value['sr_photo'];
            }
        }
        $_r = array(
            'errorCode' => '1',
            'errorName' => '查询成功',
            'list' => $date_list,
        );
        echo json_encode($_r);
    }

    /*****  PC后台统计 ****/
    //考勤报表
    public function signReport()
    {
        $company_id = trim($_REQUEST['company']);
        $partment_id = isset($_REQUEST['partment']) ? trim($_REQUEST['partment']) : '0';
        $ss_id = isset($_REQUEST['ss_id']) ? trim($_REQUEST['ss_id']) : '0';
        //$user_id	 = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '0';
        $year = isset($_REQUEST['year']) ? trim($_REQUEST['year']) : date('Y');
        $month = isset($_REQUEST['month']) ? intval(trim($_REQUEST['month'])) : intval(date('m'));

        $_user = M('users', 'oa_', 'DB_CONFIG_OA');
        $_sign_set = M('sign_set', 'oa_', 'DB_CONFIG_OA');
        $_sign_user = M('sign_users', 'oa_', 'DB_CONFIG_OA');
        $_sign_record = M('sign_record', 'oa_', 'DB_CONFIG_OA');

        if ($month < 12) {
            $month_end = $month + 1;
            $year_end = $year;
            if ($month < '10') {
                $month = '0' . $month;
            }
            if ($month_end < '10') {
                $month_end = '0' . $month_end;
            }
        } else if ($month == '12') {
            $year_end = $year + 1;
            $month_end = '01';
        }

        $date_begin = $year . "-" . $month . "-01";
        $date_end = date('Y-m-d', (strtotime($year_end . "-" . $month_end . "-01") - 1));

        $SQL = "SELECT sr_user_id, user_name, partment_name, position_name, sr_id, sr_date, sr_std_time, sr_time, sr_step, sr_status, sr_distance, sr_outside, sr_reason, sr_photo FROM oa_sign_record LEFT JOIN oa_users ON user_id = sr_user_id LEFT JOIN oa_partments ON partment_id = user_partment_id LEFT JOIN oa_positions ON position_id = user_position_id WHERE sr_company_id = {$company_id} and sr_date BETWEEN '{$date_begin}' AND '{$date_end}'";
        if ($partment_id != '0') {
            //子孙部门
            $childs = $this->getChildsByPartment($company_id, $partment_id);
            $childs[] = $partment_id;
            $partment_str = implode(',', $childs);
            //按部门查看考勤统计
            $SQL .= " AND sr_partment_id in ({$partment_str})";
        }
        if ($ss_id != '0') {
            //按考勤查看考勤统计
            $SQL .= " AND sr_sign_id = {$ss_id}";
        }
        $SQL .= " order by sr_user_id, sr_date, sr_step";
        $list = $_sign_record->query($SQL);
        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询失败',
                'errorSql' => $_sign_record->getlastsql(),
            );
        } else if (empty($list)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '暂无数据',
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'list' => $list,
                'errorSql' => $_sign_record->getlastsql(),
            );
        }
        echo json_encode($_r);
    }



    /*****  公共函数  *****/

    // 构造并返回部门树
    function &_tree($partments)
    {
        import('@.ORG.tree');
        $tree = new Tree();
        $tree->setTree($partments, 'partment_id', 'partment_parent_id', 'partment_name');
        return $tree;
    }

    //通过部门id获取所有子孙部门id
    function getChildsByPartment($company_id, $partment_id)
    {
        $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
        $partment_list = $_partment->where("partment_company_id = {$company_id}")->select();
        foreach ($partment_list as $k => $v) {
            $partment_for_tree[$v['partment_id']] = $v;
        }
        $tree =& $this->_tree($partment_for_tree);

        $partment_ids = $tree->getChilds($partment_id);

        return $partment_ids;
    }

    //通过部门id获取所有祖先部门
    function getParentByPartment($company_id, $partment_id)
    {
        $_partment = M('partments', 'oa_', 'DB_CONFIG_OA');
        $partment_list = $_partment->where("partment_company_id = {$company_id}")->select();
        foreach ($partment_list as $k => $v) {
            $partment_for_tree[$v['partment_id']] = $v;
        }
        $tree =& $this->_tree($partment_for_tree);

        $partment_ids = $tree->getParents($partment_id);

        return $partment_ids;
    }

}