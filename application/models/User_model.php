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
    function getUserList($filters, $sort, $page, $pageSize)
    {

        // 默认排序
        $orderStr = '';
        if ($sort['prop'] && $sort['order']) {
            $orderStr = " ORDER BY " . $sort['prop'] . " " . ($sort['order'] === 'ascending' ? 'asc' : 'desc');
        }

        $filterStr = '';
        $j = 0;
        foreach ($filters as $k => $v) {
            if ($v['value'] !== '' && !is_null($v['value'])) {
                if ($j) {
                    $filterStr = $filterStr . " and ";
                }

                if ($v['prop'] === 'username') {
                    $filterStr .= $v['prop'] . " like '%" . $v['value'] . "%' ";
                }
                if ($v['prop'] === 'status') {
                    $filterStr .= $v['prop'] . "=" . $v['value'];
                }

                $j++;
            }
        }

        if ($filterStr) {
            $filterStr = " and " . $filterStr;
        }

        $sql = "SELECT
                     *
                FROM
                    sys_user where 1=1 "
            . $filterStr
            . $orderStr . " limit " . ($page - 1) * $pageSize . "," . $pageSize;

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function getUserListCnt($filters)
    {
        $filterStr = '';
        $j = 0;
        foreach ($filters as $k => $v) {
            if ($v['value'] !== '' && !is_null($v['value'])) {
                if ($j) {
                    $filterStr = $filterStr . " and ";
                }

                if ($v['prop'] === 'username') {
                    $filterStr .= $v['prop'] . " like '%" . $v['value'] . "%' ";
                }
                if ($v['prop'] === 'status') {
                    $filterStr .= $v['prop'] . "=" . $v['value'];
                }

                $j++;
            }
        }

        if ($filterStr) {
            $filterStr = " and " . $filterStr;
        }
        $sql = "SELECT
                    count(u.id) cnt
                FROM
                    sys_user u  where 1=1 " . $filterStr;

        $query = $this->db->query($sql);
        if (empty($query->result_array())) {
            return 0;
        } else {
            $result = $query->result_array();
            return $result[0]['cnt'];
        }
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
     * 根据 用户ID 获取该用户被分配的角色
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