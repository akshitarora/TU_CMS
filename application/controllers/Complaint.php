<?php
class Complaint extends CI_Controller{
	public function developers($page = 'developers')
	{
        if ( ! file_exists(APPPATH.'/views/complaint/'.$page.'.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }

		$data['title'] = ucfirst($page); // Capitalize the first letter
        $this->load->view('templates/header_static',$data);
        $this->load->view('complaint/'.$page);
        $this->load->view('templates/footer');
	}
	
	public function instructions($page='instruction'){
		if ( ! file_exists(APPPATH.'/views/complaint/'.$page.'.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }

		$data['title'] = ucfirst($page.'s'); // Capitalize the first letter
        $this->load->view('templates/header_static',$data);
        $this->load->view('complaint/'.$page);
        $this->load->view('templates/footer');
	}
	
	public function home($page='index'){
		if ( ! file_exists(APPPATH.'/views/complaint/'.$page.'.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }

		$data['title'] = "Home"; 
        $this->load->view('templates/header',$data);
        $this->load->view('complaint/'.$page);
        $this->load->view('templates/footer');
	}
	
	public function sign_in($page='login'){
		if ( ! file_exists(APPPATH.'/views/complaint/'.$page.'.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }

		$data['title'] = 'Sign In'; 
        $this->load->view('templates/header',$data);
        $this->load->view('complaint/'.$page);
	}
	
	public function contact($page='contact'){
		if ( ! file_exists(APPPATH.'/views/complaint/'.$page.'.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }
		session_start();
		$data['title'] = ucfirst($page.' Us'); // Capitalize the first letter
        $this->load->view('templates/header_static',$data);
        $this->load->view('complaint/'.$page);
		$this->load->view('templates/footer');
		unset($_SESSION['stmt']);
	}
	
	public function string_validate($str) {

		$str = filter_var($str, FILTER_SANITIZE_STRING);
		$str1 = str_replace("%","p","$str");
		/* @var $mysqli type */
		return $this->db->escape($str1);
	}
	
	public function insertContact(){
		$data=$this->input->post();
		//print_r($data);
		$data['name']	= 	$this->string_validate($data['name']);
		$data['email']	=	$this->string_validate($data['email']);
		$data['message']=	$this->string_validate($data['message']);
		$this->load->model('Outer_model');
		$this->Outer_model->contact($data);
		session_start();
		$_SESSION['stmt']=TRUE;
		$_SESSION['nm']=$data['name'];
		redirect('http://localhost/ci/index.php/complaint/contact/');
	}
	
	public function check_user() {
		$data['email']=$this->input->post('email');
		$data['password']=$this->input->post('password');
		$salt = "thispasswordcannotbehacked";
		$data['password'] = hash('sha256', $salt . $data['password']);
		session_start();
		$this->load->model('Outer_model');
		$result=$this->Outer_model->validate_user($data);
		if ($result == 'student')
			echo 'student/home/';
		else if ($result == 'caretaker' || $result == 'warden')
			echo 'admin/home/';
		else echo 0;
	}		
	
	public function forgotPassword($page='forgot'){
		if ( ! file_exists(APPPATH.'/views/complaint/'.$page.'.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }
		session_start();
		$data['title'] = ucfirst($page.' Password'); // Capitalize the first letter
        $this->load->view('templates/header_static',$data);
        $this->load->view('complaint/'.$page);
		session_unset();
	}
	
	public function checkEmail(){
		$this->load->helper('email');
		$email=$this->input->post('email');
		session_start();
		if(valid_email($email)){
			$email=trim($email);
			$this->load->model('Outer_model');
			$exists = $this->Outer_model->email_exists($email);
			if($exists){
				$this->send_reset_password_email($email, $exists);	
				$error="SUCCESS";
			}
			else $error = 'This email is not registered. Please provide your registered email...';
		}
		else{
			$error = 'Please enter a valid email...';
		}
		$_SESSION['error']=$error;
		redirect('http://localhost/ci/index.php/complaint/forgotPassword/');
	}
	
	function send_reset_password_email($email, $name){
		$email_code=sha1($email.$name);

		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => 465,
			'smtp_user' => 'imcool.saurabh@gmail.com', // change it to yours
			'smtp_pass' => '$mart90415', // change it to yours
			'mailtype' => 'html',
			'charset' => 'iso-8859-1',
			'wordwrap' => TRUE
		);
		
		$message = '<html>
		<body>
		<p>Dear '. $name .', <br><br>
		To reset your onlinehostelj.in password, <a href="http://localhost/ci/index.php/complaint/resetPassword/'. $email .'/'. $email_code .'/">click here</a>. <br><br>
		If you are not able to view the link above, copy and paste into your address bar: 
		http://localhost/ci/index.php/complaint/resetPassword/'. $email .'/'. $email_code .'/ <br><br>
		If this was not you, kindly ignore this email.<br><br>
		Thanks,<br>
		Developer
		</p>
		</body>
		</html>
		';
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");
		$this->email->from('imcool.saurabh@gmail.com'); // change it to yours
		$this->email->to($email);// change it to yours
		$this->email->subject('Password Reset at onlinehostelj.in');
		$this->email->message($message);
		$this->email->send();
	}
	
	public function resetPassword($email,$email_code){
		if ( ! file_exists(APPPATH.'/views/complaint/reset.php'))
        {
                // Whoops, we don't have a page for that!
                show_404();
        }
		$this->load->model('Outer_model');
		$exists = $this->Outer_model->email_exists($email);
		if($exists){
			$name=$exists;
			$email_newcode=sha1($email.$name);
			if($email_code == $email_newcode){
				$data['title'] = ucfirst('Reset Password'); // Capitalize the first letter
				$data['email']=$email;
				$this->load->view('templates/header_static',$data);
				$this->load->view('complaint/reset',$data);		
			}
		}
		else /**REDIRECT to some error page**/;
	}
	
	function updatePassword(){
		$email=$this->input->post('email');
		$pass=$this->input->post('pass');
		$repass=$this->input->post('repass');
		$this->load->model('Outer_model');
		$exists = $this->Outer_model->email_exists($email);
		if($exists){
			if($pass==$repass){
				$salt = "thispasswordcannotbehacked";
				$pass = hash('sha256', $salt . $pass);
				$this->Outer_model->updatePass($email,$pass);
			}
		}
		else /**REDIRECT to some error page**/;
	}
	
	public function logout(){
		session_start();
		if (!isset($_SESSION['id']))
			header('location: http://localhost/ci/index.php/complaint/home/');
		session_unset();
		session_destroy();
		header('location: http://localhost/ci/index.php/complaint/home/');		
	}

	
}
?>