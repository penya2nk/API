<?php

class Controller extends Application{
	
	
	var $GETDB = null;

	public function __construct(){
		
		parent::__construct();
		
		if (!$GLOBALS['CODEKIR']['LOGS']){

			if ($this->configkey=='default'){
				$this->loadModel('helper_model');
				$this->loadModel('contentHelper');
				$GLOBALS['CODEKIR']['LOGS'] = new helper_model;	
			}
		}	
	}
	
	
	function index()
	{
		global $CONFIG, $LOCALE, $basedomain, $rootpath, $title, $DATA, $app_domain, $CODEKIR;
		$filePath = APP_CONTROLLER.$this->page.$this->php_ext;
		
		$this->view = $CODEKIR['smarty'];
		$this->view->assign('basedomain',$basedomain);
        $this->view->assign('rootpath',$rootpath);
		$this->view->assign('page',$DATA[$this->configkey]);
		
		
		if ($this->configkey=='default')$this->view->assign('user',$this->isUserOnline());
		if ($this->configkey=='admin')$this->view->assign('admin',$this->isAdminOnline());
		if ($this->configkey=='dashboard')$this->view->assign('dashboard',$this->isAdminOnline());
		if ($this->configkey=='services')$this->view->assign('services',$this->isAdminOnline());
		// $this->inject();
		// pr($this->isUserOnline());
		// exit;

		//inject Data

		if ($this->configkey=='default'){
			$this->view->assign('dateToday',date('Y-m-d'));
			$this->view->assign('banner_home',$this->getBannerHome());
			$this->view->assign('agenda',$this->getAgenda());
			$this->view->assign('kliping_index',$this->getKliping());
		}
		
		
		if (file_exists($filePath)){
			
			if ($DATA[$this->configkey]['page']!=='login'){
				if (array_key_exists('admin',$CONFIG)) {

					if (!$this->isAdminOnline()){
						redirect($basedomain.$CONFIG[$this->configkey]['login']);
						exit;
					}
				}

				if (array_key_exists('dashboard',$CONFIG)) {
					
					if (!$this->isAdminOnline()){
						redirect($basedomain.$CONFIG[$this->configkey]['login']);
						exit;
					}
				}

			}

			if ($this->configkey == 'default'){
				if ($DATA[$this->configkey]['page']=='login'){

					/* remove session if user exist in same browser */
					$ignoreFunc = array('validate','accountValid','doLogin','doSignup');
					if (in_array($DATA[$this->configkey]['function'], $ignoreFunc)){
						// do nothing
					}else{
						if ($this->isUserOnline()){
						// redirect($CONFIG[$this->configkey]['default_view']);
						redirect($basedomain);
					}

					
					exit;
					}

				}
			}
			// pr($DATA);
			if ($this->configkey == 'admin'){
				if ($DATA[$this->configkey]['page']=='login'){
					if ($this->isAdminOnline()){
					redirect($CONFIG[$this->configkey]['default_view']);
					exit;
					}
				}
			}

			if ($this->configkey == 'dashboard'){ 
				if ($DATA[$this->configkey]['page']=='login'){
					if ($this->isAdminOnline()){
					redirect($CONFIG[$this->configkey]['default_view']);
					exit;
					}
				}
			}

			if ($this->configkey == 'services'){  
				if ($DATA[$this->configkey]['page']=='login'){
					if ($this->isAdminOnline()){
					redirect($CONFIG[$this->configkey]['default_view']);
					exit;
					}
				}
			}

			
			include $filePath;
			
			$createObj = new $this->page();
			
			$content = null;
			if (method_exists($createObj, $this->func)) {
				
				$function = $this->func;
				
				$content = $createObj->$function();
				$this->view->assign('content',$content);
			} else {
				
				if ($CONFIG['default']['app_debug'] == TRUE) {
					show_error_page($LOCALE[$this->configkey]['error']['debug']); exit;
				} else {
					
					redirect($CONFIG[$this->configkey]['base_url']);
					
				}
				
			}
			
			// $masterTemplate = APP_VIEW.'master_template'.$this->php_ext;
			$masterTemplate = APP_VIEW.'master_template'.$this->html_ext;
			if (file_exists($masterTemplate)){
				
				$title = $this->page;
				// $this->view->display(APP_VIEW.$fileName);
				$this->view->display($masterTemplate);
				// include $masterTemplate;
			
			}else{
				
				show_error_page($LOCALE[$this->configkey]['error']['missingtemplate']); exit;
			}
			
		}
		
	}
	
	function isUserOnline()
	{
		$session = new Session;
		
		// $userOnline = @$_SESSION['user'];
		$userOnline = $session->get_session();
		
		if ($userOnline){
			return $userOnline;
		}else{
			return false;
		}
		
	}
	
	function isAdminOnline()
	{
		global $CONFIG;
		
		if (!$this->configkey) $this->configkey = 'admin';
		$uniqSess = sha1($CONFIG['admin']['root_path'].'codekir-v0.1'.$this->configkey);
		$session = new Session;
		$userOnline = $session->get_session();
		// vd($userOnline);exit;
		if ($userOnline){
			return $userOnline;
		}else{
			return false;
		}
		
	}
	
	function inject()
	{
		$session = new Session;
		
		$data = array('id'=>1,'name'=>'ovancop');
		$session->set_session($data);
	}
	
	function loadLeftView($fileName, $data="")
	{
		global $CONFIG, $basedomain;
		$php_ext = $CONFIG[$this->configkey]['php_ext'];
		
		if ($data !=''){
			/* Ubah subkey menjadi key utama */
			foreach ($data as $key => $value){
				$$key = $value;
			}
		}
		
		
		/* include file view */
		if (is_file(APP_VIEW.$fileName.$php_ext)) {
			if ($fileName !='') $fileName = $fileName.'.php';
			include APP_VIEW.$fileName;
			
			return ob_get_clean();
		
		}else{
			show_error_page('File not exist');
			return FALSE;
		}
		
		//return TRUE;
	}
	
	
	/* under develope */
	// function assign($key, $data)
	// {
		// return array($key => $data);
	// }
	
	function getModelHelper($param=false)
	{
		
		//$getDB = $this->loadModel('helper_model');
		
		$showFunct = $this->GETDB->getData_sel($param);
		
		if ($showFunct) return $showFunct;
		return false;
	}
	
	function validatePage()
	{
		global $basedomain, $CONFIG, $DATA;
		if (!$this->isUserOnline()){
			
			redirect($basedomain.$CONFIG[$this->configkey]['login']);
			exit;
		}else{
		
			if ($DATA[$this->configkey]['page'] == $CONFIG[$this->configkey]['login']){
				
				redirect($basedomain.$CONFIG[$this->configkey]['default_view']);
				exit;
			}
		}
		
		
	}
	
	public function loadMHelper()
	{
		$this->GETDB = $this->loadModel('helper_model');
		return $this->GETDB;
	}
	
	/*Function Untuk Meload jumlah Data*/
	function loadCountData($table,$categoryid=false,$articletype,$condition=false)

	{
		//	memanggil helper model yang sudah ada pada $GETDB
		if (!$this->GETDB)$this->GETDB = new helper_model();
		//	memanggil funtion getCountData yang terdapat pada model helper_model

		$data = $this->GETDB->getCountData($table,$categoryid,$articletype,$condition);

		$this->GETDB = null;
		if ($data) return $data;
		return false;
	}
	
	function loadCountData_search($table,$search=false)
	{
		//	memanggil helper model yang sudah ada pada $GETDB
		if (!$this->GETDB)$this->GETDB = new helper_model();
		//	memanggil funtion getCountData yang terdapat pada model helper_model
		$data = $this->GETDB->getCountData_search($table,$search);
		
		$this->GETDB = null;
		if ($data) return $data;
		return false;
	}
	
	function sidebar($table,$content=1, $type=false, $start=0, $limit=5)
	{
		/*
		content = categoryID
		type 	= articleType
		start 	= paging start
		Limit 	= paging limit
		*/
		
		if (!$this->GETDB)$this->GETDB = new helper_model();
		$helper = $this->GETDB->getSidebar($table,$content, $type, $start, $limit);
		if ($helper) return $helper;
		return false;
	}

	function log($action='surf',$comment)
	{
		$getHelper = new helper_model;

		$getHelper->logActivity($action,$comment);

	}

	function modified_array_column($data, $param)
	{
		if ($data){
			foreach ($data as $key => $value) {
				$newData[] = $value[$param];
			}
			return $newData;
		}
		
		return false;
		
	}

	function array_flatten($array) { 
	  if (!is_array($array)) { 
	    return FALSE; 
	  } 
	  $result = array(); 
	  foreach ($array as $key => $value) { 
	    if (is_array($value)) { 
	      $result = array_merge($result, array_flatten($value)); 
	    } 
	    else { 
	      $result[$key] = $value; 
	    } 
	  } 
	  return $result; 
	}

	function getBannerHome(){
		$getHelper = new contentHelper;
		$data = $getHelper->getBanner(0,5);
		//pr($data);exit;
		if (!empty($data)) {return $data;}
		else return false;
	}

	function getAgenda()
	{
		global $basedomain;
		$getHelper = new helper_model;
		$data = $getHelper->getNews(false,$cat=2, $type=0,0,100);
        // pr($data);

        foreach ($data as $key => $value) {
        	$start = $value['start'];
        	$count = array_count_values($this->modified_array_column($data, 'start'));
		}

		foreach ($count as $key => $value) {
        	$result[] = array('date'=>$key,'badge'=>false, 'title'=> 'dasd');
		}

		//pr($result);
		logFile(serialize($result));
		if (!empty($data)) {return json_encode($result);}
        else if (empty($data)){return json_encode('empty');}
		else return false;
	}

	function getKliping(){
		$getHelper = new contentHelper;
		$data = $getHelper->getNews($id=false,$cat=1,$type=2,0,3);
		//pr($data);exit;
		if (!empty($data)) {return $data;}
		else return false;
	}
	
}

?>
