<?php
/**
 * FlowAction.class.php
 * 工作主线相关接口
 * DaMingGe 2018-05-20
 */
import("@.Action.BaseAction");

class FlowAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加工作主线分类
     */
    public function createFlowType()
    {
        $flow_type_data = array();
        $flow_type_data['company_id'] = $_REQUEST['company_id'];
        $flow_type_data['flow_type_name'] = $_REQUEST['flow_type_name'];

        $_flow_type = M("flow_type", "oa_", 'DB_CONFIG_OA');

        $rs = $_flow_type->add($flow_type_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow_type->getlastsql(),
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
     * 编辑工作主线分类
     */
    public function editFlowType()
    {
        $flow_type_id = $_REQUEST['flow_type_id'];
        $flow_type_data = array();
//        $flow_type_data['company_id'] = $_REQUEST['company_id'];
        $flow_type_data['flow_type_name'] = $_REQUEST['flow_type_name'];

        $_flow_type = M("flow_type", "oa_", 'DB_CONFIG_OA');

        $rs = $_flow_type->where("flow_type_id = {$flow_type_id}")->save($flow_type_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow_type->getlastsql(),
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
     * 取得工作主线分类列表
     */
    public function getFlowTypeList()
    {

        $company_id = $_REQUEST['company_id'];

        $_flow_type = M("flow_type", "oa_", 'DB_CONFIG_OA');

        // 查询列表
        $list = $_flow_type->field("*")->where("company_id= {$company_id} ")->select();

        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow_type->getlastsql(),
            );
        } else {
            if (empty($list)) {
                // 数据为空
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '没有数据',
                );
            } else {
                // 查询成功
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
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     * 删除工作主线分类，并将该分类下的主线设置为默认分类
     */
    public function deleteFlowType()
    {
        $flow_type_id = $_REQUEST['flow_type_id'];
        $_flow_type = M("flow_type", "oa_", 'DB_CONFIG_OA');

        $rs = $_flow_type->where("flow_type_id = {$flow_type_id}")->delete();

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '删除失败',
                'errorSql' => $_flow_type->getlastsql(),
            );
        } else {

            // 将该分类下的主线设置为默认分类
            $_flow = M("flow", "oa_", 'DB_CONFIG_OA');

            $flow_data = array();
            $flow_data['flow_type_id'] = 0;
            $_flow->where("flow_type_id = {$flow_type_id}")->save($flow_data);

            $_r = array(
                'errorCode' => '1',
                'errorName' => '删除成功',
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
     * 新建主线
     */
    public function createFlow()
    {
        // 操作人用户ID
        $operator = $_REQUEST['operator_id'];

        // 模版ID
        $template_id = isset($_REQUEST['template_id']) ? $_REQUEST['template_id'] : '0';;

        $flow_data = array();
        $flow_data['flow_name'] = $_REQUEST['flow_name'];
        $flow_data['flow_type_id'] = $_REQUEST['flow_type_id'];
        $flow_data['company_id'] = $_REQUEST['company_id'];
        $flow_data['form_ids'] = $_REQUEST['form_ids'];
        $flow_data['director_user_ids'] = $_REQUEST['director_user_ids'];
        $flow_data['participant_user_ids'] = $_REQUEST['participant_user_ids'];
        $flow_data['partner_user_ids'] = $_REQUEST['partner_user_ids'];
        $flow_data['remind_type'] = $_REQUEST['remind_type'];

        //创建时间
        $flow_data['create_time'] = date("Y-m-d H:i:s");
        $flow_data['create_user'] = $operator;

        $_flow = M("flow", "oa_", 'DB_CONFIG_OA');

        $new_flow_id = $_flow->add($flow_data);

        // 如果使用模版
        if ($template_id != 0) {
            // 取得模版信息
            $_flow_template = M("flow_template", "oa_", 'DB_CONFIG_OA');
            $template = $_flow_template->where("template_id = {$template_id}")->find();

            if (isset($template)) {

                $_flow_template_node = M("flow_template_node", "oa_", 'DB_CONFIG_OA');

                // 取得模版关联的工作主线节点列表
                $template_node_list = $_flow_template_node->where("template_id = {$template_id}")->order("flow_node_id")->select();

                // 取得操作人所在公司ID
                $_users = M("users", "oa_", 'DB_CONFIG_OA');
                $user = $_users->field("user_company_id")->where("user_id = {$operator}")->find();

                $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

                // 二维数据,key为模版节点ID,value为新节点ID
                $node_id_arr = array();
                foreach ($template_node_list as $key => $template_node) {
                    // copy模版节点结构到新建工作主线节点结构
                    $flow_node_data = array();
                    $flow_node_data['flow_id'] = $new_flow_id;
                    $flow_node_data['flow_node_name'] = $template_node['tpl_node_name'];
                    if (isset($template_node['tpl_node_parent_id']) && $template_node['tpl_node_parent_id'] != 0) {
                        $flow_node_data['flow_node_parent_id'] = $node_id_arr[$template_node['tpl_node_parent_id']];
                    }

                    // 如果使用的模版所属为本公司
                    if ($template_node['company_id'] == $user['user_company_id']) {
                        // 套用节点负责人和参与人
                        $flow_node_data['director_user_ids'] = $template_node['director_user_ids'];
                        $flow_node_data['participant_user_ids'] = $template_node['participant_user_ids'];
                    }

                    $flow_node_data['create_time'] = date("Y-m-d H:i:s");
                    $flow_node_data['create_user'] = $operator;

                    $new_flow_node_id = $_flow_node->add($flow_node_data);

                    $node_id_arr[$template_node['tpl_node_id']] = $new_flow_node_id;

                }
            }
        }


        if ($new_flow_id === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow->getlastsql(),
            );
        } else {

            // 创建主线成功

            // 生成事件日志
            $user_ids = array();
            if ($_REQUEST['remind_type'] == 1) {
                // 除创建者以外,给其它负责人和参与人生成消息
                $user_ids = array_merge(array_merge($_REQUEST['director_user_ids'], $_REQUEST['participant_user_ids']), $_REQUEST['partner_user_ids']);

                // 去除重复的用户ID
                array_unique($user_ids);
            }
            $this->createLogMessage($operator, $new_flow_id, "新建主线", "", $user_ids);

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
     * 编辑主线
     */
    public function editFlow()
    {
        // 操作人用户ID
        $operator = $_REQUEST['operator_id'];
        // 工作主线ID
        $flow_id = $_REQUEST['flow_id'];

        $flow_data = array();
        if (isset($_REQUEST['flow_name'])) {
            $flow_data['flow_name'] = $_REQUEST['flow_name'];
        }

        if (isset($_REQUEST['flow_status'])) {
            $flow_data['flow_status'] = $_REQUEST['flow_status'];
        }

        if (isset($_REQUEST['flow_type_id'])) {
            $flow_data['flow_type_id'] = $_REQUEST['fle_ow_typid'];
        }

        if (isset($_REQUEST['form_ids'])) {
            $flow_data['form_ids'] = $_REQUEST['form_ids'];
        }

        if (isset($_REQUEST['director_user_ids'])) {
            $flow_data['director_user_ids'] = $_REQUEST['director_user_ids'];
        }

        if (isset($_REQUEST['participant_user_ids'])) {
            $flow_data['participant_user_ids'] = $_REQUEST['participant_user_ids'];
        }

        if (isset($_REQUEST['partner_user_ids'])) {
            $flow_data['partner_user_ids'] = $_REQUEST['partner_user_ids'];
        }

        if (isset($_REQUEST['remind_type'])) {
            $flow_data['remind_type'] = $_REQUEST['remind_type'];
        }

        //创建时间
        $flow_data['create_time'] = date("Y-m-d H:i:s");
        $flow_data['create_user'] = $operator;

        $_flow = M("flow", "oa_", 'DB_CONFIG_OA');

        $rs = $_flow->where("flow_id = {$flow_id}")->save($flow_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow->getlastsql(),
            );
        } else {

            // 编辑主线成功

            /*****编辑主线没有发送消息
             * $_flow_log = M("flow_log", "oa_", 'DB_CONFIG_OA');
             * // 取得此工作主线的日志主表信息
             * $flow_log = $_flow_log ->where("log_relate_data_name = oa_flow and log_relate_data_id = {$flow_id}")->find();
             *
             * // 生成事件日志明细
             * $flow_log_detail_data = array();
             * $flow_log_detail_data['log_detail_name'] = "更新主线";
             * // 编辑主线时,标题为空
             * $flow_log_detail_data['log_detail_title'] = "";
             * $flow_log_detail_data['log_operator'] = $operator;
             * $flow_log_detail_data['create_time'] = date("Y-m-d H:i:s");
             * $flow_log_detail_data['log_id'] = $flow_log['log_id'];
             *
             * $_flow_log_detail = M("flow_log_detail", "oa_", 'DB_CONFIG_OA');
             *
             * $detail_id = $_flow_log_detail->add($flow_log_detail_data);
             *
             * // 除创建者以外,给其它负责人和参与人生成消息
             * $user_ids = array();
             * if(isset($_REQUEST['director_user_ids'])){
             * array_merge($user_ids, $_REQUEST['director_user_ids']);
             * }
             *
             * if(isset($_REQUEST['participant_user_ids'])){
             * array_merge($user_ids, $_REQUEST['participant_user_ids']);
             * }
             *
             * if(isset($_REQUEST['partner_user_ids'])){
             * array_merge($user_ids, $_REQUEST['partner_user_ids']);
             * }
             *
             * // 去除重复的用户ID
             * array_unique($user_ids);
             *
             * $_flow_message = M("flow_message", "oa_", 'DB_CONFIG_OA');
             * foreach ($user_ids as $key => $user_id) {
             * if ($user_id != $operator) {
             * $flow_message_data = array();
             * $flow_message_data['receiver_id'] = $user_id;
             * $flow_message_data['log_detail_id'] = $detail_id;
             * $flow_message_data['is_read'] = 0;
             *
             * $_flow_message->add($flow_message_data);
             * }
             * }
             ******/

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
     * 新建工作主线节点
     */
    public function createFlowNode()
    {
        // 工作主线ID
        $flow_id = $_REQUEST['flow_id'];
        $operator = $_REQUEST['operator_id'];

        $flow_node_data = array();
        $flow_node_data['flow_id'] = $flow_id;
        $flow_node_data['flow_node_name'] = $_REQUEST['flow_node_name'];
        $flow_node_data['flow_node_parent_id'] = isset($_REQUEST['flow_node_parent_id']) ? $_REQUEST['flow_node_parent_id'] : 0;
        $flow_node_data['flow_node_content'] = $_REQUEST['flow_node_content'];
        $flow_node_data['flow_node_file'] = $_REQUEST['flow_node_file'];
//        $flow_node_data['director_user_ids'] = $_REQUEST['director_user_ids'];
        $flow_node_data['participant_user_ids'] = $_REQUEST['participant_user_ids'];
        $flow_node_data['partner_user_ids'] = $_REQUEST['partner_user_ids'];
        $flow_node_data['remind_type'] = $_REQUEST['remind_type'];
        $flow_node_data['is_can_see'] = $_REQUEST['is_can_see'];

        //创建时间
        $flow_node_data['create_time'] = date("Y-m-d H:i:s");
        $flow_node_data['create_user'] = $operator;

        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

        $rs = $_flow_node->add($flow_node_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow_node->getlastsql(),
            );
        } else {
            // 新建工作主线节点成功

            // 生成事件日志
            $user_ids = array();
            if ($_REQUEST['remind_type'] == 1) {
                // 除创建者以外,给其它负责人和参与人生成消息
                if (isset($_REQUEST['participant_user_ids'])) {
                    array_merge($user_ids, $_REQUEST['participant_user_ids']);
                }

                if (isset($_REQUEST['partner_user_ids'])) {
                    array_merge($user_ids, $_REQUEST['partner_user_ids']);
                }

                // 去除重复的用户ID
                array_unique($user_ids);
            }
            $this->createLogMessage($operator, $flow_id, "新建工作主线节点", "", $user_ids);

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
     * 编辑工作主线节点
     */
    public function editFlowNode()
    {
        // 工作主线ID
        $flow_node_id = $_REQUEST['flow_node_id'];
        $operator = $_REQUEST['operator_id'];

        $flow_node_data = array();

        if (isset($_REQUEST['flow_node_name'])) {
            $flow_node_data['flow_node_name'] = $_REQUEST['flow_node_name'];
        }

        if (isset($_REQUEST['flow_node_content'])) {
            $flow_node_data['flow_node_content'] = $_REQUEST['flow_node_content'];
        }

        if (isset($_REQUEST['flow_node_file'])) {
            $flow_node_data['flow_node_file'] = $_REQUEST['flow_node_file'];
        }

        if (isset($_REQUEST['participant_user_ids'])) {
            $flow_node_data['participant_user_ids'] = $_REQUEST['participant_user_ids'];
        }

        if (isset($_REQUEST['partner_user_ids'])) {
            $flow_node_data['partner_user_ids'] = $_REQUEST['partner_user_ids'];
        }

        if (isset($_REQUEST['remind_type'])) {
            $flow_node_data['remind_type'] = $_REQUEST['remind_type'];
        }

        if (isset($_REQUEST['is_can_see'])) {
            $flow_node_data['is_can_see'] = $_REQUEST['is_can_see'];
        }

        //创建时间
        $flow_node_data['create_time'] = date("Y-m-d H:i:s");
        $flow_node_data['create_user'] = $operator;

        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

        $rs = $_flow_node->where("flow_node_id = {$flow_node_id}")->save($flow_node_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow_node->getlastsql(),
            );
        } else {
            // 编辑工作主线节点成功

            // 取得最新的节点信息
            $flow_node = $_flow_node->where("flow_node_id = {$flow_node_id}")->find();

            // 生成操作日志并给相关人成生消息
            $user_ids = array();
            if ($_REQUEST['remind_type'] == 1) {
                // 除创建者以外,给参与人生成消息
                if (isset($_REQUEST['participant_user_ids'])) {
                    array_merge($user_ids, $_REQUEST['participant_user_ids']);
                }

                if (isset($_REQUEST['partner_user_ids'])) {
                    array_merge($user_ids, $_REQUEST['partner_user_ids']);
                }

                // 去除重复的用户ID
                array_unique($user_ids);
            }

            $this->createLogMessage($operator, $flow_node['flow_id'], "编辑工作主线节点", "", $user_ids);

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
     * 生成操作日志并给相关人成生消息
     * @param $operator
     * @param $flow_id
     * @param $log_detail_name
     * @param $log_detail_title
     * @param $user_ids
     */
    public function createLogMessage($operator, $flow_id, $log_detail_name, $log_detail_title, $user_ids)
    {

        $_flow_log = M("flow_log", "oa_", 'DB_CONFIG_OA');
        // 取得此工作主线的日志主表信息
        $flow_log = $_flow_log->where("log_relate_data_name = oa_flow and log_relate_data_id = {$flow_id}")->find();

        if (empty($flow_log)) {
            $flow_log_data = array();
            $flow_log_data['log_name'] = $_REQUEST['flow_name'];
            $flow_log_data['log_type'] = 1;
            $flow_log_data['log_relate_data_name'] = "oa_flow";
            $flow_log_data['log_relate_data_id'] = $flow_id;

            $_flow_log = M("flow_log", "oa_", 'DB_CONFIG_OA');

            $log_id = $_flow_log->add($flow_log_data);

        } else {
            $log_id = $flow_log['log_id'];
        }

        // 生成事件日志明细
        $flow_log_detail_data = array();
        $flow_log_detail_data['log_detail_name'] = $log_detail_name;
        // 标题为空
        $flow_log_detail_data['log_detail_title'] = $log_detail_title;
        $flow_log_detail_data['log_operator'] = $operator;
        $flow_log_detail_data['create_time'] = date("Y-m-d H:i:s");
        $flow_log_detail_data['log_id'] = $log_id;

        $_flow_log_detail = M("flow_log_detail", "oa_", 'DB_CONFIG_OA');

        $detail_id = $_flow_log_detail->add($flow_log_detail_data);

        // 除创建者以外,给参与人生成消息
        $_flow_message = M("flow_message", "oa_", 'DB_CONFIG_OA');
        foreach ($user_ids as $key => $user_id) {
            if ($user_id != $operator) {
                $flow_message_data = array();
                $flow_message_data['receiver_id'] = $user_id;
                $flow_message_data['log_detail_id'] = $detail_id;
                $flow_message_data['is_read'] = 0;

                $_flow_message->add($flow_message_data);
            }
        }
    }

}