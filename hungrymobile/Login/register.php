<?php 

	//If a post request comes to this script 
	if($_SERVER['REQUEST_METHOD']=='POST'){	
		//getting username password and phone number 
		$name = $_POST['name'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$phone = $_POST['phone'];
		$dob = date('d-m-Y',strtotime($_POST['dob']));
		
		//Generating a 6 Digits OTP or verification code 
		$otp = rand(100000, 999999);
		
		//Importing the db connection script 
		require_once('../dbConnect.php');
		
		//Creating an SQL Query 
		$sql = "INSERT INTO user (name, password, email, dob, mobile) values ('$name','$password','$email','$dob','$phone')";
		
		//If the query executed on the db successfully 
		if(mysqli_query($con,$sql)){
			//printing the response given by sendOtp function by passing the otp and phone number 
           echo 'success';
		}else{
			//printing the failure message in json 
			echo 'failure';
		}
		
		//Closing the database connection 
		mysqli_close($con);
	}
	?>