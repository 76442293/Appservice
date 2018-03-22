<?php
/**
 * ModuleViewed.class.php
 * 模块个人查看状态相关接口
 * DaMingGe 2018-03-22
 */
import("@.Action.BaseAction");

class ModuleViewedAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 设置模块为未设看
     */
    public function setModuleNoViewed()
    {
        // 模块ID
        $mv_module_id = isset($_REQUEST['module_id']) ? $_REQUEST['module_id'] : '0';
        // 用户ID
        $mv_uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';

        if ($mv_module_id == 0) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'module_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        if ($mv_uid == 0) {
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'uid参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        $_wf_module_viewed = M("wf_module_viewed", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_module_viewed->where("mv_module_id = {$mv_module_id} and mv_uid = {$mv_uid}")->delete();

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_module_viewed->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }


        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 设置模块为已查看
     */
    public function setModuleViewed()
    {
        // 模块ID
        $mv_module_id = isset($_REQUEST['module_id']) ? $_REQUEST['module_id'] : '0';
        // 用户ID
        $mv_uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';

        if (!isset($mv_module_id)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'module_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        if (!isset($mv_uid)) {
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'uid参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        $_wf_module_viewed = M("wf_module_viewed", "oa_", 'DB_CONFIG_OA');

        $module_viewed = $_wf_module_viewed->field("*")->where("mv_module_id = {$mv_module_id} and mv_uid = {$mv_uid}")->find();

        if (!empty($module_viewed)) {
            $mv_data = array();
            $mv_data['mv_uid'] = $mv_uid;
            $mv_data['mv_module_id'] = $mv_module_id;
            $mv_data['mv_create_time'] = date("Y-m-d H:i:s");

            $rs = $_wf_module_viewed->add($mv_data);

            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_wf_module_viewed->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '执行成功',
                );
            }
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 取得所有模块的查看和未查看状态
     */
    public function listModuleViewed()
    {
        // 用户ID
        $mv_uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';

        if ($mv_uid == 0) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'uid参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        $_wf_module_viewed = M("wf_module_viewed", "oa_", 'DB_CONFIG_OA');
        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');
        $_users = M("users", "oa_", 'DB_CONFIG_OA');

        // 用户所在公司ID
        $user = $_users->field("user_company_id")->where("user_id = {$mv_uid}")->find();

        $wm_company = $user['user_company_id'];

        // 取出该用户所属部门的所有模块列表
        $list = $_wf_module->field("*")->where("wm_company = {$wm_company}")->select();

        // 取得该用户关闭的模块列表
        $module_viewed_list = $_wf_module_viewed->field("*")->where("mv_uid = {$mv_uid}")->select();
        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_module->getlastsql(),
            );
        } else {
            if (empty($list)) {
                $_r = array(
                    'errorCode' => '3',
                    'errorName' => '没有数据',
                );
            } else {

                foreach ($list as $kk => $module) {
                    $list[$kk]['viewed'] = true;
                    foreach ($module_viewed_list as $k => $module_viewed) {
                        if ($module['wm_id'] == $module_viewed['mv_module_id']) {
                            $list[$kk]['viewed'] = false;
                        }
                    }
                }

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $list,
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }


}