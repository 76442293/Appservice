<?php
/**
 * WorkFlowAction.class.php
 * 工作流相关接口
 * DaMingGe 20180222
 */
import("@.Action.BaseAction");

class WorkFlowAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据工作流ID修改工作流，ID为0则添加新工作流
     */
    public function editWfWorkFlow()
    {
        $wf_id = isset($_REQUEST['wf_id']) ? $_REQUEST['wf_id'] : '0';
        $wf_data = array();
        $wf_data['wf_name'] = $_REQUEST['wf_name'];
        $wf_data['wf_company'] = $_REQUEST['wf_company'];
        $wf_data['wf_module'] = $_REQUEST['wf_module'];
        $wf_data['wf_abled'] = $_REQUEST['wf_abled'];
        $wf_data['wf_type'] = $_REQUEST['wf_type'];

        $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');
        if ($wf_data['wf_abled'] == '1') {
            //如果启用工作流，记录启用时间
            $wf_data['wf_start_time'] = date("Y-m-d H:i:s");
        }
        if ($wf_id == '0') {
            //新增工作流
            $wf_data['wf_create_time'] = date("Y-m-d H:i:s");//工作流创建时间
            $rs = $_wf_workflow->add($wf_data);
        } else {
            //修改工作流
            $rs = $_wf_workflow->where("wf_id = {$wf_id}")->save($wf_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_workflow->getlastsql(),
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
     * 取得工作流列表
     */
    public function listWfWorkFlow()
    {
        $wf_module = isset($_REQUEST['wf_module']) ? $_REQUEST['wf_module'] : '0';

        if ($wf_module == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wf_module参数为空',
            );
        } else {

            $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_workflow->field("*")->where("wf_module = {$wf_module}")->select();

            if ($list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_wf_workflow->getlastsql(),
                );
            } else {
                if (empty($list)) {
                    $_r = array(
                        'errorCode' => '2',
                        'errorName' => '没有数据',
                    );
                } else {
                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '查询成功',
                        'list' => $list,
                    );
                }
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
     * 根据工作流ID取得工作流详情
     */
    public function detailWfWorkFlow()
    {
        $wf_id = isset($_REQUEST['wf_id']) ? $_REQUEST['wf_id'] : '0';
        if ($wf_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wf_id参数为空',
            );
        } else {
            $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');

            $detail = $_wf_workflow->field("*")->where("wf_id = {$wf_id}")->find();
            if ($detail === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_workflow->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'detail' => $detail,
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
     * 根据工作流ID删除工作流
     */
    public function delWfWorkFlow()
    {
        $wf_id = $_REQUEST['wf_id'] ? $_REQUEST['wf_id'] : '0';
        if ($wf_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wf_id参数为空',
            );
        } else {
            $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_workflow->where("wf_id = {$wf_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_workflow->getlastsql(),
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

}