<?php
include '../configdb.php';

if(isset($_POST['btnLogin'])){
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$sqlStatement = "SELECT * FROM pengguna WHERE username='$username'";
	$query = mysqli_query($conn, $sqlStatement);
	$dataUser = mysqli_fetch_assoc($query);
	
	Svar_dump($dataUser);
	
	if(isset($dataUser)){ //username ditemukan 
		if(md5($password) === $dataUser['password']){
			//echo "Username dan Password ditemukan";
			
			session_start();
			$_SESSION['username'] = $dataUser['username'];
			$_SESSION['role'] = $dataUser['role'];
			
			header("location:index1.php");
		} else { //username salah 
			echo "Password salah";
		}
	} else { //username tidak ditemukan
		echo "Username tidak terdaftar";
	}
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login and Signup Form</title>
  <link rel="stylesheet" href="../Admin-HTML/css/regis.css">
</head>

<body>
  <div class="form-container">
    <div class="forms-wrapper">

      <!-- Login Form -->
      <div class="form-box" id="login-form">
        <h2>Login Form</h2>
        <div class="toggle-buttons">
          <button class="active" onclick="showLogin()">Login</button>
          <button onclick="showSignup()">Signup</button>
        </div>
        <form>
          <input type="email" placeholder="Email Address" required>

          <div class="password-wrapper">
            <input type="password" id="login-password" placeholder="Password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('login-password', this)">👁️</button>
          </div>

          <a href="#" class="forgot-password">Forgot password?</a>
          <button type="submit" class="submit-btn">Login</button>
          <p class="signup-link">Not a member? <a href="#" onclick="showSignup()">Signup now</a></p>
        </form>
      </div>

      <!-- Signup Form -->
      <div class="form-box hidden" id="signup-form">
        <h2>Signup Form</h2>
        <div class="toggle-buttons">
          <button onclick="showLogin()">Login</button>
          <button class="active" onclick="showSignup()">Signup</button>
        </div>
        <form>
          <input type="email" placeholder="Email Address" required>

          <div class="password-wrapper">
            <input type="password" id="signup-password" placeholder="Password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('signup-password', this)">👁️</button>
          </div>

          <div class="password-wrapper">
            <input type="password" id="signup-confirm-password" placeholder="Confirm Password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('signup-confirm-password', this)">👁️</button>
          </div>

          <button type="submit" class="submit-btn">Signup</button>
        </form>
      </div>

    </div>
  </div>

  <script src="../Admin-HTML/js/regis.js"></script>
</body>
</html>
