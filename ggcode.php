<?php

if(!function_exists(money_format)){
	function money_format($format, $number) { 
    $regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'. 
              '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/'; 
    if (setlocale(LC_MONETARY, 0) == 'C') { 
        setlocale(LC_MONETARY, ''); 
    } 
    $locale = localeconv(); 
    preg_match_all($regex, $format, $matches, PREG_SET_ORDER); 
    foreach ($matches as $fmatch) { 
        $value = floatval($number); 
        $flags = array( 
            'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? 
                           $match[1] : ' ', 
            'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0, 
            'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? 
                           $match[0] : '+', 
            'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0, 
            'isleft'    => preg_match('/\-/', $fmatch[1]) > 0 
        ); 
        $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0; 
        $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0; 
        $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits']; 
        $conversion = $fmatch[5]; 

        $positive = true; 
        if ($value < 0) { 
            $positive = false; 
            $value  *= -1; 
        } 
        $letter = $positive ? 'p' : 'n'; 

        $prefix = $suffix = $cprefix = $csuffix = $signal = ''; 

        $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign']; 
        switch (true) { 
            case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+': 
                $prefix = $signal; 
                break; 
            case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+': 
                $suffix = $signal; 
                break; 
            case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+': 
                $cprefix = $signal; 
                break; 
            case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+': 
                $csuffix = $signal; 
                break; 
            case $flags['usesignal'] == '(': 
            case $locale["{$letter}_sign_posn"] == 0: 
                $prefix = '('; 
                $suffix = ')'; 
                break; 
        } 
        if (!$flags['nosimbol']) { 
            $currency = $cprefix . 
                        ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) . 
                        $csuffix; 
        } else { 
            $currency = ''; 
        } 
        $space  = $locale["{$letter}_sep_by_space"] ? ' ' : ''; 

        $value = number_format($value, $right, $locale['mon_decimal_point'], 
                 $flags['nogroup'] ? '' : $locale['mon_thousands_sep']); 
        $value = @explode($locale['mon_decimal_point'], $value); 

        $n = strlen($prefix) + strlen($currency) + strlen($value[0]); 
        if ($left > 0 && $left > $n) { 
            $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0]; 
        } 
        $value = implode($locale['mon_decimal_point'], $value); 
        if ($locale["{$letter}_cs_precedes"]) { 
            $value = $prefix . $currency . $space . $value . $suffix; 
        } else { 
            $value = $prefix . $value . $space . $currency . $suffix; 
        } 
        if ($width > 0) { 
            $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ? 
                     STR_PAD_RIGHT : STR_PAD_LEFT); 
        } 

        $format = str_replace($fmatch[0], $value, $format); 
    } 
    return $format; 
	}
}

function gg_info( $atts, $content = null ){
	
	//Get passed data
		extract( shortcode_atts( array(
			'projectid' => '0',
			'giveforyouth' => false, //Is it a giveforyouth project
			'reports' => true, //Valid Values -> true (yes display reports), false (don't display reports), number (display only that number of reports)
			'projectdata' => true, //hide project header info to display only reports
			'images' => true,
			'bpgg' => true,
			), $atts ) );
	//Get API Key
		$apikeynum = get_option('ggdisplay_apikey');
			//No API Key Set
			if ($apikeynum == false) {
			exit("The API has not been set - please contact the system administrator");
			}
	//Projects for Loop
	$projectsarray = explode(",",$projectid);
	for($projectnum=0;$projectnum<count($projectsarray);$projectnum++) {
		//Set URL for Project
			$project_url = "https://api.globalgiving.org/api/public/projectservice/projects/collection/ids?api_key=" . get_option('ggdisplay_apikey') . "&projectIds=" . $projectsarray[$projectnum];
			$reports_url = "https://api.globalgiving.org/api/public/projectservice/projects/" . $projectsarray[$projectnum] . "/reports?api_key=" . get_option('ggdisplay_apikey');
			$images_url = "https://api.globalgiving.org/api/public/projectservice/projects/" . $projectsarray[$projectnum] . "/imagegallery?api_key=" . get_option('ggdisplay_apikey');
		//Set OPTS and Context
			$opts = array('http'=>array(
				'method'=>"GET",
				'header'=>"Accept: application/json\r\n", ) );
			$context = stream_context_create($opts);
		//Run Rest Queries
			$project_output = gg_file_get_contents($project_url, false, $context,get_the_ID(),$projectsarray[$projectnum],"project");
			$reports_output = gg_file_get_contents($reports_url, false, $context,get_the_ID(),$projectsarray[$projectnum],"report");
			$images_output = gg_file_get_contents($images_url, false, $context,get_the_ID(),$projectsarray[$projectnum],"image");
		//Remap
			$gg_outputarr[$projectnum]['project'] = $project_output['projects']['project'];
			//Reports Loop
			for($reportnum=0;$reportnum<count($reports_output['entries']);$reportnum++) {
				$gg_outputarr[$projectnum]['reports'][$reportnum] = $reports_output['entries'][$reportnum];
			}
			//Images Loop
			for($imagenum=0;$imagenum<count($images_output['images']['image']);$imagenum++) {
				$gg_outputarr[$projectnum]['images'][$imagenum] = $images_output['images']['image'][$imagenum];
			}			
	}
	//Project Link
		if ($giveforyouth == true) {
				$projectlink = str_replace("http://www.globalgiving.org","http://www.giveforyouth.org",$gg_outputarr[0]['project']['projectLink']);
				$projectlink = str_replace("https://www.globalgiving.org","https://www.giveforyouth.org",$projectlink);	
			} else {
				$projectlink = $gg_outputarr[0]['project']['projectLink'];
			}
	//Percentage Funded
		$percentagefunded = ($gg_outputarr[0]['project']['funding']/$gg_outputarr[0]['project']['goal'])*100;
	//Initialize return sting
		$ggreturn = '<div id="ggshort-div">';
	//Output Project 1 only
		if($projectdata == true) {
			//Top Level
				$ggreturn = $ggreturn .  '<div id="ggshort-project-title">' . $gg_outputarr[0]['project']['title'] . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-link"><a href="'. $projectlink . '">'. $projectlink . "</a></div>";
			//General Info
				$ggreturn = $ggreturn .  '<div id="ggshort-projectinfo-div">';
				$ggreturn = $ggreturn .  '<div id="ggshort-project-summary-title">'. 'Summary' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-summary">'. $gg_outputarr[0]['project']['summary'] . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-need-title">'. 'What is the issue, problem, or challenge?' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-need">'. $gg_outputarr[0]['project']['need'] . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-activity-title">'. 'How will this project solve this problem?' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-activity">'. $gg_outputarr[0]['project']['activities'] . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-longtermimpact-title">'. 'Potential Long Term Impact' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-longtermimpact">'. $gg_outputarr[0]['project']['longTermImpact'] . "</div>";
				$ggreturn = $ggreturn .  '</div>'; //End of Project Report Info Section
			//Funding Info
				$ggreturn = $ggreturn .  '<div id="ggshort-project-fundingdiv">';
				$ggreturn = $ggreturn .  '<div id="ggshort-project-funding-title">'. 'Funding Recieved: ' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-funding">'. money_format('%(#10n', $gg_outputarr[0]['project']['funding']) . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-remaining-title">'. 'Funds Remaining: ' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-remaining">'. money_format('%(#10n', $gg_outputarr[0]['project']['remaining']) . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-goal-title">'. 'Funding Goal: ' . "</div>";
				$ggreturn = $ggreturn .  '<div id="ggshort-project-goal">'. money_format('%(#10n', $gg_outputarr[0]['project']['goal']) . "</div>";
				$ggreturn = $ggreturn .  "<div style='width:100px; background-color:white; height:30px; border:1px solid #000;'>
    			<div style='width:".$percentagefunded."px; background-color:#00ff00; height:30px;'></div></div>";
				$ggreturn = $ggreturn .  "</div>"; //End Funding Div Section
			//Close Project Info
				$ggreturn = $ggreturn .  "</div>"; //End ggshort-projectinfo-div
				
		}
	//Output Custom Widget		
		if($content !== null) { //Only output if there is content
			$ggreturn = $ggreturn . '<div id="ggshort-customwidget">';
			$ggreturn = $ggreturn . $content;
			$ggreturn = $ggreturn .  "</div>";
		}	
	//Optional Output: Powered by Global Giving
		if($bpgg == true) {
			$ggreturn = $ggreturn . '<div id="ggshort-widget">';
			$ggreturn = $ggreturn . '<div id="ggshort-widget-title">' . $gg_outputarr[0]['project']['title'] . "</div>";
			$ggreturn = $ggreturn . '<div id="ggshort-widget-image">' . '<a href="'. $projectlink . '">' . '<img src="' . $gg_outputarr[0]['project']['imageLink'] . '">' . "</a></div>";
			$ggreturn = $ggreturn . '<div id="ggshort-widget-givenow">' . '<a href="'. $projectlink . '">' . '<img src="https://www.globalgiving.org/img/buttons/give_now.gif">' . "</a></div>";
			$ggreturn = $ggreturn . '<div id="ggshort-widget-poweredby">' . '<a href="'. "http://www.globalgiving.org/" . '">' . '<img src="https://www.globalgiving.org/img/logos/powered_by_globalgiving.jpg">' . "</a></div>";
			$ggreturn = $ggreturn .  "</div>";
		}	
	$ggreturn = $ggreturn . '<div class="clear"></div>'; //Clear Area	
	//Output Reports
		if($reports == true) {
			$ggreturn = $ggreturn .  '<div id="ggshort-reportblock">';
			for($projectnum=0;$projectnum<count($gg_outputarr);$projectnum++) {
				for($reportnum=0;$reportnum<count($gg_outputarr[$projectnum]['reports']);$reportnum++) {
					$ggreturn = $ggreturn .  '<div id="ggshort-report-div">';
						$ggreturn = $ggreturn .  '<div id="ggshort-report-title">' . $gg_outputarr[$projectnum]['reports'][$reportnum]['title'] . "</div>";
						$ggreturn = $ggreturn .  '<div id="ggshort-report-author-group">';
							$ggreturn = $ggreturn .  '<div id="ggshort-report-author-title">'. 'Author(s): ' . "</div>";
							for ($k=0;$k<count($gg_outputarr[$projectnum]['reports'][$reportnum]['authors']);$k++) {
								$ggreturn = $ggreturn .  '<div id="ggshort-report-author">'. $gg_outputarr[$projectnum]['reports'][$reportnum]['authors'][$k]['name'] . "</div>";
							}
						$ggreturn = $ggreturn .  '</div>'; //End Author Group
						$ggreturn = $ggreturn .  '<div id="ggshort-report-pubdate-title">'. 'Published: ' . "</div>";
						$ggdate = explode("T",$gg_outputarr[$projectnum]['reports'][$reportnum]['published']);
						$ggreturn = $ggreturn .  '<div id="ggshort-report-pubdate">'. $ggdate[0] . "</div>";
						$ggreturn = $ggreturn .  '<div id="ggshort-report-content">'. $gg_outputarr[$projectnum]['reports'][$reportnum]['content'] . "</div>";
						$ggreturn = $ggreturn .  '<div id="ggshort-report-seemore-title">'. 'See More: ' . "</div>";
						
						if ($giveforyouth == true) {
	 						$reportlink = str_replace("http://www.globalgiving.org","http://www.giveforyouth.org",$gg_outputarr[$projectnum]['reports'][$reportnum]['id']);
	 						$reportlink = str_replace("https://www.globalgiving.org","https://www.giveforyouth.org",$reportlink);	
	 					} else {
				 			$reportlink = $gg_outputarr[$projectnum]['reports'][$reportnum]['id'];
	 					}
						
						$ggreturn = $ggreturn .  '<div id="ggshort-report-seemore"><a href="'. $reportlink . '">'. $reportlink . "</a></div>";
						$ggreturn = $ggreturn . '<div class="clear"></div>'; //Clear Area
					//$ggreturn = $ggreturn .  '</div>'; //End Report Div
				}
			}
			$ggreturn = $ggreturn .  '</div>'; //End Report Div
		}
	$ggreturn = $ggreturn . '<div class="clear"></div>'; //Clear Area	
	//Output Images
		if ($images == true) {  //If images are off don't display (on by default)
			//Load Required Scripts
				$ggreturn = '<script src="' . plugins_url() . "/global-giving-display/imgslider".'/jquery.nivo.slider.pack.js" type="text/javascript"></script>' . $ggreturn;
				$ggreturn = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>' . $ggreturn;
				$ggreturn = '<link href="' . plugins_url() . "/global-giving-display/imgslider".'/dark.css" rel="stylesheet" type="text/css" />' . $ggreturn;	
				$ggreturn = '<link href="' . plugins_url() . "/global-giving-display/imgslider".'/nivo-slider.css" rel="stylesheet" type="text/css" />' . $ggreturn;	
				$ggreturn = $ggreturn . '<script type="text/javascript">
				$(window).load(function() {
					 $(\'#slider\').nivoSlider();
				});
				</script>';
			//Output Images
					$ggreturn = $ggreturn .  '<div id="ggshort-image-div">';
						$ggreturn = $ggreturn . '<div class="slider-wrapper theme-dark"><div id="slider" class="nivoSlider" style="max-height: 500px; max-width: 500px; margin: 0 auto;" >';
							//Cover Images
							$ggreturn = $ggreturn .  '<a href="' . $projectlink . '">'. '<img src="' . $gg_outputarr[0]['project']['imageLink'] . '" />' . "</a>";
							//Image Gallery
							for($projectnum=0;$projectnum<count($gg_outputarr);$projectnum++) {
								for($imagesnum=0;$imagesnum<count($gg_outputarr[$projectnum]['images']);$imagesnum++) {
									if(ggendsWith($gg_outputarr[$projectnum]['images'][$imagesnum]['imagelink'][3]['url'], "png") || ggendsWith($gg_outputarr[$projectnum]['images'][$imagesnum]['imagelink'][3]['url'], "jpg") || ggendsWith($gg_outputarr[$projectnum]['images'][$imagesnum]['imagelink'][3]['url'], "jpeg")) {
										$ggreturn = $ggreturn .  '<a href="' . $projectlink . '">'. '<img src="' . $gg_outputarr[$projectnum]['images'][$imagesnum]['imagelink'][3]['url'] . '" />' . "</a>";
									}
								}
							}
							//Report Images
							for($projectnum=0;$projectnum<count($gg_outputarr);$projectnum++) {
								for($reportnum=0;$reportnum<count($gg_outputarr[$projectnum]['reports']);$reportnum++) {
									for($reportimg=0;$reportimg<count($gg_outputarr[$projectnum]['reports'][$reportnum]['links']);$reportimg++) {
										$ggreturn = $ggreturn .  '<a href="' . $projectlink . '">'. '<img src="' . $gg_outputarr[$projectnum]['reports'][$reportnum]['links'][$reportimg]['href'] . '" />' . "</a>";
									}
								}
							}
						$ggreturn = $ggreturn . '</div></div>';	 //End of Slider Div
					$ggreturn = $ggreturn .  '</div>'; //End of Images Div
		}
	
	//End return string
			$ggreturn = $ggreturn . '</div>';  //End Shortcode Div
			$ggreturn = $ggreturn . '<div class="clear"></div>'; //Clear Area
	//Diagnostics
	
		//echo "gg_outputarr<hr><pre>";
		//print_r($gg_outputarr);
		//echo "</pre><hr>";
	
	//Return
		//Add default
		$ggreturn = '<link href="' . plugins_url() . "/global-giving-display".'/ggdefault.css" rel="stylesheet" type="text/css" />' . $ggreturn;
		//Return
		return $ggreturn;
}
add_shortcode( 'gg-info', 'gg_info' );