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
//        $flow_data['remind_type'] = $_REQUEST['remind_type'];

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

            /************************生成日志和消息相关Start**************************/
            // 生成事件日志
            if ($_REQUEST['remind_type'] == 1) {

                $this->createLogMessage($operator, "新建主线", "", 0, $new_flow_id);
            }

            /************************生成日志和消息相关End**************************/

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

//        if (isset($_REQUEST['remind_type'])) {
//            $flow_data['remind_type'] = $_REQUEST['remind_type'];
//        }

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
//        $flow_node_data['remind_type'] = $_REQUEST['remind_type'];
        $flow_node_data['is_can_see'] = $_REQUEST['is_can_see'];

        //创建时间
        $flow_node_data['create_time'] = date("Y-m-d H:i:s");
        $flow_node_data['create_user'] = $operator;

        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

        $flow_node_id_new = $_flow_node->add($flow_node_data);

        if ($flow_node_id_new === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow_node->getlastsql(),
            );
        } else {
            // 新建工作主线节点成功

            /************************生成日志和消息相关Start**************************/
            // 生成事件日志
            if ($_REQUEST['remind_type'] == 1) {

                $this->createLogMessage($operator, "新建工作主线节点", "", $flow_node_id_new, 0);
            }
            /************************生成日志和消息相关End**************************/

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

//        if (isset($_REQUEST['remind_type'])) {
//            $flow_node_data['remind_type'] = $_REQUEST['remind_type'];
//        }

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

            /************************生成日志和消息相关Start**************************/
            // 生成操作日志并给相关人成生消息
            if ($_REQUEST['remind_type'] == 1) {

                $this->createLogMessage($operator, "编辑工作主线节点", "", $flow_node_id, 0);
            }
            /************************生成日志和消息相关End**************************/

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
     * 取得工作主线信息和所属一级节点列表
     */
    public function getFlowNodeList()
    {

        // 主线ID
        $flow_id = $_REQUEST['flow_id'];
        // 当前用户ID
        $operator = $_REQUEST['operator_id'];

        $_flow = M("flow", "oa_", 'DB_CONFIG_OA');

        // 查询主线信息
        $flow = $_flow->field("*"
            . ",(select type.flow_type_name from oa_flow_type type where type.flow_type_id = oa_flow.flow_type_id) as flow_type_name"
            . ",(CASE flow_status WHEN 0 THEN '进行中' WHEN 1 THEN '已完成' END) as flow_status_ch"
            . ",(select GROUP_CONCAT(user.name) from oa_users user where find_in_set(user.user_id,oa_flow.director_user_ids)) as director_user_names"
            . ",(select GROUP_CONCAT(user.name) from oa_users user where find_in_set(user.user_id,oa_flow.participant_user_ids)) as participant_user_names"
            . ",(select company.company_name from oa_companys company where company.company_id = oa_flow.company_id) as company_name")
            ->where("flow_id = {$flow_id} and is_delete = 0 ")->find();

        if ($flow === false || empty($flow)) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误或者没有主线数据',
                'errorSql' => $_flow->getlastsql(),
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;

        }

        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

        // 全部节点列表
        $flow_node_list_all = $_flow_node->field("*")->where("flow_id = {$flow_id} and is_delete = 0 ")->find();

        // 主线负责人
        $director_user_ids = explode(",", $flow['director_user_ids']);
        // 主线参与人
        $participant_user_ids = explode(",", $flow['participant_user_ids']);

        $flow_node_list_result = array();
        if (in_array($operator, $director_user_ids) || $operator == $flow['create_user']) {
            // 如果当前用户是主线创建者或者负责人,取得全部节点列表
            $flow_node_list_result = $flow_node_list_all;

        } else {

            foreach ($flow_node_list_all as $key => $flow_node) {

                if (in_array($operator, explode(",", $flow_node['participant_user_ids']))) {
                    // 用户是此节点的参与人
                    array_push($flow_node_list_result, $flow_node);
                } elseif (in_array($operator, $participant_user_ids) && $flow_node['is_can_see'] == 1) {
                    // 用户是主线的参与人,且此节点允许主线参与人查看
                    array_push($flow_node_list_result, $flow_node);
                }
            }
        }

        // 查询成功
        $_r = array(
            'errorCode' => '1',
            'errorName' => '查询成功',
            'flow' => $flow,
            'flow_node_list' => $flow_node_list_result,
        );
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     * 删除工作主线节点
     */
    public function deleteFlowNode()
    {
        $flow_node_id = $_REQUEST['flow_node_id'];
        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

        $flow_node_data = array();
        $flow_node_data['is_delete'] = 1;

        $rs = $_flow_node->where("flow_node_id = {$flow_node_id}")->save($flow_node_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '删除失败',
                'errorSql' => $_flow_node->getlastsql(),
            );
        } else {

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
     * 生成操作日志并给相关人成生消息
     * @param $operator
     * @param $log_detail_name
     * @param $log_detail_title
     * @param $flow_node_id
     * @param $flow_id
     */
    public function createLogMessage($operator, $log_detail_name, $log_detail_title, $flow_node_id, $flow_id)
    {

        $user_ids = array();

        if ($flow_node_id != 0) {
            $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');
            // 取得最新的节点信息
            $flow_node = $_flow_node->where("flow_node_id = {$flow_node_id}")->find();

            // 主线节点的参与人
            array_merge($user_ids, explode(",", $flow_node['participant_user_ids']));
            // 主线节点的合作伙伴
            array_merge($user_ids, explode(",", $flow_node['partner_user_ids']));

            // 方法参数中有节点id时,不使用参数flow_id,使用查询出的flow_id
            $flow_id = $flow_node['flow_id'];

        }


        // 主线的负责人和参与人
        $_flow = M("flow", "oa_", 'DB_CONFIG_OA');
        $flow = $_flow->where("flow_id = {$flow_id}")->find();

        array_merge($user_ids, explode(",", $flow['director_user_ids']));
        array_merge($user_ids, explode(",", $flow['participant_user_ids']));
        array_merge($user_ids, explode(",", $flow['partner_user_ids']));
        // 主线的创建者
        array_merge($user_ids, $flow['create_user']);

        // 去除重复的用户ID
        array_unique($user_ids);

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

    /**
     * 新建工作主线节点附件
     */
    public function createFlowNodeFile()
    {
        // 通过上传接口上传文件后,返回上传的完整路径和关联的节点ID
        $file_name = $_REQUEST['file_name'];
        $file_url = $_REQUEST['file_url'];
        $file_remark = $_REQUEST['file_remark'];
        $flow_node_id = $_REQUEST['flow_node_id'];
        $create_user_id = $_REQUEST['operator_id'];
        $remind_type = $_REQUEST['remind_type'];

        // 生成文件上传记录
        $file_data = array();
        $file_data['file_name'] = $file_name;
        $file_data['file_url'] = $file_url;
        $file_data['file_remark'] = $file_remark;
        $file_data['create_time'] = date("Y-m-d H:i:s");
        $file_data['create_user_id'] = $create_user_id;

        $_file = M("file", "oa_", 'DB_CONFIG_OA');

        $file_id = $_file->add($file_data);

        if ($file_id === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_file->getlastsql(),
            );
        } else {
            // 生成数据记录
            $flowData_data = array();
            $flowData_data['flow_node_id'] = $flow_node_id;
            $flowData_data['data_name'] = $file_name;
            $flowData_data['data_title'] = $file_name;
            $flowData_data['data_type'] = 0;
            $flowData_data['relate_db_name'] = "oa_file";
            $flowData_data['relate_db_id'] = $file_id;
            $flowData_data['create_time'] = date("Y-m-d H:i:s");
            $flowData_data['create_user_id'] = $create_user_id;

            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');

            $_flow_data->add($flowData_data);

            /************************生成日志和消息相关Start**************************/

            // 生成操作日志并给相关人成生消息
            if ($remind_type == 1) {

                $this->createLogMessage($create_user_id, "新上传文件", $file_name, $flow_node_id, 0);
            }

            /************************生成日志和消息相关End**************************/

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
     * 编辑工作主线节点附件
     */
    public function editFlowNodeFile()
    {
        // 通过上传接口上传文件后,返回上传的完整路径和关联的节点ID
        $file_name = $_REQUEST['file_name'];
        $file_url = $_REQUEST['file_url'];
        $file_remark = $_REQUEST['file_remark'];
        $flow_node_id = $_REQUEST['flow_node_id'];
        $create_user_id = $_REQUEST['operator_id'];
//        $remind_type = $_REQUEST['remind_type'];

        $file_id = $_REQUEST['file_id'];


        // 生成文件上传记录
        $file_data = array();
        $file_data['file_name'] = $file_name;
        $file_data['file_url'] = $file_url;
        $file_data['file_remark'] = $file_remark;
        $file_data['create_time'] = date("Y-m-d H:i:s");
        $file_data['create_user_id'] = $create_user_id;

        $_file = M("file", "oa_", 'DB_CONFIG_OA');


        $rs = $_file->where("file_id = {$file_id}")->save($file_data);
//        $file_id = $_file->add($file_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_file->getlastsql(),
            );
        } else {
            // 生成数据记录
            $flowData_data = array();
            $flowData_data['flow_node_id'] = $flow_node_id;
            $flowData_data['data_name'] = $file_name;
            $flowData_data['data_title'] = $file_name;
            $flowData_data['data_type'] = 0;
//            $flowData_data['relate_db_name'] = "oa_file";
//            $flowData_data['relate_db_id'] = $file_id;
            $flowData_data['create_time'] = date("Y-m-d H:i:s");
            $flowData_data['create_user_id'] = $create_user_id;

            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');

            $_flow_data->where("relate_db_name = 'oa_file' and relate_db_id = {$file_id}")->save($flowData_data);

            // 编辑附件无需通知

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
     * 删除附件
     */
    public function deleteFlowNodeFile()
    {
        $file_id = $_REQUEST['file_id'];
//        $_file = M("file", "oa_", 'DB_CONFIG_OA');

        // 附件表的数据不删除
//        $rs = $_file->where("file_id = {$file_id}")->delete();


        // 删除文件对应的节点数据
        $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
        $flow_data = $_flow_data->where("relate_db_name = 'oa_file' and relate_db_id = {$file_id}")->find();
        $_flow_data->where("data_id = {$flow_data['data_id']}")->delete();

        // 删除该数据所有的评论  $flow_data['data_id']
        $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
        $_comment->where("relate_db_name = 'oa_flow_data' and relate_db_id = {$flow_data['data_id']}")->delete();

        $_r = array(
            'errorCode' => '1',
            'errorName' => '删除成功',
        );

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 取得附件信息及评论列表
     */
    public function getFlowNodeFile()
    {
        $file_id = $_REQUEST['file_id'];


        $_file = M("file", "oa_", 'DB_CONFIG_OA');
        $file = $_file->where("file_id = {$file_id}")->find();

        if ($file === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_file->getlastsql(),
            );
        } else {

            // 附件所属的数据
            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
            $flow_data = $_flow_data->where("relate_db_name = 'oa_file' and relate_db_id = {$file_id}")->find();

            // 取得该附件数据的评论列表
            $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
            // 查询列表
            $file_comment_list = $_comment->field("*")->where("relate_db_name = 'oa_flow_data' and relate_db_id = {$flow_data['data_id']} ")->select();

            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
                'file' => $file,
                'file_comment' => $file_comment_list,
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
     * 新建工作主线节点备忘
     */
    public function createFlowNodeMemo()
    {
        $flow_node_id = $_REQUEST['flow_node_id'];
        $remind_type = $_REQUEST['remind_type'];
        $create_user_id = $_REQUEST['operator_id'];

        $memo_data = array();
        $memo_data['memo_title'] = $_REQUEST['memo_title'];
        $memo_data['memo_content'] = $_REQUEST['memo_content'];
        $memo_data['memo_file_url'] = $_REQUEST['memo_file_url'];
        $memo_data['create_time'] = date("Y-m-d H:i:s");
        $memo_data['create_user_id'] = $create_user_id;

        $_memo = M("memo", "oa_", 'DB_CONFIG_OA');

        $memo_id = $_memo->add($memo_data);

        if ($memo_id === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_memo->getlastsql(),
            );
        } else {
            // 生成数据记录
            $flowData_data = array();
            $flowData_data['flow_node_id'] = $flow_node_id;
            $flowData_data['data_name'] = $memo_data['memo_title'];
            $flowData_data['data_title'] = $memo_data['memo_title'];
            $flowData_data['data_type'] = 1;
            $flowData_data['relate_db_name'] = "oa_memo";
            $flowData_data['relate_db_id'] = $memo_id;
            $flowData_data['create_time'] = date("Y-m-d H:i:s");
            $flowData_data['create_user_id'] = $create_user_id;

            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');

            $_flow_data->add($flowData_data);

            /************************生成日志和消息相关Start**************************/

            // 生成操作日志并给相关人成生消息
            if ($remind_type == 1) {

                $this->createLogMessage($create_user_id, "新建备忘", $memo_data['memo_title'], $flow_node_id, 0);
            }

            /************************生成日志和消息相关End**************************/

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
     * 编辑工作主线节点备忘
     */
    public function editFlowNodeMemo()
    {
//        $flow_node_id = $_REQUEST['flow_node_id'];
        $create_user_id = $_REQUEST['operator_id'];
        $memo_id = $_REQUEST['memo_id'];

        $memo_data = array();
        $memo_data['memo_title'] = $_REQUEST['memo_title'];
        $memo_data['memo_content'] = $_REQUEST['memo_content'];
        $memo_data['memo_file_url'] = $_REQUEST['memo_file_url'];
        $memo_data['create_time'] = date("Y-m-d H:i:s");
        $memo_data['create_user_id'] = $create_user_id;

        $_memo = M("memo", "oa_", 'DB_CONFIG_OA');

//        $memo_id = $_memo->add($memo_data);
        $rs = $_memo->where("memo_id = {$memo_id}")->save($memo_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_memo->getlastsql(),
            );
        } else {
            // 生成数据记录
            $flowData_data = array();
//            $flowData_data['flow_node_id'] = $flow_node_id;
            $flowData_data['data_name'] = $memo_data['memo_title'];
            $flowData_data['data_title'] = $memo_data['memo_title'];
//            $flowData_data['data_type'] = 1;
//            $flowData_data['relate_db_name'] = "oa_memo";
//            $flowData_data['relate_db_id'] = $memo_id;
            $flowData_data['create_time'] = date("Y-m-d H:i:s");
            $flowData_data['create_user_id'] = $create_user_id;

            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');

//            $_flow_data->add($flowData_data);
            $_flow_data->where("relate_db_name = 'oa_memo' and relate_db_id = {$memo_id}")->save($flowData_data);

            // 编辑备忘不发送通知

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
     * 删除备忘
     */
    public function deleteFlowNodeMemo()
    {
        $memo_id = $_REQUEST['memo_id'];
        $_memo = M("memo", "oa_", 'DB_CONFIG_OA');

        // 备忘表的数据
        $_memo->where("memo_id = {$memo_id}")->delete();

        // 删除文件对应的节点数据
        $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
        $flow_data = $_flow_data->where("relate_db_name = 'oa_memo' and relate_db_id = {$memo_id}")->find();
        $_flow_data->where("data_id = {$flow_data['data_id']}")->delete();

        // 删除该数据所有的评论  $flow_data['data_id']
        $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
        $_comment->where("relate_db_name = 'oa_flow_data' and relate_db_id = {$flow_data['data_id']}")->delete();

        $_r = array(
            'errorCode' => '1',
            'errorName' => '删除成功',
        );

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 取得备忘信息及评论列表
     */
    public function getFlowNodeMemo()
    {
        $memo_id = $_REQUEST['memo_id'];

        $_memo = M("memo", "oa_", 'DB_CONFIG_OA');
        $memo = $_memo->where("memo_id = {$memo_id}")->find();

        if ($memo === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_memo->getlastsql(),
            );
        } else {

            // 备忘所属的数据
            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
            $flow_data = $_flow_data->where("relate_db_name = 'oa_memo' and relate_db_id = {$memo_id}")->find();

            // 取得该数据的评论列表
            $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
            // 查询列表
            $memo_comment_list = $_comment->field("*")->where("relate_db_name = 'oa_flow_data' and relate_db_id = {$flow_data['data_id']} ")->select();

            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
                'memo' => $memo,
                'memo_comment' => $memo_comment_list,
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
     * 提交表单数据
     */
    public function createFlowNodeFormData()
    {

        $create_user_id = $_REQUEST['operator_id'];
        $form_id = $_REQUEST['form_id'];
        $data_json = $_REQUEST['data_json'];
        $flow_node_id = $_REQUEST['flow_node_id'];
        $remind_type = $_REQUEST['remind_type'];

        // 取得用户信息
        $_users = M("users", "oa_", 'DB_CONFIG_OA');
        $users = $_users->where("user_id = {$create_user_id}")->find();

        // 取得表单结构信息
        $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
        $wf_form = $_wf_forms->where("wff_id = {$form_id}")->find();

        $form_data = array();
        $form_data['wfd_company'] = $users['user_company_id'];
        $form_data['wfd_module'] = $wf_form['wff_module'];
        $form_data['wfd_workflow'] = $wf_form['wff_workflow'];
        $form_data['wfd_node'] = $wf_form['wff_node'];
        $form_data['wfd_form'] = $form_id;
        $form_data['wfd_data_json'] = $data_json;

        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
        //提交新表单数据
        $form_data['wfd_create_time'] = date("Y-m-d H:i:s");//模块创建时间
        $formData_id = $_wf_form_data->add($form_data);

        if ($formData_id === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_form_data->getlastsql(),
            );
        } else {
            // 生成数据记录
            $flowData_data = array();
            $flowData_data['flow_node_id'] = $flow_node_id;
            $flowData_data['data_name'] = $wf_form['wff_name_ch'];
            $flowData_data['data_title'] = $wf_form['wff_name_ch'];
            $flowData_data['data_type'] = 2;
            $flowData_data['relate_db_name'] = "oa_wf_form_data";
            $flowData_data['relate_db_id'] = $formData_id;
            $flowData_data['create_time'] = date("Y-m-d H:i:s");
            $flowData_data['create_user_id'] = $create_user_id;

            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');

            $_flow_data->add($flowData_data);

            /************************生成日志和消息相关Start**************************/

            // 生成操作日志并给相关人成生消息
            if ($remind_type == 1) {

                $this->createLogMessage($create_user_id, "新增表单", $wf_form['wff_name_ch'], $flow_node_id, 0);
            }

            /************************生成日志和消息相关End**************************/

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
     * 修改表单数据
     */
    public function editFlowNodeFormData()
    {
        $create_user_id = $_REQUEST['operator_id'];
        $data_json = $_REQUEST['data_json'];
        $flow_node_id = $_REQUEST['flow_node_id'];
        $remind_type = $_REQUEST['remind_type'];
        $form_data_id = $_REQUEST['form_data_id'];

        $form_data = array();
        $form_data['wfd_data_json'] = $data_json;
        $form_data['wfd_create_time'] = date("Y-m-d H:i:s");
        $form_data['wfd_user_id'] = $create_user_id;

        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_form_data->where("wfd_id = {$form_data_id}")->save($form_data);

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_form_data->getlastsql(),
            );
        } else {

            // 取得最新的表单数据信息
            $form_data_new = $_wf_form_data->where("wfd_id = {$form_data_id}")->find();

            // 取得表单结构信息
            $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
            $wf_form = $_wf_forms->where("wff_id = {$form_data_new['wfd_form']}")->find();

            // 更新数据记录
            $flowData_data = array();
//            $flowData_data['flow_node_id'] = $flow_node_id;
//            $flowData_data['data_name'] = $wf_form['wff_name_ch'];
//            $flowData_data['data_title'] = $wf_form['wff_name_ch'];
//            $flowData_data['data_type'] = 2;
//            $flowData_data['relate_db_name'] = "oa_memo";
//            $flowData_data['relate_db_id'] = $memo_id;
            $flowData_data['create_time'] = date("Y-m-d H:i:s");
            $flowData_data['create_user_id'] = $create_user_id;

            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');

            $_flow_data->where("relate_db_name = 'oa_wf_form_data' and relate_db_id = {$form_data_id}")->save($flowData_data);

            // 编辑表单数据发送通知
            /************************生成日志和消息相关Start**************************/

            // 生成操作日志并给相关人成生消息
            if ($remind_type == 1) {

                $this->createLogMessage($create_user_id, "编辑表单", $wf_form['wff_name_ch'], $flow_node_id, 0);
            }

            /************************生成日志和消息相关End**************************/

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
     * 删除表单数据
     */
    public function deleteFlowNodeFormData()
    {
        $form_data_id = $_REQUEST['form_data_id'];
        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');

        // 备忘表的数据
        $_wf_form_data->where("wfd_id = {$form_data_id}")->delete();

        // 删除文件对应的节点数据
        $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
        $flow_data = $_flow_data->where("relate_db_name = 'oa_wf_form_data' and relate_db_id = {$form_data_id}")->find();
        $_flow_data->where("data_id = {$flow_data['data_id']}")->delete();

        // 删除该数据所有的评论  $flow_data['data_id']
        $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
        $_comment->where("relate_db_name = 'oa_flow_data' and relate_db_id = {$flow_data['data_id']}")->delete();

        $_r = array(
            'errorCode' => '1',
            'errorName' => '删除成功',
        );

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 取得表单信息及评论列表
     */
    public function getFlowNodeFormData()
    {
        $form_data_id = $_REQUEST['form_data_id'];

        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
        $form_data = $_wf_form_data->where("wfd_id = {$form_data_id}")->find();

        if ($form_data === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误或者数据为空',
                'errorSql' => $_wf_form_data->getlastsql(),
            );
        } else {

            // 备忘所属的数据
            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
            $flow_data = $_flow_data->where("relate_db_name = 'oa_wf_form_data' and relate_db_id = {$form_data_id}")->find();

            // 取得该数据的评论列表
            $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
            // 查询列表
            $form_data_comment_list = $_comment->field("*")->where("relate_db_name = 'oa_flow_data' and relate_db_id = {$flow_data['data_id']} ")->select();

            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
                'form_data' => $form_data,
                'form_data_comment' => $form_data_comment_list,
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
     * 新建评论
     */
    public function createComment()
    {

        $create_user_id = $_REQUEST['operator_id'];

        // 评论目标:1 工作主线节点;2 附件;3 备忘;4 表单数据;
        $comment_type = $_REQUEST['comment_type'];

        // 被评论的目标id,如果是附件,既file_id,如果是备忘既memo_id,如果是表单数据既wfd_id,如果是节点既flow_node_id
        $comment_data_id = $_REQUEST['comment_data_id'];

        $comment_content = $_REQUEST['comment_content'];
        $file_url = $_REQUEST['file_url'];

        // 提醒方式
        $remind_type = $_REQUEST['remind_type'];

        $comment_data = array();
        $comment_data['comment_content'] = $comment_content;
        $comment_data['file_url'] = $file_url;

        if ($comment_type == 1) {
            $comment_data['relate_db_name'] = "oa_flow_node";
            $comment_data['relate_db_id'] = $comment_data_id;

            $flow_node_id = $comment_data_id;
        } else {

            if ($comment_type == 2) {
                $db_name = "oa_file";
            } elseif ($comment_type == 3) {
                $db_name = "oa_memo";
            } elseif ($comment_type == 4) {
                $db_name = "oa_wf_form_data";
            }
            // 根据评论目标ID取得对应的数据id
            $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
            $flow_data = $_flow_data->where("relate_db_name = '{$db_name}' and relate_db_id = {$comment_data_id}")->find();

            $comment_data['relate_db_name'] = "oa_flow_data";
            $comment_data['relate_db_id'] = $flow_data['data_id'];


            $flow_node_id = $flow_data['flow_node_id'];
        }

        $comment_data['create_time'] = date("Y-m-d H:i:s");
        $comment_data['create_user_id'] = $create_user_id;

        $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
        $comment_id = $_comment->add($comment_data);

        if ($comment_id === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_comment->getlastsql(),
            );
        } else {

            /************************生成日志和消息相关Start**************************/

            // 生成操作日志并给相关人成生消息
            if ($remind_type == 1) {

                $this->createLogMessage($create_user_id, "新增评论", $comment_content, $flow_node_id, 0);
            }

            /************************生成日志和消息相关End**************************/

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
     * 编辑当前用户自己的评论
     */
    public function editComment()
    {

        $create_user_id = $_REQUEST['operator_id'];

        $comment_id = $_REQUEST['comment_id'];
        $comment_content = $_REQUEST['comment_content'];
        $file_url = $_REQUEST['file_url'];

        $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
        // 取得旧的评论信息
        $comment = $_comment->where("comment_id = {$comment_id}")->find();
        if ($comment['create_user_id'] != $create_user_id) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '此用户无权限修改评论',
                'errorSql' => $_comment->getlastsql(),
            );
        } else {

            $comment_data = array();
            $comment_data['comment_content'] = $comment_content;
            $comment_data['file_url'] = $file_url;
            $comment_data['create_time'] = date("Y-m-d H:i:s");
            $comment_data['create_user_id'] = $create_user_id;

            $rs = $_comment->where("comment_id = {$comment_id}")->save($comment_data);

            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_comment->getlastsql(),
                );
            } else {

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '执行成功',
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
     * 删除当前用户自己的评论
     */
    public function deleteComment()
    {
        $create_user_id = $_REQUEST['operator_id'];
        $comment_id = $_REQUEST['comment_id'];

        $_comment = M("comment", "oa_", 'DB_CONFIG_OA');
        // 取得旧的评论信息
        $comment = $_comment->where("comment_id = {$comment_id}")->find();
        if ($comment['create_user_id'] != $create_user_id) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '此用户无权限删除评论',
                'errorSql' => $_comment->getlastsql(),
            );
        } else {
            // 备忘表的数据
            $_comment->where("comment_id = {$comment_id}")->delete();

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
     * 取得我的主线列表 (我创建的)
     */
    public function getMyFlowList()
    {
        $create_user_id = $_REQUEST['operator_id'];

        $_flow = M("flow", "oa_", 'DB_CONFIG_OA');

        // 查询列表
        $list = $_flow->field("*"
            . ",(select type.flow_type_name from flow_type_name type where type.flow_type_id = oa_flow.flow_type_id) as flow_type_name"
            . ",(select user.user_name from oa_users user where user.user_id = oa_flow.create_user) as user_name")
            ->where("create_user= {$create_user_id}")->select();

        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow->getlastsql(),
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
     * 取得全部主线列表 (我是主线负责人或者创建人或者是参与人,如果是参与人必须有查看权限,或者只取我参与的节点列表)
     */
    public function getAllFlowList()
    {
        $create_user_id = $_REQUEST['operator_id'];

        $_flow = M("flow", "oa_", 'DB_CONFIG_OA');

        // 取得全部我创建的或者负责人有我或者参与人有我的工作主线ID
        $list_my = $_flow->field("GROUP_CONCAT(flow_id) as flow_id")
            ->where("create_user= {$create_user_id} or find_in_set({$create_user_id},director_user_ids) or find_in_set({$create_user_id},participant_user_ids)")->find();

        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');
        // 取得节点参与人有我的工作主线ID
        $list_my_join = $_flow_node->field("GROUP_CONCAT(DISTINCT flow_id) as flow_id")
            ->where(" find_in_set({$create_user_id},participant_user_ids) ")->find();

        // 合并工作主线ID,并去重
        $flow_ids = array_merge(explode(",",$list_my['flow_id']),explode(",",$list_my_join['flow_id']));

        // 根据工作主线ID,查找符合要求的全部工作主线
        $flow_ids_str = implode(",",$flow_ids);
        $list = $_flow->field("*"
            . ",(select type.flow_type_name from flow_type_name type where type.flow_type_id = oa_flow.flow_type_id) as flow_type_name"
            . ",(select user.user_name from oa_users user where user.user_id = oa_flow.create_user) as user_name")
            ->where("find_in_set(flow_id,{$flow_ids_str})")->select();

        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_flow->getlastsql(),
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
     * 根据节点ID取得子节点列表和所有数据列表
     */
    public function getFlowNodeChildrenList()
    {

        // 主线ID
        $flow_node_parent_id = $_REQUEST['flow_node_parent_id'];

        $_flow_node = M("flow_node", "oa_", 'DB_CONFIG_OA');

        // 全部节点列表
        $flow_node_list = $_flow_node->field("*")->where("flow_node_parent_id = {$flow_node_parent_id} and is_delete = 0")->select();

        // 查询所属此节点的所有数据列表
        $_flow_data = M("flow_data", "oa_", 'DB_CONFIG_OA');
        $flow_data_list = $_flow_data->where("flow_node_id = {$flow_node_parent_id}")->oder("create_time")->find();

        $Model = new Model(); // 实例化一个空模型

        $flow_data_result = array();
        foreach ($flow_data_list as $key => $flow_data){
            if($flow_data['data_type'] == 0){
                $primary_key = "file_id";
            }elseif ($flow_data['data_type'] == 1){
                $primary_key = "memo_id";
            }elseif ($flow_data['data_type'] == 2){
                $primary_key = "wfd_id";
            }
            $data = $Model->execute(" select * from {$flow_data['relate_db_name']} WHERE {$primary_key} = {$flow_data['relate_db_id']}");

            $flow_data_result[$key] = $data;
        }

        // 查询成功
        $_r = array(
            'errorCode' => '1',
            'errorName' => '查询成功',
            'flow_data_list' => $flow_data_result,
            'flow_node_list' => $flow_node_list,
        );
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 取得指定节点的信息和评论列表

    // 设置通知状态为已读


    // 新建工作主线节点引用

    // 移动节点

    // 移动节点数据

}