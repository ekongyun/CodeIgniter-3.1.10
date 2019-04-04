<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model
{
    /**
     * 用户模型部分
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 获取所有用户列表
     */
    function getUserList()
    {
        $sql = "SELECT
                    u.*
                FROM
                    sys_user u 
                 ORDER BY
                    u.listorder";
        $query = $this->db->query($sql);
        return [
            "items" => $query->result_array(),
            "total" => count($query->result_array())
        ];
    }

    /**
     * 根据 $token 获取对应Token用户所拥有的角色类权限选项 新增时使用
     */
    function getRoleOptions($Token)
    {
        $sql = "SELECT
                    r.id,
                    r.name,
                    r.remark,
                    r.status
                FROM
                    sys_user_token ut,
                    sys_user u,
                    sys_user_role ur,
                    sys_role_perm rp,
                    sys_perm p,
                    sys_role r
                WHERE
                    ut.token = '" . $Token . "'
                AND ut.user_id = u.id
                AND u.id = ur.user_id
                AND ur.role_id = rp.role_id
                AND rp.perm_id = p.id
                AND p.perm_type = 'role'
                AND p.r_id = r.id
                AND r. STATUS = 1
                order by r.listorder";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    /**
     * 根据 用户ID 获取该用户被分配的角色 编辑时使用
     *
     */
    function getUserRoles($Id)
    {
        $sql = "SELECT
                    r.id,
                    r.name,
                    r.remark,
                    r.status
                FROM
                    sys_user_role ur,
                    sys_role r
                WHERE
                    ur.role_id = r.id
                AND ur.user_id =" . $Id;

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * 根据 用户ID 获取该用户所拥有的角色
     * [
     * ['user_id'=> 1, 'role_id'=>1]
     * ...
     * ]
     */
    function getRolesByUserId($Id)
    {
        $sql = "SELECT
                    ur.user_id,ur.role_id
                FROM
                    sys_user_role ur
                 WHERE ur.user_id=" . $Id;

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /***********************
     * 用户模型部分结束
     ***********************/
}