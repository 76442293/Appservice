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

        $where = '1=1';
        if ($wf_module != 0) {
            $where = $where . " and wf_module = {$wf_module}";
        }

        $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');

        $list = $_wf_workflow->field("*")->where($where)->select();

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

    /************************************************工作流驱动********************************************************/
    /**
     * 启动流程
     * @param $wf_id 工作流ID
     * @param $wj_biz_ids 业务ID(多个以逗号分隔)
     * @return bool
     */
    function start($wf_id, $wj_biz_ids)
    {
        $returnValue = true;
        $bizIdArray = explode(",", $wj_biz_ids);

        $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');
        foreach ($bizIdArray as $index => $bizId) {
            // 取得工作流信息
            $w_info = $_wf_workflow->field("*")->where("wf_id = {$wf_id}")->find();

//print_r($w_info);
//exit;

            // 开始执行节点处理
            $this->handler($w_info, $this->startNode($wf_id), $bizId);
        }

        return $returnValue;
    }

    /** 节点处理
     * @param $w_info 流程对像
     * @param $node 节点对像
     * @param $bizId 业务ID
     */
    function handler($w_info, $node, $bizId)
    {

        // 添加流程执行信息
        $workjob_id = $this->addInfo($node, $bizId);

        if ($node['wn_node_type'] == 1) {
            // 处理开始节点
//            print "处理开始节点\n";
            // 更新业务单据状态
            $this->updateState($bizId, 1);
            // 执行下一节点（回调本方法）
            $this->handler($w_info, $this->nextNode($node['wn_node_true']), $bizId);
        } else if ($node['wn_node_type'] == 3) {
            // 处理自动节点
//            print "处理自动节点\n";
            // 目前没有自动节点 预留

        } else if ($node['wn_node_type'] == 2) {
            //处理人工节点
//            print "处理人工节点\n";
            //推送人工审批通知
            $this->sendMessage($w_info, $node, $bizId, $workjob_id);
        } else if ($node['wn_node_type'] == 4) {
            //处理结束节点
//            print "处理结束节点\n";

            //更新业务单据状态
            $this->updateState($bizId, 2);

            // 根据积分计算规则，得出此次工作流最后积分 TODO

        }

    }

    /**
     * 开始节点
     * @param $wf_id 工作流ID
     * @return mixed 此工作流的开始节点
     */
    function startNode($wf_id)
    {
        $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');
        $node = $_wf_nodes->field("*")->where("wn_workflow = {$wf_id} and wn_node_type = 1")->find();

//        print $_wf_nodes->getLastSql();
//        print_r($node);
//        print "\n";

        return $node;
    }

    /**
     * 添加流程执行过程记录
     * @param $node 节点对象
     * @param $bizId 业务单据ID
     * @return mixed 插入数据后返回的自增长ID
     */
    function addInfo($node, $bizId)
    {
        $_wf_workjob = M("wf_workjob", "oa_", 'DB_CONFIG_OA');

        $wj_data = array();
        $wj_data['wj_job_name'] = $node['wn_name'];
        $wj_data['wj_biz_id'] = $bizId;
        $wj_data['wj_node'] = $node['wn_id'];
        $wj_data['wj_create_time'] = date("Y-m-d H:i:s");
        $wj_data['wj_user'] = $node['wn_user'];

        $count = $_wf_workjob->field("count(0) as step")->where("wj_biz_id = {$bizId}")->find();

//print_r($count);

        $wj_data['wj_step'] = $count['step'];

        $insertId = $_wf_workjob->add($wj_data);

        // 返回自增长主键
        return $insertId;
    }

    /**
     * 更新业务记录状态
     * @param $bizId 业务单据ID
     * @param $state_value 状态值
     */
    function updateState($bizId, $state_value)
    {
        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
        $form_data = $_wf_form_data->where("wfd_id = {$bizId}")->find();

        $form_data['wfd_state'] = $state_value;

        $_wf_form_data->where("wfd_id = {$bizId}")->save($form_data);

    }

    /**
     * 下一节点
     * @param $node_id 下一节点ID
     * @return mixed 下一节点对象
     */
    function nextNode($node_id)
    {
        $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');
        $node = $_wf_nodes->field("*")->where("wn_id = {$node_id}")->find();

        return $node;
    }

    /**
     * 人工审批处理
     * @param $wf_id 流程ID
     * @param $node_id 节点ID
     * @param $bizId 业务ID
     * @param $result 审批结果 0 未通过 1 通过
     */
    function handler_user($wf_id, $node_id, $bizId, $result)
    {
        // 取得工作流信息
        $_wf_workflow = M("wf_workflow", "oa_", 'DB_CONFIG_OA');
        $w_info = $_wf_workflow->field("*")->where("wf_id = {$wf_id}")->find();

        // 当前节点
        $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');
        $node = $_wf_nodes->field("*")->where("wn_id = {$node_id}")->find();

        //根据审批结果调用不同下一结点
        if ($result == 1) {
            $this->handler($w_info, $this->nextNode($node['wn_node_true']), $bizId);
        } else {
            $this->handler($w_info, $this->nextNode($node['wn_node_false']), $bizId);
        }

    }

    /**
     * 推送流程消息给处理人
     * @param $w_info 工作流对象
     * @param $node 节点对象
     * @param $bizId 业务单据ID
     * @param $workjob_id 工作任务ID
     */
    function sendMessage($w_info, $node, $bizId, $workjob_id)
    {
        $wm_data = array();
        $wm_data['wm_info'] = $w_info['wj_job_name'] . "[业务编号:" . $bizId . "]";
        $wm_data['wm_is_open'] = 0;
        $wm_data['wm_state'] = 0;
        $wm_data['wm_user_id'] = $node['wn_user'];
        $wm_data['wm_workflow_id'] = $w_info['wf_id'];
        $wm_data['wm_workjob_id'] = $workjob_id;
        $wm_data['wm_node_id'] = $node['wn_id'];
        $wm_data['wm_biz_id'] = $bizId;
        $wm_data['wm_create_time'] = date("Y-m-d H:i:s");

        $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');
        $_wf_message->add($wm_data);

//        print "推送流程消息给处理人\n";
//        print $_wf_message->getLastSql()."\n";

        // 使用UMeng推送app消息 TODO

    }

    /**
     * 启动工作流
     */
    public function startWorkflow()
    {
        $wf_id = $_REQUEST['wf_id'];
        $wj_biz_ids = $_REQUEST['wj_biz_ids'];

        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
        $fromData = $_wf_form_data->where("wfd_id = {$wj_biz_ids}")->find();
        if (empty($fromData) || $fromData['wfd_state'] != 0) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '业务单据状态不对,无法启动工作流'
            );
        } else {
            $rs = $this->start($wf_id, $wj_biz_ids);
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误'
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '执行成功',
                );
            }
        }

//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 处理人工审批结果
     */
    public function userWorkflow()
    {
        // 工作流ID
        $wf_id = $_REQUEST['wf_id'];
        // 节点ID
        $wn_id = $_REQUEST['wn_id'];
        // 业务单据ID
        $wj_biz_id = $_REQUEST['wj_biz_id'];
        // 审批结果
        $wj_examine_result = $_REQUEST['wj_examine_result'];
        // 审批意见
        $wj_examine_opinion = $_REQUEST['wj_examine_opinion'];
        // 消息ID
        $message_id = $_REQUEST['message_id'];
        // 工作任务ID
        $wj_id = $_REQUEST['wj_id'];

        $this->handler_user($wf_id, $wn_id, $wj_biz_id, $wj_examine_result);

        //更新消息状态
        $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');
        $wm_data = array();
        $wm_data['wm_state'] = 1;
        $_wf_message->where("wm_id = {$message_id}")->save($wm_data);

        // 更新工作任务记录信息
        $wj_data = array();
        $wj_data['wj_examine_result'] = $wj_examine_result;
        $wj_data['wj_examine_opinion'] = $wj_examine_opinion;
        $wj_data['wj_update_time'] = date("Y-m-d H:i:s");
        $_wf_workjob = M("wf_workjob", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_workjob->where("wj_id = {$wj_id}")->save($wj_data);

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
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     *  审核列表
     */
    public function userApproveList()
    {
        // 用户ID
        $wm_user_id = $_REQUEST['user_id'];
        // 模块ID
        $module_id = $_REQUEST['module_id'];
        // 创建时间开始
        $wm_create_time_start = $_REQUEST['wm_create_time_start'];
        // 创建时间结束
        $wm_create_time_end = $_REQUEST['wm_create_time_end'];
        // 审批状态
        $wm_state = $_REQUEST['wm_state'];
        // 公司ID
        $wf_company = $_REQUEST['company_id'];

        if (!isset($wf_company)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'company_id参数缺少',
            );
        } else {

            $where = " workflow.wf_company = {$wf_company} ";
            if (isset($wm_user_id)) {
                $where = $where . " and oa_wf_message.wm_user_id = {$wm_user_id}";
            }
            if (isset($module_id)) {
                $where = $where . " and workflow.wf_module = {$module_id}";
            }
            if (isset($wm_create_time_start)) {
                $where = $where . " and oa_wf_message.wm_create_time >= '{$wm_create_time_start}'";
            }
            if (isset($wm_create_time_end)) {
                $where = $where . " and oa_wf_message.wm_create_time < '{$wm_create_time_end}'";
            }
            if (isset($wm_state)) {
                $where = $where . " and oa_wf_message.wm_state = {$wm_state}";
            }

            $_wf_message = M("wf_message", "oa_", 'DB_CONFIG_OA');
            $message = $_wf_message->field("oa_wf_message.*,if(oa_wf_message.wm_state=1,'已审批','未审批') as wm_state_ch," .
                "(select u.user_name from oa_users u where u.user_id = oa_wf_message.wm_user_id) as user_name ")
                ->join("oa_wf_workflow workflow on workflow.wf_id = oa_wf_message.wm_workflow_id")
                ->where($where)->select();

            if ($message === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_message->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $message,
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


}