<?php
/**
 * WorkJobAction.class.php
 * 工作任务相关接口
 * DaMingGe 20180226
 */
import("@.Action.BaseAction");

class WorkJobAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据任务ID修改工作任务，ID为0则添加新任务
     */
    public function editWorkJob()
    {
        $wj_id = isset($_REQUEST['wj_id']) ? $_REQUEST['wj_id'] : '0';
        $wj_data = array();
        $wj_data['wj_step'] = $_REQUEST['wj_step'];
        $wj_data['wj_job_name'] = $_REQUEST['wj_job_name'];
        $wj_data['wj_biz_id'] = $_REQUEST['wj_biz_id'];
        $wj_data['wj_node'] = $_REQUEST['wj_node'];
        $wj_data['wj_user'] = $_REQUEST['wj_user'];
        $wj_data['wj_examine_result'] = $_REQUEST['wj_examine_result'];
        $wj_data['wj_examine_opinion'] = $_REQUEST['wj_examine_opinion'];
        $wj_data['wj_remarks'] = $_REQUEST['wj_remarks'];
        $wj_data['wj_state'] = $_REQUEST['wj_state'];

        $_wf_workjob = M("wf_workjob", "oa_", 'DB_CONFIG_OA');
        if ($wj_id == '0') {
            //新增任务
            $rs = $_wf_workjob->add($wj_data);
        } else {
            //修改任务
            $rs = $_wf_workjob->where("wj_id = {$wj_id}")->save($wj_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_workjob->getlastsql(),
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
     * 根据工作业务表单ID取得此业务ID的工作任务列表
     */
    public function listWorkJob()
    {
        $wj_biz_id = isset($_REQUEST['wj_biz_id']) ? $_REQUEST['wj_biz_id'] : '0';

        if ($wj_biz_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wj_biz_id参数为空',
            );
        } else {

            $_wf_workjob = M("wf_workjob", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_workjob->field("*,(select user.user_name from oa_users user where user.user_id = oa_wf_workjob.wj_user) as user_name")->where("wj_biz_id = {$wj_biz_id}")->select();

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_workjob->getlastsql(),
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
            echo json_encode($_r,JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 根据工作任务ID取得任务详情
     */
    public function detailWorkJob()
    {
        $wj_id = isset($_REQUEST['wj_id']) ? $_REQUEST['wj_id'] : '0';

        if ($wj_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wj_id参数为空',
            );
        } else {
            $_wf_workjob = M("wf_workjob", "oa_", 'DB_CONFIG_OA');

            $detail = $_wf_workjob->field("*")->where("wj_id = {$wj_id}")->find();
            if ($detail === false || empty($detail)) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_workjob->getlastsql(),
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
     * 根据任务ID删除工作任务
     */
    public function deleteWorkJob()
    {
        $wj_id = isset($_REQUEST['wj_id']) ? $_REQUEST['wj_id'] : '0';
        if ($wj_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wj_id参数为空',
            );
        } else {
            $_wf_workjob = M("wf_workjob", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_workjob->where("wj_id = {$wj_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_workjob->getlastsql(),
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