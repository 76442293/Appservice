<?php
/**
 * FormWidgetsAction.class.php
 * 自定义控件相关接口
 * DaMingGe 2017-12-13
 */
import("@.Action.BaseAction");

class FormWidgetsAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询控件列表/根据ID查询单个控件及此控件所有属性
     */
    public function listWfFormWidgets()
    {

        $wfw_id = isset($_REQUEST['wfw_id']) ? $_REQUEST['wfw_id'] : '0';

        if (!empty($wfw_id)) {
            $where['wfw_id'] = $wfw_id;
        }
        $where['wfw_abled'] = 1;

        // 实例化控件model
        $_form_widgets = M("wf_form_widgets", "oa_", 'DB_CONFIG_OA');

        // 查询控件列表
        $list = $_form_widgets->field("*")->where($where)->select();
        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_form_widgets->getlastsql(),
            );
        } else {
            if (empty($list)) {
                // 数据为空
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '暂无数据',
                );
            } else {

                foreach ($list as $key => $widget) {
                    // 格式化控件属性
                    $list[$key]['wfw_attr'] = json_decode($widget['wfw_attr']);
                }

                // 查询成功
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $list,
                    'form_id' => '',
                    'module_id' => '',
                );
            }
        }
        // 返回数据
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }

        exit;
    }

    /**
     * 取得所有代码列表/根据ID取得代码数据
     */
    public function getCodeList()
    {
        $wc_id = isset($_REQUEST['wc_id']) ? $_REQUEST['wc_id'] : '0';

        if (!empty($wc_id)) {
            $where['wc_id'] = $wc_id;
        }
        $where['wc_abled'] = 1;

        // 实例化代码表model
        $_wf_code = M("wf_code", "oa_", 'DB_CONFIG_OA');

        // 查询代码列表
        $list = $_wf_code->field("*")->where($where)->select();
        if ($list === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_code->getlastsql(),
            );
        } else if (empty($list)) {
            // 数据为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => '暂无数据',
            );
        } else {

            // 查询成功
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'list' => $list,
            );
        }

        // 返回数据
        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        exit;
    }

    /**
     * 根据代码ID取得该代码所有明细数据
     */
    public function getCodeDetailById()
    {

        $wc_id = isset($_REQUEST['wc_id']) ? $_REQUEST['wc_id'] : '0';

        if ($wc_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wc_id参数为空',
            );
        } else {
            // 实例化代码明细model
            $_wf_code_detail = M("wf_code_detail", "oa_", 'DB_CONFIG_OA');
            // 查询属性
            $code_detail_list = $_wf_code_detail->field("*")->where("wcd_abled = 1 AND wcd_wc_id = {$wc_id}")->order("wcd_order")->select();
            if ($code_detail_list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_wf_code_detail->getlastsql(),
                );
            } else if (empty($code_detail_list)) {
                // 数据为空
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '暂无数据',
                );
            } else {
                // 查询成功
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $code_detail_list,
                );
            }
        }

//        // 返回数据
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
//        exit;

        // 返回数据
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r,JSON_UNESCAPED_UNICODE);
        }

        exit;
    }


    /**
     * 根据代码明细ID取得指定的代码明细数据
     */
    public function getCodeDetailByDetailId()
    {

        $wcd_id = isset($_REQUEST['wcd_id']) ? $_REQUEST['wcd_id'] : '0';

        if ($wcd_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wcd_id参数为空',
            );
        } else {
            // 实例化代码明细model
            $_wf_code_detail = M("wf_code_detail", "oa_", 'DB_CONFIG_OA');
            // 查询属性
            $code_detail_list = $_wf_code_detail->field("*")->where("wcd_abled = 1 AND wcd_id = {$wcd_id}")->select();
            if ($code_detail_list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '执行错误',
                    'errorSql' => $_wf_code_detail->getlastsql(),
                );
            } else if (empty($code_detail_list)) {
                // 数据为空
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '暂无数据',
                );
            } else {
                // 查询成功
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $code_detail_list,
                );
            }
        }

//        // 返回数据
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
//        exit;
        // 返回数据
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }

        exit;
    }

    /**
     * 新增控件或根据控件ID编辑控件
     */
    public function editWfFormWidgets()
    {
        $jsonStr = $_REQUEST['jsonStr'];

//        $jsonStr = '{"wfw_id":"21","wfw_name":"aaccc","wfw_name_ch":"","wfw_abled":"1","wfw_icon":"","wfw_attr":[{"uid":"","dataSource":"","labelName":"","display":"false","diy":[{"data_type":"","data_name":"","data":""}]}]}';


        $wfw_data = json_decode($jsonStr, true);
        $wfw_id = $wfw_data['wfw_id'];
        $wfw_data['wfw_attr'] = json_encode($wfw_data['wfw_attr'],JSON_UNESCAPED_UNICODE);

        $_form_widgets = M("wf_form_widgets", "oa_", 'DB_CONFIG_OA');
        if ($wfw_id == '0') {
            //新增控件
            $rs = $_form_widgets->add($wfw_data);
        } else {
            //修改控件
            $rs = $_form_widgets->where("wfw_id = {$wfw_id}")->save($wfw_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_form_widgets->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }

        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        exit;
    }

    /**
     * 新增代码或根据代码ID编辑代码
     */
    public function editCode()
    {
        $wc_id = isset($_REQUEST['wc_id']) ? $_REQUEST['wc_id'] : '0';
        $wf_data = array();
        $wf_data['wc_name'] = $_REQUEST['wc_name'];
        $wf_data['wc_name_ch'] = $_REQUEST['wc_name_ch'];
        $wf_data['wc_abled'] = $_REQUEST['wc_abled'];

        $_wf_code = M("wf_code", "oa_", 'DB_CONFIG_OA');
        if ($wc_id == '0') {
            //新增代码
            $rs = $_wf_code->add($wf_data);
        } else {
            //修改代码
            $rs = $_wf_code->where("wc_id = {$wc_id}")->save($wf_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_code->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }

        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        exit;
    }

    /**
     * 根据ID删除代码和此代码所有明细(不建议使用)
     */
    public function deleteCodeById()
    {
        $wc_id = $_REQUEST['wc_id'];

        if ($wc_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wc_id参数为空',
            );
        } else {
            $_wf_code = M("wf_code", "oa_", 'DB_CONFIG_OA');
            $_wf_code_detail = M("wf_code_detail", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_code->where("wc_id = {$wc_id}")->delete();
            $rs1 = $_wf_code_detail->where("wcd_wc_id = {$wc_id}")->delete();
            if ($rs === false || $rs1 === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除失败',
                    'errorSql' => $_wf_code->getlastsql(),
                    'detailErrorSql' => $_wf_code_detail->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '删除成功',
                );
            }
        }

        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        exit;
    }

    /**
     * 新增代码明细或根据代码明细ID编辑代码明细
     */
    public function editCodeDetail()
    {
        $wcd_id = isset($_REQUEST['wcd_id']) ? $_REQUEST['wcd_id'] : '0';
        $detail_data = array();
        $detail_data['wcd_wc_id'] = $_REQUEST['wcd_wc_id'];
        $detail_data['wcd_value'] = $_REQUEST['wcd_value'];
        $detail_data['wcd_text'] = $_REQUEST['wcd_text'];
        $detail_data['wcd_order'] = $_REQUEST['wcd_order'];
        $detail_data['wcd_is_default'] = $_REQUEST['wcd_is_default'];
        $detail_data['wcd_abled'] = $_REQUEST['wcd_abled'];

        $_wf_code_detail = M("wf_code_detail", "oa_", 'DB_CONFIG_OA');
        if ($wcd_id == '0') {
            //新增代码明细
            $rs = $_wf_code_detail->add($detail_data);
        } else {
            //修改代码明细
            $rs = $_wf_code_detail->where("wcd_id = {$wcd_id}")->save($detail_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_code_detail->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }

        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        exit;
    }

    /**
     * 根据明细ID删除指定的代码明细
     */
    public function deleteCodeDetailByDetailId()
    {
        $wcd_id = $_REQUEST['wcd_id'];

        if ($wcd_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wcd_id参数为空',
            );
        } else {
            $_wf_code_detail = M("wf_code_detail", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_code_detail->where("wcd_id = {$wcd_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除失败',
                    'errorSql' => $_wf_code_detail->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '删除成功',
                );
            }
        }

        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        exit;
    }


}

