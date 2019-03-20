<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }




    function saveAdd($table,$data)
    {
        $this->db->insert($table,$data);
        return $this->db->insert_id();
    }

    function saveEdit($table,$data,$where)
    {
        $this->db->where($where);
        $this->db->update($table,$data);
        return $this->db->affected_rows();
    }

    function isExist($table,$where)
    {
        $this->db->where($where);
        $this->db->from($table);
        return $this->db->count_all_results();
    }

    function saveDel($table,$where)
    {
        $this->db->where($where);
        $this->db->delete($table);
        return $this->db->affected_rows();
    }

    function select($table,$select='',$where='',$order='')
    {
        if ($select) { $this->db->select($select); }
        if ($where) { $this->db->where($where); }
        if ($order) { $this->db->order_by($order); }

        $query = $this->db->get($table);
        return $query->result();


    }

    function total($table,$field,$keyword)
    {
        $sql="select count(*) numrows from $table where $field like '%$keyword%' ";
        $query=$this->db->query($sql);
        if( ($query->row_array())==null ){
            return 0;
        }else
        {
            $result=$query->result_array();
            return $result;
        }
    }

    function saveUserWxUserId($id, $wxUserId, $wxUserName)
    {
        $sql = "UPDATE userinfo SET wxUserName='" . $wxUserName . "',wxUserId='" . $wxUserId . "' WHERE id=" . $id;
        $this->db->query($sql);
    }

    function getUserByWxUserId($wxUserId)
    {
        $sql = "select Id id,
        USERNAME name,
        USERALIAS,
        USERPASS,
        USERDES,
        LASTLOGIN,
        LASTIP,
        CPASS,
        USERPROP,
        CTIME,
        UTIME,wxUserId,wxUserName from userinfo where status = 1 and wxUserId='" . $wxUserId . "'";
        $query = $this->db->query($sql);
        if (($query->row_array()) == null) {
            $result = array(
                'success' => false,
                'userinfo' => null
            );
        } else {
            $result = array(
                'success' => true,
                'userinfo' => $query->row_array()
            );
        }
        return $result;
    }

    function app_user_login_validate($input_account, $input_password)
    {

        $sql = "select Id id,
                USERNAME name,
                USERALIAS,
                USERPASS,
                USERDES,
                LASTLOGIN,
                LASTIP,
                CPASS,
                USERPROP,
                CTIME,
                UTIME,wxUserId,wxUserName from  userinfo where status = 1 and username='" . $input_account . "' and userpass='" . $input_password . "'";
        $query = $this->db->query($sql);


        if (($query->row_array()) == null) {
            $result = array(
                'success' => false,
                'userinfo' => null
            );
        } else {
            $result = array(
                'success' => true,
                'userinfo' => $query->row_array()
            );
//            $s_userinf = array(
//                'S_UserID'  	=> $result['Id'],
//                'S_UserName'  	=> $result['USERNAME'],
//                'S_UserAlias'  	=> $result['USERALIAS'],
//                'S_LastIP' 		=> $result['LASTIP'],
//                'S_LastLogin' 	=> $result['LASTLOGIN']
//            );
//            $this->session->set_userdata($s_userinf);
        }
        return $result;
    }

    /*
     * app用户修改密码
     */
    function changepwd($UserID, $oldpass, $newpass)
    {
        $sql = "select * from userinfo where Id=" . $UserID . " and USERPASS='" . $oldpass . "'";
        $query = $this->db->query($sql);

        if (($query->row_array()) == null) {
            return 0;
            //"{\"success\": false,\"message\": \"原始密码验证错误!.\"}";
        } else {
            $result = $query->row_array();

            $sql = "update userinfo set USERPASS='" . $newpass . "' where Id=" . $UserID;
            $query = $this->db->query($sql);
            return 1;

        }
    }

    function isExpired($token)
    {
        $ctime = time();
        $sql = "select * from  auth a where token='" . $token . "' and UNIX_TIMESTAMP(a.expiredAt)> " . $ctime;
        $query = $this->db->query($sql);

        if (($query->row_array()) == null) {
            return true;
        } else {
            return false;
        }
    }

    function getdangkeLists($UserName, $Page, $Row)
    {

        $sql = "SELECT i.*,(select USERALIAS from userinfo where Id=i.CreateMan) USERALIAS FROM `item_file` i order by Id desc";
        $query = $this->db->query($sql);
        if (($query->row_array()) == null) {
            //return $sql;
            return null;
        } else {
            $result = $query->result();
            return $result;
        }
    }


}