<?php
/**
 * FormSearchAction.class.php
 * 表单筛选条件配置相关接口
 * DaMingGe 2018-03-17
 */
import("@.Action.BaseAction");

class FormSearchAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据公司ID取得该公司表单结构列表的筛选条件列表
     */
    public function listFormSearch()
    {

        $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '0';

        $_wf_form_search = M("wf_form_search", "oa_", 'DB_CONFIG_OA');

        $list = $_wf_form_search->field("*")->where("company_id = {$company_id}")->select();
        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_form_search->getlastsql(),
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
        // 返回数据
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 根据公司ID取得表单结构的筛选条件
     */
    public function getFormSearchByCompayID()
    {
        $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '0';

        $_form_search = M("wf_form_search", "oa_", 'DB_CONFIG_OA');

        // 查询筛选条件列表
        $list = $_form_search->field("*")->where("company_id = {$company_id}")->select();
        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_form_search->getlastsql(),
            );
        } else {
            if (empty($list)) {
                // 数据为空
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '没有数据',
                );
            } else {

                $list[0]['wff_json'] = json_decode($list[0]['wff_json']);

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
            header('Access-Control-Allow-Origin:*');
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 添加修改表单结构筛选信息
     */
    public function editFormSearch()
    {
        $s_id = isset($_REQUEST['s_id']) ? $_REQUEST['s_id'] : '0';
        $fs_data = array();
        $fs_data['company_id'] = $_REQUEST['company_id'];
        $fs_data['s_key'] = $_REQUEST['s_key'];
        $fs_data['s_name'] = $_REQUEST['s_name'];

        $_wf_form_search = M("wf_form_search", "oa_", 'DB_CONFIG_OA');
        if ($s_id == '0') {
            //新增条件
            $rs = $_wf_form_search->add($fs_data);
        } else {
            //修改条件
            $rs = $_wf_form_search->where("s_id = {$s_id}")->save($fs_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_form_search->getlastsql(),
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
            header('Access-Control-Allow-Origin:*');
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 删除表单结构筛选条件
     */
    public function delFormSearch()
    {
        $s_id = $_REQUEST['s_id'];
        $_wf_form_search = M("wf_form_search", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_form_search->where("s_id = {$s_id}")->delete();
        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '删除失败',
                'errorSql' => $_wf_form_search->getlastsql(),
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
            header('Access-Control-Allow-Origin:*');
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

}