<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Dept extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Base_model');
        $this->load->model('Dept_model');
        // $this->config->load('config', true);
    }

    // 增
    function add_post()
    {
        $uri = $this->uri->uri_string;
        $Token = $this->input->get_request_header('X-Token', TRUE);
        $retPerm = $this->permission->HasPermit($Token, $uri);
        if ($retPerm['code'] != 50000) {
            $this->set_response($retPerm, REST_Controller::HTTP_OK);
            return;
        }

        $parms = $this->post();  // 获取表单参数，类型为数组

//        var_dump($parms);return;
        if ($this->Base_model->_key_exists('sys_dept', ['name' => $parms['name']])) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['name'] . ' - 机构名称重复'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        $dept_id = $this->Base_model->_insert_key('sys_dept', $parms);
        if (!$dept_id) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['name'] . ' - 机构新增失败'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        // 超级管理员用户的超级管理员角色自动归属该机构
        $user_role_id = $this->Base_model->_insert_key('sys_user_role', ["user_id" => 1, "role_id" => 1, "dept_id" => $dept_id]);
        if (!$user_role_id) {
            var_dump($this->uri->uri_string . ' 超级管理员用户的超级管理员角色自动归属该机构, 失败...');
            var_dump(["user_id" => 1, "role_id" => 1, "dept_id" => $dept_id]);
            return;
        }

        $message = [
            "code" => 20000,
            "type" => 'success',
            "message" => $parms['name'] . ' - 机构新增成功'
        ];
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // 改
    function edit_post()
    {
        $uri = $this->uri->uri_string;
        $Token = $this->input->get_request_header('X-Token', TRUE);
        $retPerm = $this->permission->HasPermit($Token, $uri);
        if ($retPerm['code'] != 50000) {
            $this->set_response($retPerm, REST_Controller::HTTP_OK);
            return;
        }

        // $id = $this->post('id'); // POST param
        $parms = $this->post();  // 获取表单参数，类型为数组
        // var_dump($parms['path']);

        // 参数检验/数据预处理
        $id = $parms['id'];
        unset($parms['id']); // 剃除索引id
        unset($parms['children']); // 剃除传递上来的子节点信息

        if ($id == $parms['pid']) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['name'] . ' - 上级机构不能为自己'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        $where = ["id" => $id];

        if (!$this->Base_model->_update_key('sys_dept', $parms, $where)) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['name'] . ' - 机构更新错误'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        $message = [
            "code" => 20000,
            "type" => 'success',
            "message" => $parms['name'] . ' - 机构更新成功'
        ];
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // 删
    function del_post()
    {
        $uri = $this->uri->uri_string;
        $Token = $this->input->get_request_header('X-Token', TRUE);
        $retPerm = $this->permission->HasPermit($Token, $uri);
        if ($retPerm['code'] != 50000) {
            $this->set_response($retPerm, REST_Controller::HTTP_OK);
            return;
        }

        $parms = $this->post();  // 获取表单参数，类型为数组
        // var_dump($parms['path']);

        // 参数检验/数据预处理
        // 含有子节点不允许删除
        $hasChild = $this->Dept_model->hasChildDept($parms['id']);
        if ($hasChild) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['name'] . ' - 存在子节点不能删除'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        // 先删除外键关联表
        if (!$this->Base_model->_delete_key('sys_user_role', ['dept_id' => $parms['id']])) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => '删除关联表失败 ' . json_encode($parms)
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        // 删除基础表 sys_dept
        if (!$this->Base_model->_delete_key('sys_dept', ['id' => $parms['id']])) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['name'] . ' - 机构删除失败'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
            return;
        }

        $message = [
            "code" => 20000,
            "type" => 'success',
            "message" => $parms['name'] . ' - 机构删除成功'
        ];
        $this->set_response($message, REST_Controller::HTTP_OK);

    }

    // 查
    function view_post()
    {
        $uri = $this->uri->uri_string;
        $Token = $this->input->get_request_header('X-Token', TRUE);

        $retPerm = $this->permission->HasPermit($Token, $uri);
        if ($retPerm['code'] != 50000) {
            $this->set_response($retPerm, REST_Controller::HTTP_OK);
            return;
        }

        $DeptArr = $this->Dept_model->getDeptList();
        $DeptTree = $this->permission->genDeptTree($DeptArr, 'id', 'pid', 0);

        $message = [
            "code" => 20000,
            "data" => $DeptTree,
        ];
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

}
