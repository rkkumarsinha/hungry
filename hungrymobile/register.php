<?php 
	//Constants for our API
	//this is applicable only when you are using Cheap SMS Bazaar
	define('SMSUSER','HGYDNY');
	define('PASSWORD','HGYDNY');
	define('SENDERID','HGYDNY');
	
	
	//This function will send the otp 
	function sendOtp($otp, $phone){
		//This is the sms text that will be sent via sms 
		//$sms_content = "Welcome to Hungry Dunia: Your verification code is $otp";
		$sms_content = "Welcome to Hungry Dunia !! ".$otp. " is your verification code. Please enter this OTP to verify your identity .";
		
		//Encoding the text in url format
		$sms_text = urlencode($sms_content);

		// This is the Actual API URL concatnated with required values 
		// $api_url = "http://64.31.1.242/api/smsapi.aspx?username=".SMSUSER."&password=".PASSWORD."&to=".$phone."&from=".SENDERID."&message=".$sms_text;
		$api_url="http://api.alerts.solutionsinfini.com/v3/?method=sms&api_key=A7918990678ab558bfc27189c8ddda4a2&to=".$phone."&sender=".SMSUSER."&message=".$sms_text."&format=json&custom=1";
		
		//Envoking the API url and getting the response 
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; da; rv:1.9.1) Gecko/20090624 Firefox/3.5');
		$data = curl_exec($ch);
		curl_close($ch);
		
		//Returning the response 
		return $data;
	}
	
	
	//If a post request comes to this script 
	if($_SERVER['REQUEST_METHOD']=='POST'){	
		//getting username password and phone number 
		$username = $_POST['username'];
		$password = $_POST['password'];
		$phone = $_POST['phone'];
		
		//Generating a 6 Digits OTP or verification code 
		$otp = rand(100000, 999999);
		
		//Importing the db connection script 
		require_once('dbConnect.php');
		
		//Creating an SQL Query 
		$sql = "INSERT INTO otp (username, password, phone, otp) values ('$username','$password','$phone','$otp')";
		
		//If the query executed on the db successfully 
		if(mysqli_query($con,$sql)){
			//printing the response given by sendOtp function by passing the otp and phone number 
            echo sendOtp($otp,$phone);
		}else{
			//printing the failure message in json 
			echo '{"ErrorMessage":"Failure"}';
		}
		
		//Closing the database connection 
		mysqli_close($con);
	}
	?>