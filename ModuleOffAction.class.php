<?php
/**
 * ModuleOffAction.class.php
 * 模块个人开关相关接口
 * DaMingGe 2018-03-20
 */
import("@.Action.BaseAction");

class ModuleOffAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 开启模块
     */
    public function moduleOn()
    {
        // 模块ID
        $off_module_id = $_REQUEST['module_id'];
        // 用户ID
        $off_uid = $_REQUEST['uid'];

        if (!isset($off_module_id)) {
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

        if (!isset($off_uid)) {
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

        $_wf_module_off = M("wf_module_off", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_module_off->where("off_module_id = {$off_module_id} and off_uid = {$off_uid}")->delete();

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_module_off->getlastsql(),
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
     * 关闭模块
     */
    public function moduleOff()
    {
        // 模块ID
        $off_module_id = isset($_REQUEST['module_id']) ? $_REQUEST['module_id'] : '0';
        // 用户ID
        $off_uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';

        if (!isset($off_module_id)) {
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

        if (!isset($off_uid)) {
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

        $_wf_module_off = M("wf_module_off", "oa_", 'DB_CONFIG_OA');

        $module_off = $_wf_module_off->field("*")->where("off_module_id = {$off_module_id} and off_uid = {$off_uid}")->find();

        if (!empty($module_off)) {
            $rs = $module_off->where("off_module_id = {$off_module_id} and off_uid = {$off_uid}")->delete();

            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_wf_module_off->getlastsql(),
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
     * 取得所有模块的打开和关闭状态
     */
    public function listModuleOnOff()
    {
        // 用户ID
        $off_uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';

        if (!isset($off_uid)) {
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

        $_wf_module_off = M("wf_module_off", "oa_", 'DB_CONFIG_OA');
        $_wf_module = M("wf_module", "oa_", 'DB_CONFIG_OA');
        $_users = M("users", "oa_", 'DB_CONFIG_OA');

        // 用户所在公司ID
        $user = $_users->field("user_company_id")->where("user_id = {$off_uid}")->find();

        $wm_company = $user['user_company_id'];

        // 取出该用户所属部门的所有模块列表
        $list = $_wf_module->field("*")->where("wm_company = {$wm_company}")->select();

        // 取得该用户关闭的模块列表
        $module_off_list = $_wf_module_off->field("*")->where("off_uid = {$off_uid}")->select();
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
                    $list[$kk]['on'] = true;
                    foreach ($module_off_list as $k => $module_off) {
                        if ($module['wm_id'] == $module_off['off_module_id']) {
                            $list[$kk]['on'] = false;
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