<?php
App::uses('AppController', 'Controller');

class UsersController extends AppController {
    var $name = 'Users';
    public $helpers = array('Html', 'Form');
    public $uses = array('User','WorkerJob','Role');
    public $components = array( 'Common', 'Auth', 'Session', 'Cookie', 'RequestHandler', 'Email', 'PaymentHandlerPaypal');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login','signup','forgot_password','contact','is_user_available','is_invalid_email','reset_password','get_list_orders' ) );
    }

    /*
     * login
     */
    public function login() {

        if($this->Session->check('Auth.User')){
            $this->redirect(array('controller'=>'pages','action' => 'display'));
        }

        if ($this->request->is('post')) {

            if ($this->Auth->login()) {
                $this->redirect(array('action' => 'user_dashboard'));
            } else {
                $this->Session->setFlash("Invalid username or password !",'default',array('class'=>'alert alert-danger'));
            }
        }
    }

    /*
     * admin login
     * @param:null
     * @return:null
     */
    public function admin_login() {
        $this->layout = 'admin_login';
        if($this->Session->check('Auth.User')){
            $this->redirect(array('controller'=>'dashboards','action' => 'display'));
        }

        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Session->setFlash("Invalid username or password !",'default',array('class'=>'error'));
            }
        }
    }

    /*
     * Logout
     */
    public function logout() {
        $this->redirect($this->Auth->logout());
    }

    /*
     * user signup
     * @return null
     * @param null
     */
    public function signup() {

        if ($this->request->is('post')) {

            // START : photo upload
            /*$photo = $this->request->data['User']['photo'];
            if ($photo['name']) {
                $result = $this->FileHandler->uploadImage($photo);
                if ($result) {
                    $photo = $this->FileHandler->_uploadimgname;
                }else {
                    $photo = '';
                }
            }else{
                $photo = '';
            }
            $this->request->data['User']['photo'] = $photo;*/
            //END: photo upload

             //Start Save Seller information
            $seller = array('Seller'=>array(
                'marketplace_id'        =>$this->request->data['User']['marketplace_id'],
                'seller_id'             =>$this->request->data['User']['seller_id'],
                'access_key_id'         =>$this->request->data['User']['access_key_id'],
                'secret_access_key'     =>$this->request->data['User']['secret_access_key'],
                'msw_auth_token'        =>$this->request->data['User']['msw_auth_token']

                )
            );

            $this->loadModel('Seller');
            $this->Seller->create();
            $this->Seller->save($seller);

            // End Save Seller information

            $seller_id = $this->Seller->getLastInsertId();

            $this->User->create();

            $this->request->data['User']['role_id'] = 4;
            $this->request->data['User']['seller_id'] = $seller_id;
            $this->request->data['User']['status'] = '1';
            $email = $this->request->data['User']['email'];
            $this->request->data['User']['username'] = $email;

            $user = $this->request->data;

            if ($this->User->save($user)) {
                $this->__sendEmail(
                    array($this->Common->settings('site_name'),$this->Common->settings('site_email')),
                    $email,
                    'Thank you for your registration',
                    'signup',
                    $user
                );


                if ($this->Auth->login()) {
                    $this->Session->setFlash("You have successfully registered",'default',array('class'=>'alert alert-success'));
                    $this->redirect( array( 'controller'=>'pages','action' => 'display' ) );
                } else {
                    $this->Session->setFlash(__('Invalid username or password'),'default',array('class'=>'alert alert-danger'));
                }
            }

            $this->Session->setFlash("Unable to registered !",'default',array('class'=>'alert alert-danger'));

        }

        // country drop down
        $countries = $this->Common->Country();
        $this->set('countries',$countries);

    }


    /**
     * payment
     */

    public function payment() {

        $settings = $this->Common->settings();
        $this->set('account_charge', $settings['account_charge'] );

        if( $this->request->is('post') ) {

            $CartContent[0]=array(
                "Name"=>'Fitdad Account',
                "Code"=>time(),
                "UnitPrice"=>$settings['account_charge'],
                "Quantity"=>1,// quantity should ne changed
                "UseShippingCost"=>false,
                "ShippingCost"=>0,
                "UseHandlingCost"=>false,
                "HandlingCost"=>0
            );

            $user = $this->Session->read('Auth.User');


            echo $this->PaymentHandlerPaypal->PayPalBuyNowButtonCustom(
                $PayPalGateway="https://www.paypal.com/cgi-bin/webscr",
                $Command="_cart",
                //$BusinessEmail="info@themexgroup.com",
                $BusinessEmail=$settings['email'],
                $Item=$CartContent,
                $CurrencyCode="USD",
                $UseShippingCost=false,
                $ShippingCost=0,
                $NoShippingAddress=false,
                $UseHandlingCost=false,
                $HandlingCost=0,
                $TAX=0,
                $AutomaticNotificationURL= Router::url('/users/paypal_ipn/'.$user['id'].'/'.$user['token'], true),
                $ReturnURLOnSuccess= Router::url('/categories/video_categories', true),
                $ReturnURLOnFailure= Router::url('/', true),
                $ButtonCaption=" Click here to Pay Now ",
                $ImageButtonSource="",
                $ExtraOption=array(),
                $NoteCaption="",
                $NoNote=true,
                $Custom="",
                $InvoiceNumber="",
                $ReturnToMerchantButtonCaption="Please click here to complete the process",
                $SimulationMode= false
            );

            echo '<script>
                    document.getElementById("checkout").submit();
                </script>';
        }

    }

    /** paypal ipn */
    public function paypal_ipn( $user_id=null, $token=null ) {
        $this->autoRender = false;

        if($this->User->findByIdAndToken( $user_id, $token ) ) {

            $user = array( 'User'=>array( 'id'=>$user_id,'is_paid'=>1 ) );

            if( $this->User->save($user) ) {
                $this->Session->setFlash( "You have successfully paid",'default',array('class'=>'success') );
                $this->Session->write('Auth.User.is_paid',1);
            }

        }else{
            $this->Session->setFlash( "Invalid request, Please try again ",'default',array('class'=>'error') );
            $this->redirect( array( 'action' => 'payment') );
        }

    }

    /*
     * @return bool|void
     * @throws NotFoundException
     */
    public function my_profile() {
        $user = $this->User->findByIdAndStatus($this->Session->read('Auth.User.id'),1);
        if ($this->request->is(array('user', 'put'))) {

            // start cover photo up
           /* $photo = $this->request->data['User']['photo'];
            if ($photo['name']) {
                $result = $this->FileHandler->uploadImage($photo);
                if ($result) {
                    $photo = $this->FileHandler->_uploadimgname;
                }else {
                    $photo = '';
                }
            }else{
                $photo = $user['User']['photo'];
            }

            $this->request->data['User']["photo"] = $photo;*/
            // end cover photo up

            $this->User->id = $user['User']['id'];
            $this->request->data['User']['date_updated'] = date("Y-m-d H:i:s");

            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash("Your profile has been updated.",'default',array('class'=>'alert alert-success'));
                $this->redirect($this->referer());
            }
            $this->Session->setFlash("Unable to update !",'default',array('class'=>'alert alert-danger'));
        }

        if (!$this->request->data) {
            $this->request->data = $user;
        }

        // country drop down
        $countries = $this->Common->Country();
        $this->set('countries',$countries);

    }

    /*
     * forgot password
     * @param:null
     * @return:
     */
    public function forgot_password() {
        if(!empty($this->request->data)) {
            $user_info = $this->User->findByEmail($this->request->data['User']['email']);
            if($user_info) {
                //this, token is not related with user token table, save token and token generated date time
                $user['User']['token'] = $this->Common->getToken();
                $user['User']['token_generated'] = date("Y-m-d H:i:s");
                $user['User']['id'] = $user_info['User']['id'];

                if($this->User->save($user)) {
                    // send stored token and other info into email
                    $user = $this->User->findByEmail($this->request->data['User']['email']);
                    $this->__sendEmail(
                        array($this->Common->settings('site_name'),$this->Common->settings('site_email')),
                        $user['User']['email'],
                        'Reset password request',
                        'forgot_password',
                        $user
                    );

                    $message = 'An email has been sent with a link to reset your password';
                    $this->Session->setFlash($message,'default',array('class'=>'alert alert-success'));
                    $this->redirect($this->referer());
                }

            } else {
                $message = 'No user was found with the submitted email address';
                $this->Session->setFlash($message,'default',array('class'=>'alert alert-danger'));
            }
        }
    }

    /*
     * reset password
     */
    public function reset_password($token = null){
        // submit reset password form
        if($this->request->is('post')) {
            $user = $this->User->findByToken($this->request->data['User']['token']);
            $user['User']['password'] = $this->request->data['User']['password'];
            if($this->User->save($user)) {
                $this->__sendEmail(
                    array($this->Common->settings('site_name'),$this->Common->settings('site_email')),
                    $user['User']['email'],
                    'Reset password',
                    'reset_password',
                    $user
                );

                $message = 'Your password has been reset successfully,you may login now';
                $this->Session->setFlash($message,'default',array('class'=>'alert alert-success'));

                $this->redirect(array('action'=>'login'));
            }

        }else{
            // redirect from email forgot password link
            $redirect = false;
            if($token){
                $user = $this->User->findByToken($token);
                if($user){
                    if($this->Common->countDays($user['User']['token_generated'])>1){
                        $this->Session->setFlash('Request timeout, Please try again','default',array('class'=>'alert alert-danger'));
                        $redirect = true;
                    }
                }else{
                    $this->Session->setFlash('Invalid token, Please try again','default',array('class'=>'alert alert-danger'));
                    $redirect = true;
                }
            }else{
                $this->Session->setFlash('Token is empty, Please try again','default',array('class'=>'alert alert-danger'));
                $redirect = true;
            }

            if($redirect){
                $this->redirect(array('action'=>'forgot_password'));
            }else{
                $this->set('token',$token);
            }
        }
    }



    /**
     * change password
     * @param:null
     * @return:
     */
    function change_password() {

        //pr($settings);
        if($this->request->isPost()) {

            $email = $this->Session->read('Auth.User.email');
            $user = $this->User->find('first',array('conditions'=>array('email'=>$email,'status'=>1)));
            $current_password = AuthComponent::password($this->request->data['User']['current_password']);


            if($current_password === $user['User']['password']) {

                $this->request->data['User']['id'] = $user['User']['id'];

                if($this->User->save($this->request->data)){
                    $user['User']['password']= $this->request->data['User']['password'];
                    $this->__sendEmail(
                        array($this->Common->settings('site_name'),$this->Common->settings('site_email')),
                        $user['User']['email'],
                        'Change password request',
                        'change_password',
                        $user
                    );

                    $this->Session->setFlash("Password has been successfully changed",'default',array('class'=>'alert alert-success'));
                    $this->redirect($this->referer());
                }else{
                    $this->Session->setFlash("Unable to change password !",'default',array('class'=>'alert alert-danger'));
                }

            } else {
                $this->Session->setFlash("Your current password didn't match",'default',array('class'=>'alert alert-danger'));
            }

        }
    }


    /*
     * create temporary password
     * @param $len
     * @return string
     */

    public function __createTempPassword($len) {
        $pass = '';
        $lchar = 0;
        $char = 0;
        for($i = 0; $i < $len; $i++) {
            while($char == $lchar) {
                $char = rand(48, 109);
                if($char > 57) $char += 7;
                if($char > 90) $char += 6;
            }

            $pass .= chr($char);
            $lchar = $char;
        }

        return $pass;
    }


    /*
     * view users from admin panel
     * @param:null
     * @return:null
     */
    public function admin_index($user_role_id = null) {
        if(!$this->Session->check('Auth.User')){
            $this->redirect(array('controller'=>'users','action' => 'login'));
        }

        if($this->request->isPost()){
            $this->Session->write("UserFilter_$user_role_id", $this->request->data['User']);
        }
        $where = $this->__builtContentWhere($user_role_id);

        $this->paginate = array(
            'conditions' => $where,
            'limit' => 30,
            'order' => array('id' => 'desc')
        );

        if($user_role_id == 2 ){ // Worker id = 2
            $page_title = "Worker";
        }elseif($user_role_id == 3){ // Client id = 3

            $page_title = "Client";
        }

        $users = $this->paginate('User');

        $this->set(compact('users','page_title'));
    }


    /*
     * create user from admin panel
     * @return null
     * @param null
     */
    public function admin_create() {
        if ($this->request->is('post')) {

            /*$get_file = $this->request->data['User']['photo']['name'];

            // START : photo upload
            $photo = $this->request->data['User']['photo'];
            if ($photo['name']) {
                $result = $this->FileHandler->uploadImage($photo);
                if ($result) {
                    $photo = $this->FileHandler->_uploadimgname;
                }else {
                    $photo = '';
                }
            }else{
                $photo = '';
            }
            $this->request->data['User']['photo'] = $photo;
            //END: photo upload*/

            $this->User->create();
            $role_id = $this->request->data['User']['role_id'];
            $this->request->data['User']['username'] = $this->request->data['User']['email'];

            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash("User has been successfully created",'default',array('class'=>'alert alert-success'));
                return $this->redirect(array('action' => 'admin_index',$role_id));
            }

            $this->Session->setFlash("Unable to save !",'default',array('class'=>'alert alert-danger'));

        }

        // country drop down
        $countries = $this->Common->Country();
        $roles = $this->Role->getRoles();
        $this->set(compact('countries','roles'));

    }



    /***
     * update user from admin panel
     * @param null $id
     * @return bool|void
     * @throws NotFoundException
     */
    public function admin_update($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid request !'));
        }

        $user = $this->User->findById($id);

        if (!$user) {
            throw new NotFoundException(__('Invalid request !'));
        }

        if ($this->request->is(array('user', 'put'))) {

            /*// start cover photo up
            $photo = $this->request->data['User']['photo'];
            if ($photo['name']) {
                $result = $this->FileHandler->uploadImage($photo);
                if ($result) {
                    $photo = $this->FileHandler->_uploadimgname;
                }else {
                    $photo = '';
                }
            }else{
                $photo = $user['User']['photo'];
            }

            $this->request->data['User']["photo"] = $photo;
            // end cover photo up*/

            $this->User->id = $id;
            $role_id = $this->request->data['User']['role_id'];
            $this->request->data['User']['username'] = $this->request->data['User']['email'];

            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash("Data has been updated.",'default',array('class'=>'alert alert-success'));
                return $this->redirect(array('action' => 'admin_index',$role_id));
            }
            $this->Session->setFlash("Unable to update !",'default',array('class'=>'alert alert-danger'));
        }

        if (!$this->request->data) {
            $this->request->data = $user;
            // country drop down

            $countries = $this->Common->Country();
            $roles = $this->Role->getRoles();
            $this->set(compact('countries','roles'));
        }


    }


    /*
     * delete user from admin panel
     * @param null $id
     * @throws MethodNotAllowedException
     */
    public function admin_delete($id = null) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }

        if ($this->User->delete($id)) {
            $this->Session->setFlash("Record has been successfully deleted !",'default',array('class'=>'alert alert-success'));
            return $this->redirect(array('action' => 'admin_index'));
        }
    }


    /*
     * reset filter
     */
    public function admin_reset($user_role_id=null){
        if($this->Session->check("UserFilter_$user_role_id")){
            $this->Session->delete("UserFilter_$user_role_id");
        }
        $this->redirect('index/'.$user_role_id);
    }

    /*
     * filter
     */
    public function __builtContentWhere($user_role_id){
        $filter = null;
        $conditions = array('AND'=>array('User.role_id'=>$user_role_id));

        if($this->Session->check("UserFilter_$user_role_id")){
            $filter = $this->Session->read("UserFilter_$user_role_id.filter");
        }
        if(!empty($filter)){
            $conditions = array('OR' => array(
                array('User.first_name LIKE' => '%' . $filter . '%'),
                array('User.last_name LIKE' => '%' . $filter . '%'),
                array('User.phone LIKE' => '%' . $filter . '%'),
                array('User.email LIKE' => '%' . $filter . '%')
            ));
        }

        return $conditions;
    }



    /**
     * @param $from
     * @param $to
     * @param $subject
     * @param $template
     * @param $viewVars
     * @return bool
     */
    public function __sendEmail($from, $to, $subject, $template,$viewVars)
    {
        if($_SERVER['HTTP_HOST']!='localhost' AND $_SERVER['HTTP_HOST']!='localhost:8080'){
            $this->Email->to = $to;
            $this->Email->bcc = $to;
            $this->Email->subject = $subject;
            $this->Email->replyTo =$from[1];
            $this->Email->from = $from[0].'<'.$from[1].'>';
            $this->Email->template =$template;
            $this->Email->sendAs = 'html';
            $this->set('user',$viewVars);
            //$this->Email->delivery = 'debug';
            $success = $this->Email->send();
            return $success;
        }
    }

    /**
     * contact
     */
    public function contact() {

        $this->autoRender = false;

        if($this->request->is('post')){
            $data = $this->request->data;
           $this->log($data);
            if(!empty($data)){
                $send_email = $this->__sendEmail(
                    array($this->Common->settings('site_name'),$data['contact']['email']),
                    $this->Common->settings('site_email'),
                    $data['contact']['subject'],
                    'contact',
                    $data
                );

                if($send_email){
                    $message = 'Your message has been successfully sent';
                }else{
                    $message = 'Message could not be sent';
                }

                $this->Session->setFlash($message,'default',array('class'=>'alert alert-success'));
                $this->redirect($this->referer());
            }
        }
    }

    /**
     * user details
     */
    public function admin_details($user_role_id = null, $user_id = null ){
        if(!empty($user_id)){
            $user = $this->User->findById($user_id);
            $jobs = $this->WorkerJob->find('all', array('conditions'=>array('user_id'=>$user_id)));

            if( $user_role_id == 2 ){ // Worker role id = 2
               $page_title = "Worker";
            }elseif( $user_role_id == 3 ){
                $page_title = "Client";
            }
            $this->set(compact('user','jobs','page_title'));

        }else{
            $this->Session->setFlash("This user could not found",'default',array('class'=>'alert alert-danger'));
        }
    }

    function is_user_available(){
        $this->autoRender = false;
        $user = $this->User->findByEmail($this->request->data['email']);
        if(empty($user)){
            echo 'true';die;
        }else{
            echo 'false';die;
        }

    }

    function is_invalid_email(){
        $this->autoRender = false;
        $user = $this->User->findByEmail($this->request->data['email']);
        if(!empty($user)){
            echo 'true';die;
        }else{
            echo 'false';die;
        }

    }


    /*
     *  Users Dashboard
     */

    public function user_dashboard(){

    }

}
?>