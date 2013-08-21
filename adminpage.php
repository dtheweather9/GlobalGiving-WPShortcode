<?php
function ggdisplay_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
    //Defaults
    	$default_orgid = 10000;
    	$default_apikey = "Enter API Key Here";
    	$default_refreshtime = 60;
    //Load Settings for OrgID
    	$orgidnum = get_option('ggdisplay_orgid');
    	if($orgidnum == false) {  //First time or if options are cleared
	    	update_option('ggdisplay_orgid',$default_orgid);
	    	$orgidnum = $default_orgid;
    	} 
    //Load Settings for API Key
    	$apikeynum = get_option('ggdisplay_apikey');
    	if($apikeynum == false) {  //First time or if options are cleared
	    	update_option('ggdisplay_apikey',$default_apikey);
	    	$apikeynum = $default_apikey;
    	}
    //Load Settings for refreshtime
    	$refreshtimenum = get_option('ggdisplay_refreshtime');
    	if($refreshtimenum == false) {  //First time or if options are cleared
	    	update_option('ggdisplay_refreshtime',$default_refreshtime);
	    	$refreshtimenum = $default_refreshtime;
    	}	
    //Options for select time
    	$refreshtimeops = array(
    		"None" => 0,
    		"Half Hour" => 30,
    		"Hour" => 60,
    		"Six hours" => 360,
    		"24 Hours" => 1440,
    		"One Week" => 10080,
    		);
    	update_option('ggdisplay_refreshtimeops',$refreshtimeops);	
  	//Post Response for OrgID
    	if(isset($_POST['orgid'])) { //Organization Post Response
    		if( get_option('ggdisplay_orgid') !== $_POST['orgid']) {
    		//New Organiation
    			update_option('ggdisplay_orgid',$_POST['orgid']);
    			$orgidnum = get_option('ggdisplay_orgid');
    		} 
    		
    	}
    //Post Response for API Key
    	if(isset($_POST['apikey'])) { //Organization Post Response
    		if( get_option('ggdisplay_apikey') !== $_POST['apikey']) {
    		//New Organiation
    			update_option('ggdisplay_apikey',$_POST['apikey']);
    			$apikeynum = get_option('ggdisplay_apikey');
    		} 
    		
    	}
    //Post Response for Refreshtime
    	if(isset($_POST['refreshtime'])) { //Organization Post Response
    		if( get_option('ggdisplay_refreshtime') !== $_POST['refreshtime']) {
    		//New Organiation
    			update_option('ggdisplay_refreshtime',$_POST['refreshtime']);
    			$refreshtimenum = get_option('ggdisplay_refreshtime');
    		} 
    		
    	}		
    //Form
    	echo "<h3>Global Giving Options Page</h3>";
    	echo '<form action="" method="post">';
    	echo 'Organization ID: <input type="number" name="orgid" min="1" max="99999" value="' . $orgidnum . '"><br>';
    	echo 'API Key: <input type="text" name="apikey" value="' . $apikeynum . '"><br>';
    	//Output Refreshtime
    		$arrkeys = array_keys($refreshtimeops);
    		$arrvalues = array_values($refreshtimeops);
    		echo 'Cache Time: <select name="refreshtime">';
    		for ($i=0;$i<count($arrkeys);$i++) {
    		 if (get_option('ggdisplay_refreshtime') == $arrvalues[$i]) {
    			echo '<option value="' . $arrvalues[$i] . '" selected>' . $arrkeys[$i] . '</option>';
    		 } else {
    			echo '<option value="' . $arrvalues[$i] . '">' . $arrkeys[$i] . '</option>';
    		 }	
    		}
    		echo '</select><br>';
    	
    	echo '<input type="submit" value="Submit">';
    	echo '</form>';
    //Option's form
    
    //Diagnostics
    /*	echo '<pre>';
    	print_r(array_keys($refreshtimeops));
    	echo '</pre>';
    	*/
}