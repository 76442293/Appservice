<?php
/**
 * NodesAction.class.php
 * 工作流节点相关接口
 * DaMingGe 20180226
 */
import("@.Action.BaseAction");

class NodesAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据节点ID修改节点，ID为0则添加新节点
     */
    public function editNode()
    {
        $wn_id = isset($_REQUEST['wn_id']) ? $_REQUEST['wn_id'] : '0';
        $wn_data = array();
        $wn_data['wn_name'] = $_REQUEST['wn_name'];
        $wn_data['wn_company'] = $_REQUEST['wn_company'];
        $wn_data['wn_module'] = $_REQUEST['wn_module'];
        $wn_data['wn_workflow'] = $_REQUEST['wn_workflow'];
        $wn_data['wn_step'] = $_REQUEST['wn_step'];
        $wn_data['wn_node_type'] = $_REQUEST['wn_node_type'];
        $wn_data['wn_user'] = $_REQUEST['wn_user'];
        $wn_data['wn_node_true'] = $_REQUEST['wn_node_true'];
        $wn_data['wn_node_false'] = $_REQUEST['wn_node_false'];
        $wn_data['wn_remarks'] = $_REQUEST['wn_remarks'];
        $wn_data['wn_node_action'] = $_REQUEST['wn_node_action'];

        $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');
        if ($wn_id == '0') {
            //新增节点
            $rs = $_wf_nodes->add($wn_data);
        } else {
            //修改节点
            $rs = $_wf_nodes->where("wn_id = {$wn_id}")->save($wn_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_nodes->getlastsql(),
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
     * 根据工作流ID取得所属节点列表
     */
    public function listNodes()
    {
        $wn_workflow = isset($_REQUEST['wn_workflow']) ? $_REQUEST['wn_workflow'] : '0';

        if ($wn_workflow == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wn_workflow参数为空',
            );
        } else {

            $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_nodes->field("*")->where("wn_workflow = {$wn_workflow}")->select();

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_nodes->getlastsql(),
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
     * 根据节点ID取得节点详情
     */
    public function detailNode()
    {
        $wn_id = isset($_REQUEST['wn_id']) ? $_REQUEST['wn_id'] : '0';

        if ($wn_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wn_id参数为空',
            );
        } else {
            $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');

            $detail = $_wf_nodes->field("*")->where("wn_id = {$wn_id}")->find();
            if ($detail === false || empty($detail)) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_nodes->getlastsql(),
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
     * 根据节点ID删除节点
     */
    public function deleteNode()
    {
        $wn_id = isset($_REQUEST['wn_id']) ? $_REQUEST['wn_id'] : '0';
        if ($wn_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'wn_id参数为空',
            );
        } else {
            $_wf_nodes = M("wf_nodes", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_nodes->where("wn_id = {$wn_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_nodes->getlastsql(),
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