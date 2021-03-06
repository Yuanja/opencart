<?php
class ControllerInformationContact extends Controller {
	
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$name_of_uploaded_file =
				basename($_FILES['uploaded_file']['name']);
			$path_of_uploaded_file = NULL;
			if(isset($name_of_uploaded_file)){
				//copy the temp. uploaded file to uploads folder
				$path_of_uploaded_file = DIR_UPLOAD . $name_of_uploaded_file;
				$tmp_path = $_FILES["uploaded_file"]["tmp_name"];
				
				if(is_uploaded_file($tmp_path))
				{
					if(!copy($tmp_path,$path_of_uploaded_file))
					{
						$errors .= '\n error while copying the uploaded file';
					}
				}
			}
			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$senderName=isset($this->request->post['name']) ? html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8') : $this->customer->getFirstName()." ".$this->customer->getLastName(); 
			$email=isset($this->request->post['email']) ? $this->request->post['email'] : $this->customer->getEmail(); 
			$emailSubject=NULL;
			if (isset($this->request->post['product_name'])){
				//Get product name and get the link
				$emailSubject = html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['product_name']), ENT_QUOTES, 'UTF-8');
			} else {
				$emailSubject = html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8');
			}

			$emailBody = NULL;
			if (isset($this->request->post['product_link'])){ //in the case of product enquiry
				$emailBody = $this->request->post['enquiry'];	
				$emailBody = $emailBody."\nProduct Link: ".html_entity_decode($this->request->post['product_link']);
			} else {
				$emailBody = $this->request->post['enquiry'];
			}
			
			$logline = "FROM: " . $email ." SUBJECT: " .$emailSubject ." BODY: " .$emailBody;
			$this->write_log($logline);
			
			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($email);
			$mail->setReplyTo($email);
			$mail->setSender($senderName);
			$mail->setSubject($emailSubject);
			$mail->setText($emailBody);
			
			if (isset($name_of_uploaded_file)){
				$mail->addAttachment($path_of_uploaded_file);
			}
			$mail->send();

			$this->response->redirect($this->url->link('information/contact/success'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_location'] = $this->language->get('text_location');
		$data['text_store'] = $this->language->get('text_store');
		$data['text_contact'] = $this->language->get('text_contact');
		$data['text_address'] = $this->language->get('text_address');
		$data['text_telephone'] = $this->language->get('text_telephone');
		$data['text_fax'] = $this->language->get('text_fax');
		$data['text_open'] = $this->language->get('text_open');
		$data['text_comment'] = $this->language->get('text_comment');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_enquiry'] = $this->language->get('entry_enquiry');

		$data['button_map'] = $this->language->get('button_map');

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['enquiry'])) {
			$data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$data['error_enquiry'] = '';
		}
		
		if (isset($this->error['upload'])) {
			$data['error_upload'] = $this->error['upload'];
		} else {
			$data['error_upload'] = '';
		}
		
		if (isset($this->error['captcha'])) {
			$data['error_captcha'] = $this->error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}
		
		$data['button_submit'] = $this->language->get('button_submit');

		$data['action'] = $this->url->link('information/contact');

		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('config_image_location_width'), $this->config->get('config_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['fax'] = $this->config->get('config_fax');
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');

		$data['locations'] = array();

		$this->load->model('localisation/location');

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('config_image_location_width'), $this->config->get('config_image_location_height'));
				} else {
					$image = false;
				}

				$data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				);
			}
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}
		
		if(isset($this->request->post['product_link'])){
			$data['product_link'] = $this->request->post['product_link'];
		} else {
			$data['product_link'] = NULL;
		}
		
		if (isset($this->request->post['product_name'])){
			$data['product_name'] = $this->request->post['product_name'];
		} else {
			$data['product_name'] = NULL;
		}
		
		if (isset($this->request->get['rfq']) 
				|| isset($this->request->post['rfq'])){
			$data['rfq'] = '1';
		} else {
			$data['rfq'] = NULL;
		}
		
		if (isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'];
		} else {
			$data['enquiry'] = '';
		}

		if ($this->config->get('google_captcha_status')) {
			$this->document->addScript('https://www.google.com/recaptcha/api.js');
			$data['site_key'] = $this->config->get('google_captcha_key');
		} else {
			$data['site_key'] = '';
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/contact.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/information/contact.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/information/contact.tpl', $data));
		}
	}

	public function success() {
		$this->load->language('information/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_message'] = $this->language->get('text_success');

		$data['button_continue'] = $this->language->get('button_continue');

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/success.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/success.tpl', $data));
		}
	}

	protected function validate() {
		if ($this->config->get('google_captcha_status')) {
			$recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('google_captcha_secret')) . '&response=' . $this->request->post['g-recaptcha-response'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);
		
			$recaptcha = json_decode($recaptcha, true);
		
			if (!$recaptcha['success']) {
				$this->error['captcha'] = $this->language->get('error_captcha');
			}
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
			$this->error['enquiry'] = $this->language->get('error_enquiry');
		}
		
		if(isset($_FILES['uploaded_file']['name'])){
			//get the file extension of the file
			$type_of_uploaded_file =
			    substr($name_of_uploaded_file,
			    strrpos($name_of_uploaded_file, '.') + 1);
			 
			$size_of_uploaded_file =
			    $_FILES["uploaded_file"]["size"]/1024;//size in KBs
			//Settings
			$max_allowed_file_size = 500; // size in KB
			$allowed_extensions = array("jpg", "jpeg", "gif", "bmp");
			
			//Validations
			if($size_of_uploaded_file > $max_allowed_file_size )
			{
				$this->error['upload'] = "\n Size of file should be less than $max_allowed_file_size";
			}
			
			//------ Validate the file extension -----
			$allowed_ext = false;
			for($i=0; $i<sizeof($allowed_extensions); $i++)
			{
				if(strcasecmp($allowed_extensions[$i],$type_of_uploaded_file) == 0)
				{
					$allowed_ext = true;
				}
			}
			
			if(!$allowed_ext)
			{
				$this->error['upload'] = "\n The uploaded file is not supported file type. ".
						" Only the following file types are supported: ".implode(',',$allowed_extensions);
			}
		}
		return !$this->error;
	}
	
	function write_log($message, $logfile='') {
		// Determine log file
		if($logfile == '') {
			// checking if the constant for the log file is defined
			$logfile = DIR_LOGS."inquires.log";

			// the constant is not defined and there is no log file given as input
		}else {
			error_log('No log file defined!',0);
			return array(status => false, message => 'No log file defined!');
		}
	
		// Get time of request
		if( ($time = $_SERVER['REQUEST_TIME']) == '') {
			$time = time();
		}
	
		// Get IP address
		if( ($remote_addr = $_SERVER['REMOTE_ADDR']) == '') {
			$remote_addr = "REMOTE_ADDR_UNKNOWN";
		}
	
		// Get requested script
		if( ($request_uri = $_SERVER['REQUEST_URI']) == '') {
			$request_uri = "REQUEST_URI_UNKNOWN";
		}
	
		// Format the date and time
		$date = date("Y-m-d H:i:s", $time);
	
		// Append to the log file
		if($fd = @fopen($logfile, "a")) {
			$result = fputcsv($fd, array($date, $remote_addr, $request_uri, $message));
			fclose($fd);
	
			if($result > 0)
				return array(status => true);
			else
				return array(status => false, message => 'Unable to write to '.$logfile.'!');
		}
		else {
			return array(status => false, message => 'Unable to open log '.$logfile.'!');
		}
	}
}
