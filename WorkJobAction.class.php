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

            // 此表单数据所属的表单结构ID
            $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
            $formData = $_wf_form_data->field("wfd_form,wfd_user_id")->where("wfd_id = {$wj_biz_id}")->find();

            // 表单结构所属的工作流ID
            $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
            $form = $_wf_forms->field("wff_workflow")->where("wff_id = {$formData['wfd_form']}")->find();

            // 此工作流下的节点列表
            $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');
            $node_list = $_wf_nodes->field("oa_wf_nodes.wn_id,oa_wf_nodes.wn_name,oa_wf_nodes.wn_node_type,workjob.wj_state" .
                ",workjob.wj_examine_result,workjob.wj_examine_opinion,workjob.wj_create_time,workjob.wj_update_time" .
                ",(select user.user_name from oa_users user where user.user_id = oa_wf_nodes.wn_user) as user_name" .
                ",(select user.user_face from oa_users user where user.user_id = oa_wf_nodes.wn_user) as user_face" .
                ",(CASE oa_wf_nodes.wn_node_type WHEN 1 THEN '系统开始节点' WHEN 2 THEN '人工处理节点' WHEN 3 THEN '系统自动节点' WHEN 4 THEN '系统结束节点' END) AS wn_node_type_name" .
                ",(CASE workjob.wj_examine_result WHEN 1 THEN '通过' WHEN 0 THEN '拒绝' ELSE '未处理' END) AS wj_examine_result_name")
                ->join("LEFT JOIN oa_wf_workjob workjob ON workjob.wj_node = oa_wf_nodes.wn_id AND workjob.wj_biz_id = {$wj_biz_id} ")
                ->where("oa_wf_nodes.wn_workflow = {$form['wff_workflow']}")->order("oa_wf_nodes.wn_step")->select();



            if ($node_list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_nodes->getlastsql(),
                );
            } else {

                // 将第一个节点的用户ID修改为提交审批的用户
                $_users = M("users", "oa_", 'DB_CONFIG_OA');
                $user = $_users->field("*")->where("user_id = {$formData['wfd_user_id']}")->find();

                $node_list[0]['user_name'] = $user['user_name'];
                $node_list[0]['user_face'] = $user['user_face'];

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $node_list,
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 根据工作业务表单ID取得此业务ID的工作任务列表
     */
    public function listWorkJobTemp()
    {

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