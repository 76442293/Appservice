<?php
/**
 * FormAction.class.php
 * 自定义表单相关接口
 * DaMingGe 2017-12-15
 */
import("@.Action.BaseAction");

class FormsAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 表单结构列表
     *      查询条件待指定
     *      为减少数据传输此列表中没有表单具体结构
     */
    public function listWfForms()
    {
        $where['wff_abled'] = 1;

        // 表单结构ID
        $wff_id = $_REQUEST['wff_id'];
        if (isset($wff_id)) {
            $where['wff_id'] = $wff_id;
        }

        // 表单结构名称
        $wff_name = $_REQUEST['wff_name'];
        if (isset($wff_name)) {
            $where['wff_name'] = $wff_name;
        }

        // 公司ID
        $wff_company = $_REQUEST['wff_company'];
        if (isset($wff_company)) {
            if ($wff_company == -1) {
                $where['wff_company'] = array('NEQ', 1);
            } else {
                $where['wff_company'] = $wff_company;
            }
        } else {
            $where['wff_company'] = array('NEQ', 1);
        }

        // 模块ID
        $wff_module = $_REQUEST['wff_module'];
        if (isset($wff_module)) {
            $where['wff_module'] = $wff_module;
        }

        // 工作流ID
        $wff_workflow = $_REQUEST['wff_workflow'];
        if (isset($wff_workflow)) {
            $where['wff_workflow'] = $wff_workflow;
        }

        // 创建时间
        $wff_create_time = $_REQUEST['wff_create_time'];
        if (isset($wff_create_time)) {
            $where['wff_create_time'] = $wff_create_time;
        }

        // 表单中文名称
        $wff_name_ch = $_REQUEST['wff_name_ch'];
        if (isset($wff_name_ch)) {
            $where['wff_name_ch'] = $wff_name_ch;
        }

        // 实例化表单model
        $_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');

        // 查询表单列表
        $list = $_forms->field("*,(SELECT flow.wf_name FROM oa_wf_workflow flow WHERE flow.wf_id = oa_wf_forms.wff_workflow) AS workflow_name ")->where($where)->select();
        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_forms->getlastsql(),
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
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 根据表单结构ID取得指定的表单结构
     */
    public function getWfFormByID()
    {
        $wff_id = isset($_REQUEST['wff_id']) ? $_REQUEST['wff_id'] : '0';

        if ($wff_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wff_id参数为空',
            );
        } else {

            // 实例化表单model
            $_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');

            // 查询表单列表
            $list = $_forms->field("*")->where("wff_abled = 1 AND wff_id = {$wff_id}")->select();
            if ($list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_forms->getlastsql(),
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
     * 添加修改表单结构信息
     *      使用head实现跨域
     */
    public function editWfForms()
    {
        $wff_id = isset($_REQUEST['wff_id']) ? $_REQUEST['wff_id'] : '0';
        $wf_data = array();
        $wf_data['wff_name'] = $_REQUEST['wff_name'];
        $wf_data['wff_company'] = $_REQUEST['wff_company'];
        $wf_data['wff_module'] = $_REQUEST['wff_module'];
        $wf_data['wff_workflow'] = $_REQUEST['wff_workflow'];
        $wf_data['wff_node'] = $_REQUEST['wff_node'];
        $wf_data['wff_abled'] = $_REQUEST['wff_abled'];
        $wf_data['wff_json'] = $_REQUEST['wff_json'];
        $wf_data['wff_name_ch'] = $_REQUEST['wff_name_ch'];
        $wf_data['wff_icon'] = $_REQUEST['wff_icon'];

        $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
        if ($wf_data['wff_abled'] == '1') {
            //如果启用模块，记录启用时间
            $wf_data['wff_start_time'] = date("Y-m-d H:i:s");
        }
        if ($wff_id == '0') {
            //新增模块
            $wf_data['wff_create_time'] = date("Y-m-d H:i:s");//模块创建时间
            $rs = $_wf_forms->add($wf_data);
        } else {

            //判断是否有表单数据应用此表单结构
            $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
            $check_wf = $_wf_form_data->where("wfd_form = {$wff_id}")->find();
            if (!empty($check_wf)) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '有表单数据套用此表单结构，不可以修改',
                );
            } else {
                //修改模块
                $rs = $_wf_forms->where("wff_id = {$wff_id}")->save($wf_data);
            }
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_forms->getlastsql(),
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
     * 删除表单结构
     */
    public function delWfForms()
    {
        $wff_id = $_REQUEST['wff_id'];
        $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');

        //判断是否有表单数据应用此表单结构
        $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
        $check_wf = $_wf_form_data->where("wfd_form = {$wff_id}")->find();
        if (!empty($check_wf)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '有表单数据套用此表单结构，不可以删除',
            );
        } else {
            $rs = $_wf_forms->where("wff_id = {$wff_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除失败',
                    'errorSql' => $_wf_forms->getlastsql(),
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

    /**
     * 复用表单结构
     *      使用head实现跨域
     */
    public function copyForm()
    {
        $wff_id = isset($_REQUEST['wff_id']) ? $_REQUEST['wff_id'] : '0';
        $to_company_id = $_REQUEST['to_company_id'];
        $to_module_id = $_REQUEST['to_module_id'];
        $to_workflow_id = $_REQUEST['to_workflow_id'];

        if ($wff_id == '0') {
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wff_id参数缺少',
            );
        } else {

            //判断是否有表单数据应用此表单结构
            $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
            $form = $_wf_forms->where("wff_id = {$wff_id}")->find();
            if (empty($form)) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '表单结构不存在',
                );
            } else {

                unset($form['wff_id']);
                // copy原表单为共享表单
                if ($to_company_id == 1) {
                    $form['wff_company'] = 1;
                    $form['wff_module'] = 0;
                    $form['wff_workflow'] = 0;
                } else {
                    // copay一个共享表单到本公司模块下
                    $form['wff_company'] = $to_company_id;
                    $form['wff_module'] = $to_module_id;
                    $form['wff_workflow'] = $to_workflow_id;
                    $form['wff_create_time'] = date("Y-m-d H:i:s");//模块创建时间
                    $form['wff_start_time'] = date("Y-m-d H:i:s");//模块创建时间

                }

                $rs = $_wf_forms->add($form);

                if ($rs === false) {
                    $_r = array(
                        'errorCode' => '0',
                        'errorName' => '执行失败',
                        'errorSql' => $_wf_forms->getlastsql(),
                    );
                } else {
                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '执行成功',
                    );
                }
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

}