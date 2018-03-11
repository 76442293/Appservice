<?php
/**
 * WorkMessageAction.class.php
 * 工作流消息相关接口
 * DaMingGe 20180306
 */
import("@.Action.BaseAction");

class WorkMessageAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据消息ID修改消息，ID为0则添加新消息
     */
    public function editWorkMessage()
    {
        $wm_id = isset($_REQUEST['wm_id']) ? $_REQUEST['wm_id'] : '0';
        $wm_data = array();
        $wm_data['wm_info'] = $_REQUEST['wm_info'];
        $wm_data['wm_workjob_id'] = $_REQUEST['wm_workjob_id'];
        $wm_data['wm_function'] = $_REQUEST['wm_function'];
        $wm_data['wm_user_id'] = $_REQUEST['wm_user_id'];
        $wm_data['wm_is_open'] = $_REQUEST['wm_is_open'];
        $wm_data['wm_state'] = $_REQUEST['wm_state'];
        $wm_data['wm_create_time'] = $_REQUEST['wm_create_time'];
        $wm_data['wm_update_time'] = $_REQUEST['wm_update_time'];
        $wm_data['wm_remarks'] = $_REQUEST['wm_remarks'];
        $wm_data['wm_node_id'] = $_REQUEST['wm_node_id'];
        $wm_data['wm_biz_id'] = $_REQUEST['wm_biz_id'];
        $wm_data['wm_workflow_id'] = $_REQUEST['wm_workflow_id'];

        $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');
        if ($wm_id == '0') {
            //新增消息
            $rs = $_wf_message->add($wm_id);
        } else {
            //修改消息
            $rs = $_wf_message->where("wm_id = {$wm_id}")->save($wm_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_message->getlastsql(),
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
     * 根据业务单据ID取得此业务ID的消息列表(即流程图)
     */
    public function listWorkMessage()
    {
        $wm_biz_id = isset($_REQUEST['wm_biz_id']) ? $_REQUEST['wm_biz_id'] : '0';

        if ($wm_biz_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wm_biz_id参数为空',
            );
        } else {

            $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_message->field("*")->where("wm_biz_id = {$wm_biz_id}")->select();

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_message->getlastsql(),
                );
            } else {
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
     * 根据消息ID取得消息详情
     */
    public function detailWorkMessage()
    {
        $wm_id = isset($_REQUEST['wm_id']) ? $_REQUEST['wm_id'] : '0';

        if ($wm_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wm_id参数为空',
            );
        } else {
            $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');

            $detail = $_wf_message->field("*")->where("wm_id = {$wm_id}")->find();
            if ($detail === false || empty($detail)) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_message->getlastsql(),
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
     * 根据消息ID删除消息
     */
    public function deleteWorkMessage()
    {
        $wm_id = isset($_REQUEST['wm_id']) ? $_REQUEST['wm_id'] : '0';
        if ($wm_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wm_id参数为空',
            );
        } else {
            $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_message->where("wm_id = {$wm_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_message->getlastsql(),
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