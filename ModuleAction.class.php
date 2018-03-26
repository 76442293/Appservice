<?php
/**
 * ModuleAction.class.php
 * 模块相关接口
 * yfb 2017-12-10
 */
import("@.Action.BaseAction");

class ModuleAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据模块ID修改模块，模块ID为0则添加新模块
     */
    public function editWfModule()
    {
        $wm_id = isset($_REQUEST['wm_id']) ? $_REQUEST['wm_id'] : '0';
        $wm_data = array();
        $wm_data['wm_name'] = $_REQUEST['wm_name'];
        $wm_data['wm_company'] = $_REQUEST['wm_company'];
        $wm_data['wm_users'] = isset($_REQUEST['wm_users']) ? $_REQUEST['wm_users'] : '';//未指定管理人员，默认为空，即所有user_type=1的超级管理员
        $wm_data['wm_partments'] = isset($_REQUEST['wm_partments']) ? $_REQUEST['wm_partments'] : '0';//不传默认为0，全部部门
        $wm_data['wm_icon'] = $_REQUEST['wm_icon'];
        $wm_data['wm_tpl'] = $_REQUEST['wm_tpl'];
        $wm_data['wm_abled'] = $_REQUEST['wm_abled'];
        $wm_data['wm_type'] = $_REQUEST['wm_type'];

        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');
        if ($wm_data['wm_abled'] == '1') {
            //如果启用模块，记录启用时间
            $wm_data['wm_start_time'] = date("Y-m-d H:i:s");
        }
        if ($wm_id == '0') {
            //新增模块
            $wm_data['wm_create_time'] = date("Y-m-d H:i:s");//模块创建时间
            $rs = $_wf_module->add($wm_data);
        } else {
            //修改模块
            $rs = $_wf_module->where("wm_id = {$wm_id}")->save($wm_data);
        }


        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_module->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 取得模块列表并根据公司ID取得每个模块管理人姓名和适用部门名称
     */
    public function listWfModule()
    {
        $wm_company = isset($_REQUEST['wm_company']) ? $_REQUEST['wm_company'] : '0';

        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');

        if (!empty($wm_company)) {
            $where = " wm_company = {$wm_company}";
        } else {
            $where = " 1=1";
        }

        $list = $_wf_module->field("oa_wf_module.*, company_name")->join("oa_companys on company_id = wm_company")->where($where)->select();
        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_module->getlastsql(),
            );
        } else {
            if (empty($list)) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '没有数据',
                );
            } else {
                $_user = M("users", "oa_", 'DB_CONFIG_OA');
                $_partment = M("partments", "oa_", 'DB_CONFIG_OA');
                foreach ($list as $k => $v) {
                    //查询管理人姓名
                    $admin_names = '';
                    if (empty($v['wm_users'])) {
                        //未指定管理人，查询所有user_type=1的超级管理员
                        $admin_list = $_user->field("user_id, user_name")->where("user_company_id = {$wm_company} and user_type = 1")->select();
                    } else {
                        $admin_ids = str_replace("_", ",", $v['wm_users']);
                        $admin_list = $_user->field("user_id, user_name")->where("user_company_id = {$wm_company} and user_id in ({$admin_ids})")->select();
                    }
                    foreach ($admin_list as $al) {
                        $admin_names = empty($admin_names) ? $al['user_name'] : $admin_names . '、' . $al['user_name'];
                    }
                    $list[$k]['admin_names'] = $admin_names;

                    //查询适用部门名称
                    $partment_names = '';
                    if (empty($v['wm_partments'])) {
                        $partment_names = '全体部门';
                    } else {
                        $partment_ids = str_replace("_", ",", $v['wm_partments']);
                        $partment_list = $_partment->where("partment_id in ({$partment_ids})")->select();
                        foreach ($partment_list as $pl) {
                            $partment_names = empty($partment_names) ? $pl['partment_name'] : $partment_names . '、' . $pl['partment_name'];
                        }
                    }
                    $list[$k]['partment_names'] = $partment_names;
                }
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $list,
                );
            }
        }

//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 根据模块ID和公司ID取得模块详情，未指定管理人的指定管理人为超级管理员,无适用部门的指定所有部门
     */
    public function detailWfModule()
    {
        $wm_company = isset($_REQUEST['wm_company']) ? $_REQUEST['wm_company'] : '0';
        $wm_id = $_REQUEST['wm_id'];

        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');

        $detail = $_wf_module->field("oa_wf_module.*, company_name")->join("oa_companys on company_id = wm_company")->where("wm_id = {$wm_id}")->find();
        if ($detail === false || empty($detail)) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_module->getlastsql(),
            );
        } else {
            //查询管理人姓名
            $admin_names = '';

            $_user = M("users", "oa_", 'DB_CONFIG_OA');
            $_partment = M("partments", "oa_", 'DB_CONFIG_OA');

            if (empty($detail['wm_users'])) {
                //未指定管理人，查询所有user_type=1的超级管理员
                $admin_list = $_user->field("user_id, user_name")->where("user_company_id = {$wm_company} and user_type = 1")->select();
            } else {
                $admin_ids = str_replace("_", ",", $detail['wm_users']);
                $admin_list = $_user->field("user_id, user_name")->where("user_company_id = {$wm_company} and user_id in ({$admin_ids})")->select();
            }
            foreach ($admin_list as $al) {
                $admin_names = empty($admin_names) ? $al['user_name'] : $admin_names . '、' . $al['user_name'];
            }
            $detail['admin_names'] = $admin_names;

            //查询适用部门名称
            $partment_names = '';
            if (empty($detail['wm_partments'])) {
                $partment_names = '全体部门';
            } else {
                $partment_ids = str_replace("_", ",", $detail['wm_partments']);
                $partment_list = $_partment->where("partment_id in ({$partment_ids})")->select();
                foreach ($partment_list as $pl) {
                    $partment_names = empty($partment_names) ? $pl['partment_name'] : $partment_names . '、' . $pl['partment_name'];
                }
            }
            $detail['partment_names'] = $partment_names;
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'detail' => $detail,
            );
        }
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 根据模块ID删除模块
     */
    public function delWfModule()
    {
        $wm_id = $_REQUEST['wm_id'];
        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');

        //判断是否有工作流归属于指定删除的模块
        $_wf = M("wf_workflow", "oa_", 'DB_CONFIG_OA');
        $check_wf = $_wf->where("wf_module = {$wm_id}")->find();
        if (!empty($check_wf)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '有归属于该模块的工作流，不可以删除',
            );
        } else {
            $rs = $_wf_module->where("wm_id = {$wm_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_module->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '删除成功',
                );
            }
        }
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }


    /**
     * 根据模块ID复用模块
     */
    public function copyModule()
    {
        $wm_id = isset($_REQUEST['wm_id']) ? $_REQUEST['wm_id'] : '0';
        $wm_company = isset($_REQUEST['wm_company']) ? $_REQUEST['wm_company'] : '0';


        if ($wm_id == 0) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wm_id参数缺少'
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r,JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');
        // 取得此模块信息
        $module = $_wf_module->field("*")->where("wm_id = {$wm_id}")->find();
        if ($module === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_module->getlastsql()
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r,JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        // 主键ID为0
        unset($module['wm_id']);
        // 所属公司为0
        $module['wm_comany'] = $wm_company;
        // 管理人为0
        $module['wm_users'] = 0;
        // 适用部门为0
        $module['wm_partments'] = 0;
        // 创建时间
        $module['wm_create_time'] = date("Y-m-d H:i:s");
        // 启用时间
        $module['wm_start_time'] = date("Y-m-d H:i:s");

        // 插入此模块
        $new_module_id = $_wf_module->add($module);

        // 查找此模块下的所有表单结构
        $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
        $forms = $_wf_forms->field("*")->where("wff_module = {$wm_id}")->select();

        $_wf_datalist = M("wf_datalist", "oa_", 'DB_CONFIG_OA');
        $_wf_statistics = M("wf_statistics", "oa_", 'DB_CONFIG_OA');
        $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');

        foreach ($forms as $key => $form) {
            $form_id = $form['wff_id'];
            // 主键ID
            unset($form['wff_id']);
            // 公司id
            $form['wff_company'] = $wm_company;
            // 所属模块为新的模块
            $form['wff_module'] = $new_module_id;

            // 插入此表单结构
            $new_form_id = $_wf_forms->add($form);

            // 查找此表单的所有列表管理配置
            $datalistList = $_wf_datalist->field("*")->where("wd_form = {$form_id}")->select();
            foreach ($datalistList as $k => $datalist) {
                // 主键ID
                unset($datalist['wd_id']);
                // 所属公司为0
                $datalist['wd_company'] = $wm_company;
                // 所属模块为新的模块
                $datalist['wd_module'] = $new_module_id;
                // 表单结构ID
                $datalist['wd_form'] = $new_form_id;
                // 创建时间
                $datalist['wd_create_time'] = date("Y-m-d H:i:s");

                // 插入此列表管理结构
                $_wf_datalist->add($datalist);

            }

            // 查找此表单的统计管理
            $statisticsList = $_wf_statistics->field("*")->where("ws_form = {$form_id}")->select();
            foreach ($statisticsList as $kk => $statistics) {
                // 主键ID
                unset($statistics['ws_id']);
                // 所属公司为0
                $statistics['ws_company'] = $wm_company;
                // 所属模块
                $statistics['ws_module'] = $new_module_id;
                // 所属表单结构
                $statistics['ws_form'] = $new_form_id;
                // 创建用户
                $statistics['ws_create_userid'] = 0;
                // 创建时间
                $statistics['ws_create_time'] = date("Y-m-d H:i:s");

                // 插入此统计配置
                $_wf_statistics->add($statistics);
            }

            // 查找此表单的工作流
            $workflow = $_wf_workflow->field("*")->where("wf_id = {$form['wff_workflow']}")->find();

            // 工作流ID
            unset($workflow['wf_id']);
            // 所属公司
            $workflow['wf_company'] = $wm_company;
            // 所属模块
            $workflow['wf_module'] = $new_module_id;
            // 创建时间
            $workflow['wf_create_time'] = date("Y-m-d H:i:s");
            // 启用时间
            $workflow['wf_start_time'] = date("Y-m-d H:i:s");

            $new_workflow_id = $_wf_workflow->add($workflow);


            // 更新新表单的工作流ID
            $forms_new = $_wf_forms->field("*")->where("wff_id = {$new_form_id}")->find();
            $forms_new['wff_workflow'] = $new_workflow_id;
            $forms_new['wff_create_time'] = date("Y-m-d H:i:s");
            $forms_new['wff_start_time'] = date("Y-m-d H:i:s");
            $_wf_forms->save($forms_new);

            // 工作流下的节点不复用
            // 原因1:无法指定审批人
            // 原因2:无法指定下一个审批节点的编号
        }


        $_r = array(
            'errorCode' => '1',
            'errorName' => '执行成功',
        );

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r,JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


}