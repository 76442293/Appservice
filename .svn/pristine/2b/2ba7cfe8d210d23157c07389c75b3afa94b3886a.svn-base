<?php
/**
 * FormDataAction.class.php
 * 表单提交数据相关接口
 * DaMingGe 2017-12-15
 */
import("@.Action.BaseAction");

class FormDataAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 表单数据列表
     *      查询条件待指定
     *      为减少数据传输此列表中没有表单具体JSON数据
     */
    public function listWfFormData()
    {
        // 实例化表单model
        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');

        // 查询条件待定
        $where = '1=1';
        // 查询表单列表
        $list = $_wf_form_data->field("wfd_id,wfd_company,wfd_module,wfd_workflow,wfd_node,wfd_form,wfd_create_time")->where($where)->select();
        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_form_data->getlastsql(),
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
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 根据表单数据表ID取得指定的表单数据和表单结构
     */
    public function getFormDataByID()
    {
        $wfd_id = isset($_REQUEST['wfd_id']) ? $_REQUEST['wfd_id'] : '0';

        if ($wfd_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wfd_id参数为空',
            );
        } else {

            // 实例化表单数据model
            $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');

            // 查询表单数据列表
            $list = $_wf_form_data->field("*")->where("wfd_id = {$wfd_id}")->select();
            if ($list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_form_data->getlastsql(),
                );
            } else {
                if (empty($list)) {
                    // 数据为空
                    $_r = array(
                        'errorCode' => '2',
                        'errorName' => '没有数据',
                    );
                } else {

                    // 根据表单数据中的所属表单ID查询表单结构
                    $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
                    $form = $_wf_forms->field("*")->where("wff_id = {$list[0]['wfd_form']}")->select();

                    // 表单结构
                    $form_json = json_decode($form[0]['wff_json'], true);
                    // 表单数据
                    $wff_json = json_decode($list[0]['wfd_data_json'], true);

                    // 将表单数据加在空数据的表单结构上
                    // 遍历表单结构
                    foreach ($form_json as $key => $value) {
                        // 表单结构中的控件属性
                        $wfw_attr = $value['wfw_attr'];
                        // name属性
                        $input_name = $wfw_attr[0]['name'];

                        // 遍历表单数据,找到对应的控件名称和输入值
                        foreach ($wff_json as $kk => $vv) {
                            if ($vv['name'] == $input_name) {
                                // 表单数据表中的值加在表单结构上
                                $form_json[$key]['wfw_attr'][0]['value'] = $vv['value'];
                            }
                        }
                    }

                    // 将结构和数据放在返回的结果中
                    $list[0]['wfd_data_json'] = $form_json;

                    // 查询成功
                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '查询成功',
                        'list' => $list,
                    );
                }
            }
        }

        // 返回数据
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 提交新表单数据,根据表单数据表ID修改表单数据
     *      使用head实现跨域
     */
    public function editWfFormData()
    {
        $wfd_id = isset($_REQUEST['wfd_id']) ? $_REQUEST['wfd_id'] : '0';
        $wfd_data = array();
        $wfd_data['wfd_company'] = $_REQUEST['wfd_company'];
        $wfd_data['wfd_module'] = $_REQUEST['wfd_module'];
        $wfd_data['wfd_workflow'] = $_REQUEST['wfd_workflow'];
        $wfd_data['wfd_node'] = $_REQUEST['wfd_node'];
        $wfd_data['wfd_form'] = $_REQUEST['wfd_form'];
        $wfd_data['wfd_data_json'] = $_REQUEST['wfd_data_json'];

        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
        if ($wfd_id == '0') {
            //提交新表单数据
            $wfd_data['wfd_create_time'] = date("Y-m-d H:i:s");//模块创建时间
            $rs = $_wf_form_data->add($wfd_data);
        } else {
            //修改表单数据
            $rs = $_wf_form_data->where("wfd_id = {$wfd_id}")->save($wfd_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_form_data->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }

        header('Access-Control-Allow-Origin:*');

        echo json_encode($_r);
        exit;
    }

    /**
     * 删除表单数据
     */
    public function delWfFormData()
    {
        $wfd_id = $_REQUEST['wfd_id'];
        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');

        // 归属节点id不为空，说明此表单数据已进入工作流，不可删除
        $list = $_wf_form_data->where("wfd_id = {$wfd_id}")->find();
        if (!empty($list[0]['wfd_node'])) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '表单数据已进入工作流，不可删除',
            );
        } else {
            $rs = $_wf_form_data->where("wfd_id = {$wfd_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除失败',
                    'errorSql' => $_wf_form_data->getlastsql(),
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