<?php

class Auth_model extends Sippy_model
{
    protected $table = 'users';

    public function login($data) {
       $user = $this->get_user_by_email($data['email']);
       if ($user) 
       {
           if (password_verify($data['password'], $user->password)) 
           {
               unset($data['password']);
               $_SESSION['logged_in'] = true;
               $_SESSION['user'] = $data['email'];
               $res = $this->update_user($user->id, array('last_login' => date('Y-m-d H:i:s')));
               return $res;
           } else {
               return [
                   'errors' => [
                       'Password failed'
                   ] // custom model error
               ];
           }
       }
    }

    public function register($name, $email,$password) {
        $user = $this->get_user_by_email($email);
        if($user) return false;
        $password = password_hash($password, PASSWORD_DEFAULT);
        return $this->create_user($name, $email, $password);
    }

    public function create_user($name, $email, $password) {
        $data = array(
            'name' => $name,
            'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
            'password' => $password,
            'created_at' => date('Y-m-d H:i:s')
        );
        return $this->insert($this->table, $data);
    }

    public function logout() {
        $_SESSION['logged_in'] = false;
    }
    
    public function get_user_by_email($email) {
        $user = $this->getrowobj("SELECT * FROM $this->table WHERE email='{$email}' ");
        if ($user) {
            return $user;
        } else {
            return FALSE;
        }
    }

    public function get_user_by_id($user_id) {
        $user = $this->getrowobj("SELECT * FROM $this->table WHERE id='{$user_id}' ");
        if ($user) {
            return $user;
        } else {
            return FALSE;
        }
    }

    public function update_user($userId, $data) {
        $statements = [];
        foreach($data as $dKey => $dVal) {
            $statements[] = "{$dKey} = '{$this->escape($dVal)}' ";
        }
        $sql = "UPDATE $this->table SET ";
        $sql .= implode(",",$statements);
        $sql .= " WHERE id='{$userId}' ";
        $res = $this->execute($sql);
        return $res;
    }

    public function resetPassword($userid, $password) {
        $new_password = password_hash($password, PASSWORD_DEFAULT);
        $res = $this->update_user($userid, ['password' => $new_password]);
        return $res;
    }
    
}
