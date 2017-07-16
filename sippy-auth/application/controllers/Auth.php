<?php


class Auth extends Sippy_controller {

    protected $authModel;
    protected $sess;


    public function __construct() {
        parent::__construct();
        $this->Helper('Auth_helper');
        $this->Helper('Session_helper');
        $this->authModel = $this->Model('Auth_model');
        $this->Plugin('Mailer');

        $this->sess = new Session_helper;
    }

    public function login() {
        if(Auth_helper::logged_in()) $this->redirect('main');
        $data['messSuccess'] = $this->flash->message('success');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf'] === $this->sess->get('csrf_token')) {
                //set variables
                $email = $_POST['email'];
                $password = $_POST['password'];
                $data['email'] = $email;

                $validator = $this->validate($_POST, [
                    'email' => 'required|min:5',
                    'password' => 'required',
                ]);

                if (count($validator) > 0) {
                    $data['errors'] = $validator;
                } else {

                    $res = $this->authModel->login([
                        'email' => $email,
                        'password' => $password
                    ]);

                    if ($res && !is_array($res)) {
                        //re-set csrf for session
                        $this->sess->set('csrf_token',$this->security->generate_csrf_token());
                        $this->redirect('main');
                    } else {
                        // set custom model error
                        $data['errors'] = $res['errors'];
                    }
                }
            }
        }
        $template = $this->View('auth/login_view',$data);
        $template->render();
    }

    public function register() {
        if(Auth_helper::logged_in()) $this->redirect('main');
        $data[] = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            if ($_POST['csrf'] === $this->sess->get('csrf_token')) {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $data['name'] = $name;
                $data['email'] = $email;

                $validator = $this->validate($_POST, [
                    'name' => 'required',
                    'email' => 'required|min:5',
                    'password' => 'required|min:6',
                ]);

                if (count($validator) > 0) {
                    $data['errors'] = $validator;
                } else {
                    $res = $this->authModel->register($name, $email,$_POST['password']);
                    if (!empty($res)) {
                        $this->flash->message('success','You have been registered');
                        $this->redirect('auth/login');
                    }
                }
            }
        }
        $template = $this->View('auth/register_view',$data);
        $template->render();
    }

    public function logout() {
        if(!Auth_helper::logged_in()) $this->redirect('auth/login');
        $this->authModel->logout();
        $this->sess->destroy();
        $this->redirect('auth/login');
    }

    public function forgot() {
        if(Auth_helper::logged_in()) $this->redirect('main');
        $data['messSuccess'] = $this->flash->message('success');
        $data[] = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf'] === $this->sess->get('csrf_token') ) {
                $email = $_POST['email'];
                $data['email'] = $email;

                $validator = $this->validate($_POST, [
                    'email' => 'required'
                ]);

                if (count($validator) > 0) {
                    $data['errors'] = $validator;
                } else {
                    $user = $this->authModel->get_user_by_email($email);
                    if ($user)
                    {
                        $slug = md5($user->id . $user->email . date('Ymd'));
                        $siteUrl = site_url('auth/reset/'. $user->id .'/'. $slug);

                        $message ='To reset your password please click the link below and follow the instructions:<br />
      
<a href="'. sprintf("%s",$siteUrl) .'">'. sprintf("%s",$siteUrl) .'</a><br /><br />

If you did not request to reset your password then please just ignore this email and no changes will occur.
Note: This reset code will expire after '. date('j M Y') .'.';

                        $send = Mailer::make()
                            ->setTo('einnor212@gmail.com', 'Einnor')
                            ->setFrom('sippy@noreply.com', 'Sippy')
                            ->setSubject('Reset your Password')
                            ->setMessage($message)
                            ->setHtml()
                            ->setWrap(100)
                            ->send();

                        //echo ($send) ? 'Email sent successfully' : 'Could not send email';
                        if ($send)
                        {
                            $this->flash->message('success','Please check your email box to change your password, check the spam box as well to make sure.');
                            $this->redirect('auth/forgot');
                        }

                    }
                }
            }
        }
        $template = $this->View('auth/forgot_view',$data);
        $template->render();

    }

    public function reset($user_id = null, $hash = null) {
        if (Auth_helper::logged_in()) $this->redirect('main');
        $data['messSuccess'] = $this->flash->message('success');

        if (!$user_id) die('Invalid reset code.');
        if (!$hash) die('Invalid reset code.');

        $user = $this->authModel->get_user_by_id($user_id);
        unset($user->password);
        $slug = md5($user->id . $user->email . date('Ymd'));
        if ($hash != $slug) die('Invalid reset code.');

        $data['user_id'] = $user_id;
        $data['hash'] = $hash;

        //POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf'] === $this->sess->get('csrf_token') ) {
                $password       = $_POST['password'];
                $confpassword   = $_POST['confpassword'];

                $validator = $this->validate($_POST, [
                    'password'      => 'required|min:6',
                    'confpassword'  => 'required|min:6'
                ]);

                if (count($validator) > 0) {
                    $data['errors'] = $validator;
                } else {
                    //process
                    if ($password === $confpassword) {
                        // match ok
                        $res = $this->authModel->resetPassword($user->id, $password);
                        if ($res) {
                            $this->flash->message('success','You have changed your password, you may now login');
                            $this->redirect('auth/login');
                        }
                    } else {
                        $data['errors'] = [
                            'Passwords do not match'
                        ];
                    }
                }
            }
        }


        $template = $this->View('auth/reset_view',$data);
        $template->render();

    }

}

