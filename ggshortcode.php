<?php
//Object to array
function objectToArray($d) {
    if (is_object($d))
        $d = get_object_vars($d);
    return is_array($d) ? array_map(__METHOD__, $d) : $d;
}


//Function for Global Giving Get Contents - Used for caching
function gg_file_get_contents($url, $use_include_path = false, $context,$pageid,$projectid,$type,$forceupdate = false)
{
 //Diagnostic
 	//Type Check
		if ($type == "project" || $type == "image" || $type == "report") {
		} else {
			wp_die( __('Invalid Type used please contact system admin') );
		}
	//Define Metaname
		$gg_metaname = "gg_" . $projectid . "_" . $type;
		$gg_reportname = "gg_" . $projectid . "_" . $type . "_" . "data";
	//Get Meta Check	
		//$gg_metacheck1 = update_post_meta( $pageid, $metaname . "_test",time() );
		$gg_metacheck = get_post_meta( $pageid, $gg_metaname );
		//$gg_metacheck[1];
	//Get timeframe to change
		$gg_timechange = get_option('ggdisplay_refreshtime')*60; //Changes timechange to seconds
	//run logic	to determine if running update or pulling from database
		$gg_runupdate = 0;	
		if (count($gg_metacheck[0])==1) { //record queried once has been set
			if ( (time() - $gg_timechange) > $gg_metacheck[0]) { //Time has expired on the data
				$gg_runupdate = 1;	
				//echo "<p>Time Expired</p>";
			}
		} else {
		$gg_runupdate = 1; //Query not run ever
		//echo "<p>First Run</p>";
		}
	//Run last check to make sure data exhists
		$gg_metadata = get_post_meta( $pageid, $gg_reportname );
		if (count($gg_metadata[0])==0) {
			$gg_runupdate = 1;
			//echo "<p>No Stored Data</p>";
		}
	//Update or Pull from meta
		if($gg_runupdate == 1 || $forceupdate == true) { //Run Update
			$gg_filegetcontentsdata = file_get_contents($url, $use_include_path, $context);
			$gg_filegetcontentsdata = str_replace( '\\', '\\\\', $gg_filegetcontentsdata );
			update_post_meta( $pageid, $gg_metaname,time() );
			update_post_meta( $pageid, $gg_reportname,$gg_filegetcontentsdata );
			$gg_filegetcontentsdata = get_post_meta( $pageid, $gg_reportname );
		} else { //Use Metadata
			$gg_filegetcontentsdata = $gg_metadata;			
		}
		
		$gg_filegetcontentsdata = json_decode($gg_filegetcontentsdata[0]);
		$gg_filegetcontentsdata = objectToArray($gg_filegetcontentsdata);
		return $gg_filegetcontentsdata;
}

function ggendsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
/*
function gg_project( $atts, $content = null ){
	
	//Get passed data
	extract( shortcode_atts( array(
		'projectid' => '0',
		'giveforyouth' => false, //Is it a giveforyouth project
		'reports' => true, //Valid Values -> true (yes display reports), false (don't display reports), number (display only that number of reports)
		'projectdata' => true, //hide project header info to display only reports
		'images' => true,
		'bpgg' => true,
		), $atts ) );
		//$textline = "Projectid: " . $projectid;
	//Array the projects
		$projectidarr = explode(",",$projectid);
		$projectid = $projectidarr[0]; //Sets first called project as displayed project
	//Set Identidy Objects for images
		$iindex = 0;
	//Get API Key	
	$apikeynum = get_option('ggdisplay_apikey');
	if ($apikeynum == false) {
		exit("The API has not been set - please contact the system administrator");
	}
	//Set URL for Project
	$url = "https://api.globalgiving.org/api/public/projectservice/projects/collection/ids?api_key=" . get_option('ggdisplay_apikey') . "&projectIds=" . $projectid;
	//Project Rest Interface
		$opts = array('http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n" ) );
		$context = stream_context_create($opts);
		// Open the url using the HTTP headers set above
		$output = gg_file_get_contents($url, false, $context,get_the_ID(),$projectid,"project");
		if (count($output[0]) == 1) {
		$org_projectsoutput = json_decode($output[0],true);
		} else {
		$org_projectsoutput = json_decode($output,true);	
		}
		$org_projectsoutput = $org_projectsoutput['projects'];
		$org_projectsoutput = $org_projectsoutput['project'];
	//Diagnostic

	//Interpret
	 if ($giveforyouth == true) {
	 	$projectlink = str_replace("http://www.globalgiving.org","http://www.giveforyouth.org",$org_projectsoutput['projectLink']);
	 	$projectlink = str_replace("https://www.globalgiving.org","https://www.giveforyouth.org",$projectlink);	
	 } else {
	 	$projectlink = $org_projectsoutput['projectLink'];
	 }
	//Percent Complete Calcs
	$percentagefunded = ($org_projectsoutput['funding']/$org_projectsoutput['goal'])*100;
	//Return Project Info
	$ggreturn = "";
	if ($projectdata == true) {
	$ggreturn = $ggreturn .  '<div id="ggshort-project-div">';
	$ggreturn = $ggreturn .  '<div id="ggshort-project-title">' . $org_projectsoutput['title'] . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-link"><a href="'. $projectlink . '">'. $projectlink . "</a></div>";
	//Output Donate Box
		$ggreturn = $ggreturn .  '<div id="ggshort-ggprojectbox-div">';
		if ($giveforyouth == true) {
			$ggreturn = $ggreturn . '<script type="text/javascript" src="http://www.giveforyouth.org/javascript/widget/widget.js">  { "projectids" : "' . $projectid . '", "ggtid" : "5C0435AD095EF8BBE97C7F84F5D84D62" }  </script>';
		} else {
			$ggreturn = $ggreturn . '<script type="text/javascript" src="http://www.globalgiving.org/javascript/widget/widget.js">  { "projectids" : "' . $projectid . '", "ggtid" : "5C0435AD095EF8BBE97C7F84F5D84D62" }  </script>';	
		}
		$ggreturn = $ggreturn .  '</div>';
		$ggreturn = $ggreturn .  '<div id="ggshort-projectinfo-div">';
	//Output remainder
	$ggreturn = $ggreturn .  '<div id="ggshort-project-summary-title">'. 'Summary' . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-summary">'. $org_projectsoutput['summary'] . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-need-title">'. 'What is the issue, problem, or challenge?' . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-need">'. $org_projectsoutput['need'] . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-activity-title">'. 'How will this project solve this problem?' . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-activity">'. $org_projectsoutput['activities'] . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-longtermimpact-title">'. 'Potential Long Term Impact' . "</div>";
	$ggreturn = $ggreturn .  '<div id="ggshort-project-longtermimpact">'. $org_projectsoutput['longTermImpact'] . "</div>";
	$ggreturn = $ggreturn .  '</div>'; //End of Project Report Info Section
	if ($org_projectsoutput['status'] == "active") {
		$ggreturn = $ggreturn .  '<div id="ggshort-project-fundingdiv">';
		$ggreturn = $ggreturn .  '<div id="ggshort-project-funding-title">'. 'Funding Recieved: ' . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-funding">'. money_format('%(#10n', $org_projectsoutput['funding']) . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-remaining-title">'. 'Funds Remaining: ' . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-remaining">'. money_format('%(#10n', $org_projectsoutput['remaining']) . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-goal-title">'. 'Funding Goal: ' . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-goal">'. money_format('%(#10n', $org_projectsoutput['goal']) . "</div>";
		$ggreturn = $ggreturn .  "<div style='width:100px; background-color:white; height:30px; border:1px solid #000;'>
    		<div style='width:".$percentagefunded."px; background-color:#00ff00; height:30px;'></div></div>";
		$ggreturn = $ggreturn .  "</div>"; //End Funding Div Section
	} elseif ($org_projectsoutput['status'] == "funded") {
		$ggreturn = $ggreturn .  '<div id="ggshort-project-fundingdiv">';
		$ggreturn = $ggreturn .  '<div id="ggshort-project-funding-title">'. 'Funding Recieved: ' . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-funding">'. money_format('%(#10n', $org_projectsoutput['funding']) . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-remaining-title">'. 'Funds Remaining: ' . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-remaining">'. money_format('%(#10n', $org_projectsoutput['remaining']) . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-goal-title">'. 'Funding Goal: ' . "</div>";
		$ggreturn = $ggreturn .  '<div id="ggshort-project-goal">'. money_format('%(#10n', $org_projectsoutput['goal']) . "</div>";
		$ggreturn = $ggreturn .  "<div style='width:100px; background-color:white; height:30px; border:1px solid #000; margin-left: 25px; margin-right: 25px;'>
		    <div style='width:".$percentagefunded."px; background-color:#00ff00; height:30px;'></div></div>";
		$ggreturn = $ggreturn .  "</div>"; //End Funding Div Section		
	}
	
	//$ggreturn = $ggreturn . "<hr>";
	$ggreturn = $ggreturn .  '</div>'; //End of Project Div
	} //end no project header display
	//Output custom donate info here
	if($content !== null) { //Only output if valid
		$ggreturn = $ggreturn . '<div id="ggshort-customwidget">';
		$ggreturn = $ggreturn . $content;
		$ggreturn = $ggreturn .  "</div>";
	}
	
	//Optional Output: Powered by Global Giving
		
		if($bpgg == true) {
		$ggreturn = $ggreturn . '<div id="ggshort-widget">';
		$ggreturn = $ggreturn . '<div id="ggshort-widget-title">' . $org_projectsoutput['title'] . "</div>";
		$ggreturn = $ggreturn . '<div id="ggshort-widget-image">' . '<a href="'. $projectlink . '">' . '<img src="' . $org_projectsoutput['imageLink'] . '">' . "</a></div>";
		$ggreturn = $ggreturn . '<div id="ggshort-widget-givenow">' . '<a href="'. $projectlink . '">' . '<img src="https://www.globalgiving.org/img/buttons/give_now.gif">' . "</a></div>";
		$ggreturn = $ggreturn . '<div id="ggshort-widget-poweredby">' . '<a href="'. "http://www.globalgiving.org/" . '">' . '<img src="https://www.globalgiving.org/img/logos/powered_by_globalgiving.jpg">' . "</a></div>";
		$ggreturn = $ggreturn .  "</div>";
		}
	
	
//Begin Section for outputting reports
	//Set URL for Reports
	
	
  if($reports == true) {
   for ($z=0; $z<count($projectidarr); $z++) {
  	$projectid = $projectidarr[$z];
	$url = "https://api.globalgiving.org/api/public/projectservice/projects/" . $projectid . "/reports?api_key=" . get_option('ggdisplay_apikey');
	//Project Rest Interface
		
		$opts = array('http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n", ) );
		$context = stream_context_create($opts);
		// Open the url using the HTTP headers set above
		$reportsoutput = gg_file_get_contents($url, false, $context,get_the_ID(),$projectid,"report");
		$reportsoutput[0] = strip_tags($reportsoutput[0],"<br><em></em>");
		$org_reportsoutput = json_decode($reportsoutput[0],true);
		//Loop Through each entry
	 
	  //$ggreturn = $ggreturn . "Reports:<br>";
		for ($i=0;$i<count($org_reportsoutput['entries']);$i++) {
			$org_reportsentries[$i] = $org_reportsoutput['entries'][$i];
			$ggreturn = $ggreturn .  '<div id="ggshort-report-div">';
			$ggreturn = $ggreturn .  '<div id="ggshort-report-title">' . $org_reportsentries[$i]['title'] . "</div>";
			//Authors Loop
			$ggreturn = $ggreturn .  '<div id="ggshort-report-author-group">';
			for ($k=0;$k<count($org_reportsentries[$i]['authors']);$k++) {
				$gg_reportauthorstemp = $org_reportsentries[$i]['authors'][$k];
				$gg_reportauthors[$i][$k] = $gg_reportauthorstemp['name'];
				$ggreturn = $ggreturn .  '<div id="ggshort-report-author-title">'. 'Author(s): ' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-report-author">'. $gg_reportauthors[$i][$k] . "</div>";
			}
			$ggreturn = $ggreturn .  '</div>';
			//Return Date Published
				$ggdate = explode("T",$org_reportsentries[$i]['published']);
				$ggdateparse = strtotime("YY-MM-DD",$ggdate[1]);
			//Misc Outputs
			$ggreturn = $ggreturn .  '<div id="ggshort-report-pubdate-title">'. 'Published: ' . "</div>";
			//$ggreturn = $ggreturn .  '<div id="ggshort-report-pubdate">'. $org_reportsentries[$i]['published'] . "</div>";
			$ggreturn = $ggreturn .  '<div id="ggshort-report-pubdate">'. $ggdate[0] . "</div>";
			$ggreturn = $ggreturn .  '<div id="ggshort-report-content-title">'. 'Report: ' . "</div>";
			$ggreturn = $ggreturn .  '<div id="ggshort-report-content">'. $org_reportsentries[$i]['content'] . "</div>";
			$ggreturn = $ggreturn .  '<div id="ggshort-report-seemore-title">'. 'See More: ' . "</div>";
			
			if ($giveforyouth == true) {
	 			$reportlink = str_replace("http://www.globalgiving.org","http://www.giveforyouth.org",$org_reportsentries[$i]['id']);
	 			$reportlink = str_replace("https://www.globalgiving.org","https://www.giveforyouth.org",$reportlink);	
	 		} else {
	 			$reportlink = $org_projectsoutput['projectLink'];
	 		}
			$ggreturn = $ggreturn .  '<div id="ggshort-report-seemore"><a href="'. $reportlink . '">'. $reportlink . "</a></div>";
		}
		
	}
	$ggreturn = $ggreturn .  '</div>'; //End of Report Div
  }
  
  //Images
  	if ($images == true) {  //If images are off don't display (on by default)
  	
  	$projectid = $projectidarr[0]; //Sets first called project as displayed project
		/*
		for ($i=0;$i<count($org_reportsoutput['entries']);$i++) {//Report Loop
			for ($j=0; $j<count($org_reportsoutput['entries'][$i]['links']);$j++) { //Images Loop
				$report_images[$iindex]['url'] = $org_reportsoutput['entries'][$i]['links'][$j]['href'];
				$report_images[$iindex]['title'] = "";
				$iindex++;
			}
		}
		*/
		/*
		$url = "https://api.globalgiving.org/api/public/projectservice/projects/" . $projectid . "/imagegallery?api_key=" . get_option('ggdisplay_apikey');
		//Image Rest Interface
		
		$opts = array('http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n", ) );
		$context = stream_context_create($opts);
		// Open the url using the HTTP headers set above
		$imagesoutput = gg_file_get_contents($url, false, $context,get_the_ID(),$projectid,"image");
		$org_imagesoutput = json_decode($imagesoutput[0],true);
		//$org_reportsoutput = get_object_vars($org_reportsoutput);		
		//Loop Through each image
			for ($i=0;$i<count($org_imagesoutput['images']['image']);$i++) {
				$project_images[$i]['url'] = $org_imagesoutput['images']['image'][$i]['imagelink'][3]['url'];
				$project_images[$i]['title'] = $org_imagesoutput['images']['image'][$i]['title'];
			}
		//Merge images array
			if (count($project_images)<1 || count($report_images)<1) {
				if(count($project_images)<1 ) {
					$gg_imagesarray = $report_images;
				} elseif (count($report_images)<1 ) {
					$gg_imagesarray = $project_images;
				}
			} else {
			$gg_imagesarray = @array_merge($project_images,$report_images); //Commented in case no reports
			}
		//Pull in the required scripts and css
			
			$ggreturn = '<script src="' . plugins_url() . "/global-giving-display/imgslider".'/jquery.nivo.slider.pack.js" type="text/javascript"></script>' . $ggreturn;
			$ggreturn = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>' . $ggreturn;
			$ggreturn = '<link href="' . plugins_url() . "/global-giving-display/imgslider".'/dark.css" rel="stylesheet" type="text/css" />' . $ggreturn;	
			$ggreturn = '<link href="' . plugins_url() . "/global-giving-display/imgslider".'/nivo-slider.css" rel="stylesheet" type="text/css" />' . $ggreturn;	
			$ggreturn = $ggreturn . '<script type="text/javascript">
			$(window).load(function() {
				 $(\'#slider\').nivoSlider();
			});
			</script>';
		//Output Each Images
			$ggreturn = $ggreturn .  '<div id="ggshort-image-div">';
			$ggreturn = $ggreturn . '<div class="slider-wrapper theme-dark"><div id="slider" class="nivoSlider" style="max-height: 500px; max-width: 500px; margin: 0 auto;" >';
		//Images Loop
			for($y = 0; $y<count($gg_imagesarray); $y++) {
				$ggimaagelink = $gg_imagesarray[$y]['url'];
					if(ggendsWith($ggimaagelink, "png") || ggendsWith($ggimaagelink, "jpg") || ggendsWith($ggimaagelink, "jpeg")) {
						//Link is an Image
						//<img src="slide-3.jpg" /></a>
						//<div id="ggshort-report-image-link"></div>
						$ggreturn = $ggreturn .  '<a href="' . $reportlink . '">'. '<img src="' . $gg_imagesarray[$y]['url'] . '" />' . "</a>";
					}
					
				}
				$ggreturn = $ggreturn . '</div></div>';	 //End of Slider Div
		
		$ggreturn = $ggreturn .  '</div>'; //End of Images Div
	} //End of If Loop for images
  
	//Diagnostics
	/*
		echo "<hr>";
		echo "org_reportsoutput<pre>";
		print_r($org_reportsoutput);
		echo "</pre>";
		echo "<hr>";
	*/
	/*
	//$ggreturn = $ggreturn . "<p> Plugin Path: " . plugins_url();
 //Temp CSS
 	$ggreturn = '<link href="' . plugins_url() . "/global-giving-display".'/ggdefault.css" rel="stylesheet" type="text/css" />' . $ggreturn;	
 
return $ggreturn;		
}
add_shortcode( 'gg-project', 'gg_project' );
*/

