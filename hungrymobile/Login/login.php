<?php 

	//If a post request comes to this script 
	if($_SERVER['REQUEST_METHOD']=='POST'){	
		//getting username password and phone number 
			require_once('../dbConnect.php');
			
		$user_name= mysqli_real_escape_string($con,$_POST['user_name']);
		$user_pass= mysqli_real_escape_string($con,$_POST['user_pass']);
		
		//Generating a 6 Digits OTP or verification code 
		
		//Importing the db connection script 
	
		
		//Creating an SQL Query 
		$encrypt=md5($user_pass);

$admin_query="select * from user where email='$user_name' AND password='$user_pass'";
$run = mysqli_query($con, $admin_query);
	if(mysqli_num_rows($run) > 0){

	 while($row = $run->fetch_assoc()) {
	echo $row["name"];
	 }
	}
	else{
	echo "failure";
	}


		
		//Closing the database connection 
		mysqli_close($con);
	}
	?>