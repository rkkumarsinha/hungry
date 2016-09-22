<?php
class page_getdistance extends Page
{
    function init(){
        parent::init();
        
        if(!$currentlat = $_POST['currentlat']){
        	echo 0;
        	exit;
        }

        if(!$currentlan = $_POST['currentlan']){
        	echo 0;
        	exit;
        }

        if(!$restlat = $_POST['restlat']){
        	echo 0;
        	exit;
        }
        	
        if(!$restlan = $_POST['restlan']){
        	echo 0;
        	exit;
        }

        
    	$apiURL = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".$currentlat.",".$currentlan."&destinations=".$restlat.",".$restlan."&mode=driving&language=en-EN&sensor=false";
        $data = json_decode(file_get_contents($apiURL),true);
        echo "".$data['rows'][0]['elements'][0]['distance']['text'];
        exit;
    }
}

