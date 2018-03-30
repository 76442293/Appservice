<?php
/**
 * AgentAction.class.php
 * 代理商相关接口
 * DaMingGe 2018-03-20
 */
import("@.Action.BaseAction");

class AgentAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据用户ID取得用户代理商级别以及下属公司列表
     */
    public function getAgentByUserID()
    {
        // 用户ID
        $uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '0';

        if ($uid == 0) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'uid参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        $_companys = M("companys", "oa_", 'DB_CONFIG_OA');

        $wf_agent = $_wf_agent->field("oa_wf_agent.*")->join("oa_wf_agent_user agent_user on agent_user.a_id = oa_wf_agent.a_id")
            ->where("agent_user.uid = {$uid} ")->find();

        if ($wf_agent === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $wf_agent->getlastsql(),
            );
        } else {

            $agent_compay_list = $_companys->field("oa_companys.*")->join("oa_wf_agent_compay compay on compay.compay_id = oa_companys.company_id")
                ->where("compay.a_id = {$wf_agent['a_id']}")->select();

            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'agent' => $wf_agent,
                'compay_list' => $agent_compay_list
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
     * 代理商管理员登录
     */
    public function agentUserLogin()
    {
        // 用户ID
        $au_login_name = $_REQUEST['au_login_name'];
        // 用户密码
        $au_psd = $_REQUEST['au_psd'];

        if (!isset($au_login_name)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'au_login_name参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        if (!isset($au_psd)) {
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'au_psd参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r);
            }
            exit;
        }

        $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

        // MD5加密
        $au_psd = md5($au_psd);
        $agent_user = $_wf_agent_user->field("*")->where("au_login_name = {$au_login_name} and au_psd = {$au_psd}")->find();

        if ($agent_user === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '登录错误',
                'errorSql' => $_wf_agent_user->getlastsql(),
            );
        } else {
            if (empty($list)) {
                $_r = array(
                    'errorCode' => '3',
                    'errorName' => '用户登录名称或者密码错误',
                );
            } else {

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '登录成功',
                    'agent_user' => $agent_user,
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 创建/编辑 代理商管理员
     */
    public function editAgentUser()
    {
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';
        $au_data = array();
        $au_data['au_login_name'] = $_REQUEST['au_login_name'];
        $au_data['au_psd'] = md5($_REQUEST['au_psd']);
        $au_data['a_id'] = $_REQUEST['a_id'];
        $au_data['au_name'] = $_REQUEST['au_name'];
        $au_data['create_time'] = date("Y-m-d H:i:s");
        $au_data['is_can_create'] = $_REQUEST['is_can_create'];

        $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');
        if ($au_id == '0') {
            //新增管理员
            $rs = $_wf_agent_user->add($au_data);
        } else {
            //修改管理员
            $rs = $_wf_agent_user->where("au_id = {$au_id}")->save($au_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_agent_user->getlastsql(),
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
     * 根据代理商ID取得管理员列表
     */
    public function listAgentUser()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';

        if ($a_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'a_id参数为空',
            );
        } else {

            $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_agent_user->field("au_id,au_login_name,a_id,au_name,create_time")->where("a_id = {$a_id}")->select();

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_agent_user->getlastsql(),
                );
            } else {
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
     * 删除管理员
     */
    public function deleteAgentUser()
    {
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';
        if ($au_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'au_id参数为空',
            );
        } else {
            $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_agent_user->where("au_id = {$au_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_agent_user->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '删除成功',
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
     * 创建.编辑 代理商(创建者所在代理商必须是总代或代理商  不能是公司,且能否创建代理商的开关是开启状态)
     */
    public function editAgent()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';
        $a_data = array();
        $a_data['a_name'] = $_REQUEST['a_name'];
        $a_data['a_level'] = $_REQUEST['a_level'];
        $a_data['a_parent_id'] = $_REQUEST['a_parent_id'];
        $a_data['create_time'] = date("Y-m-d H:i:s");
        $a_data['is_can_create'] = $_REQUEST['is_can_create'];

        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        if ($a_id == '0') {
            //新增代理商
            $rs = $_wf_agent->add($a_data);
        } else {
            //修改代理商
            $rs = $_wf_agent->where("$a_id = {a_id}")->save($a_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_agent->getlastsql(),
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

    // 删除代理商

    // 给指定的代理商分配公司(公司级别的代理商只能分配一个公司,同一个公司不能属于多个代理商)

}