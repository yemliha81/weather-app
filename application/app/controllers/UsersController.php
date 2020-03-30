<?php
declare(strict_types=1);
class UsersController extends ControllerBase
{
    
    public function indexAction()
    {
       $cities = Cities::find();
       $this->view->cities = $cities;
    }

    public function registerAction()
    {
        $this->view->disable();
        if ($this->request->isPost()) {

            $email = $this->request->getPost('email');
            $userCheck = Users::findFirst("email='$email'");
            /**Check if this email address is already used before */
            if($userCheck)
            {
                $response = array('status' => '300', 'message' => 'This e-mail address is already taken.');
                echo json_encode($response);
                die();
            }


            $user = new Users();
            $user->email = $this->request->getPost('email');
            $user->password = md5($this->request->getPost('password'));
            $user->city_id = $this->request->getPost('city_id');
            $user->language = $this->request->getPost('language');
            $user->opr_system = $this->getosAction();
            $user->premium_user = 0;
            $user->inserted_date = date('Y-m-d H:i:s', time());
            
            if($user->save() === false)
            {
                $response = array('status' => '300', 'message' => 'Save error!');
                echo json_encode($response);
            }
            else
            {
                $response = array('status' => '201', 'message' => 'Saved successfully!');
                echo json_encode($response);
            }
        }
    }

    public function updateAction()
    {
        $this->view->disable();
        if ($this->request->isPost()) {
            
            $token = $this->request->getHeader('authToken');
            $userid = $this->request->getPost('id');
            $user = Users::findFirst("id='$userid'");
            
            if($user->auth_token == $token)
            {
                $last_login_date = $user->last_login;
                if( $this->isTokenValid($last_login_date) === true )
                {
                    $user->email = $this->request->getPost('email');
                    $user->password = md5($this->request->getPost('password'));
                    $user->city_id = $this->request->getPost('city_id');
                    $user->language = $this->request->getPost('language');

                    if($user->save() === false)
                    {
                        $response = array('status' => '300', 'message' => 'error');
                        echo json_encode($response);
                    }
                    else
                    {
                        $response = array('status' => '200', 'message' => 'User updated successfully!');
                        echo json_encode($response);
                    }

                }else
                {
                    $response = array('status' => '300', 'message' => 'Token has expired');
                    echo json_encode($response);
                }
            }
            else
            {
                $response = array('status' => '300', 'message' => 'Token is not valid');
                echo json_encode($response);
            }
            
        }
        else
        {
            $response = array('status' => '300', 'message' => 'Wrong method was used.');
            echo json_encode($response);
        }
    }

    public function loginAction()
    {
        
        $this->view->disable();

        if ($this->request->isPost()) {

            $email = $this->request->getPost('email');
            $password = md5($this->request->getPost('password'));
            $user = Users::findFirst("email='$email' AND password = '$password'");

            if ($user)
            {
                $last_login_date = $user->last_login;

                if($last_login_date !== null)
                {
                    /* If user is already looged in last 15 minutes */
                    if( $this->isTokenValid($last_login_date) === true )
                    {
                        $response = array('status' => '300', 'message' => 'User is already logged in.');
                        echo json_encode($response);
                    }
                    /* If user info is correct */
                    else
                    {
                        $this->loginProcess($user->id);
                    }
                }
                /*First login attempt */
                else
                {
                    $this->loginProcess($user->id);
                }
            }
            /*If user not found */
            else
            {
                $response = array('status' => '300', 'message' => 'User not found');
                echo json_encode($response);
            }
        }else
        {

        }
    }

    public function activateAction()
    {
        $this->view->disable();
        
        if ($this->request->isPost()) {
            // Access POST data
            $token = $this->request->getHeader('authToken');
            $code = $this->request->getPost('promotionCode');
            $userid = $this->request->getPost('id');
            $user = Users::findFirst("id='$userid'");

            if($user->premium_user == 1)
            {
                $response = array('status' => '300', 'message' => 'This user is already activated.');
                echo json_encode($response);
                die();
            }

            if(!$user)
            {
                $response = array('status' => '300', 'message' => 'User not found.');
                echo json_encode($response);
                die();
            }
            
            if($user->auth_token == $token)
            {
                $last_login_date = $user->last_login;
                if( $this->isTokenValid($last_login_date) === true )
                {
                    
                    if($user->premium_user == 1)
                    {
                        $response = array('status' => '300', 'message' => 'User is already premium');
                        echo json_encode($response);
                    }
                    else
                    {
                        
                        $promoCode = Promotion_codes::findFirst("code='$code' AND is_active=0");

                        if( count($promoCode) == 1 )
                        {
                            //Activate User

                            $user->premium_user = 1;
                            if($user->save() === false)
                            {
                                $response = array('status' => '300', 'message' => 'Activate Error');
                                echo json_encode($response);
                            }
                            else
                            {

                                $promoCode->is_active = 1;
                                $promoCode->user_id = $userid;

                                if($promoCode->save() === false)
                                {
                                    $response = array('status' => '300', 'message' => 'Promotion Code update Error');
                                    echo json_encode($response);
                                }
                                else
                                {
                                    $response = array('status' => '200', 'message' => 'Premium User activated successfully!');
                                    echo json_encode($response);
                                }

                            }

                        }
                        else
                        {
                            $response = array('status' => '300', 'message' => 'Promotion code is not valid.');
                            echo json_encode($response);
                        }
                    } 

                }else
                {
                    $response = array('status' => '300', 'message' => 'Token has expired');
                    echo json_encode($response);
                }
                

            }
            else
            {
                $response = array('status' => '300', 'message' => 'Token is not valid...');
                echo json_encode($response);
            }

        }
    }

    private function getosAction()
    {
        $this->view->disable();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

            $os_platform  = "Unknown OS Platform";

            $os_array     = array(
                                '/windows nt 10/i'      =>  'Windows 10',
                                '/windows nt 6.3/i'     =>  'Windows 8.1',
                                '/windows nt 6.2/i'     =>  'Windows 8',
                                '/windows nt 6.1/i'     =>  'Windows 7',
                                '/windows nt 6.0/i'     =>  'Windows Vista',
                                '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                                '/windows nt 5.1/i'     =>  'Windows XP',
                                '/windows xp/i'         =>  'Windows XP',
                                '/windows nt 5.0/i'     =>  'Windows 2000',
                                '/windows me/i'         =>  'Windows ME',
                                '/win98/i'              =>  'Windows 98',
                                '/win95/i'              =>  'Windows 95',
                                '/win16/i'              =>  'Windows 3.11',
                                '/macintosh|mac os x/i' =>  'Mac OS X',
                                '/mac_powerpc/i'        =>  'Mac OS 9',
                                '/linux/i'              =>  'Linux',
                                '/ubuntu/i'             =>  'Ubuntu',
                                '/iphone/i'             =>  'iPhone',
                                '/ipod/i'               =>  'iPod',
                                '/ipad/i'               =>  'iPad',
                                '/android/i'            =>  'Android',
                                '/blackberry/i'         =>  'BlackBerry',
                                '/webos/i'              =>  'Mobile'
                            );

            foreach ($os_array as $regex => $value)
                if (preg_match($regex, $user_agent))
                    $os_platform = $value;

            return $os_platform;
        
    }

    private function loginProcess($userid)
    {
        $user = Users::findFirst("id='$userid'");
        $token = md5(uniqid());
        $user->auth_token = $token;
        $user->last_login = date('Y-m-d H:i:s', time());
        
        if($user->save() === false)
        {
            $response = array();
            $response['status'] = '300';
            $response['message'] = 'error';

            echo json_encode($response);
        }
        else
        {
            $response = array();
            $response['status'] = '200';
            $response['message'] = 'success';
            $response['premium_user'] = $user->premium_user;
            $response['auth_token'] = $user->auth_token;

            echo json_encode($response);

        }
    }

    private function isTokenValid($last_login_date)
    {
        $valid_seconds = 15*60;
        $last_login_to_time = strtotime($last_login_date);
        $passed_seconds = time() - $last_login_to_time;

        if($passed_seconds > $valid_seconds)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function cronAction()
    {
        $this->view->disable();
        $cities = Cities::find();

        foreach($cities as $city)
        {
            $dif = $city->time_dif;
            $city_time = time() + ($dif*3600);
            $city_hour = date("H", $city_time);

            if($city_hour == "09")
            {
                
                $users = Users::find("city_id ='$city->id'");

                if($users)
                {
                    foreach($users as $user)
                    {
                        if($user->premium_user == 1)
                        {
                            //Notification will be sent here.
                            $notf['message'] = "Weather is ".$city->weather." in ".$city->city_name." today.";
                            $notf['user'] = $user->email;
                            echo json_encode($notf);
                        }
                        
                    }
                }

                
            }


        }

    }
}
