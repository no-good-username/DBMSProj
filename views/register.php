<?php
session_start();
@include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require './phpMailer/src/Exception.php';
require './phpmailer/src/PHPMailer.php';
require './phpmailer/src/SMTP.php';

if (isset($_POST['submit'])) {

    $name = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = md5($_POST['password']);
    $cpass = md5($_POST['cpassword']);

    $select = " SELECT * FROM `register` WHERE user_email = '$email' && user_password = '$pass' ";

    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {

        $error[] = 'User already exists!';

    } else {
        if (($name == "") || ($email == "") || ($pass != $cpass)) {
            $error[] = 'Registration Failed. Please recheck your entered details.';
        } else {
            // OTP PART STARTS HERE
            $otp = mt_rand(100000, 999999); // 6-digit OTP
            date_default_timezone_set('Asia/Kolkata');
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'deeptejdhauskar2003@gmail.com'; // EMAIL 
            $mail->Password = 'wbxtnblepkwftmdm'; // APP PASSWORD
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('deeptejdhauskar2003@gmail.com', 'Astro'); // (EMAIL, NAME)
            $mail->addAddress($email, $name);
            $mail->Subject = 'Here\'s your OTP!';
            $mail->isHTML(true);
            $mail->Body = '
    <html>
    <head>
        <style>
            body {
                background-color: #f2f2f2;
            }
            .container {
                padding: 20px;
                background-color: #ffffff;
                border-radius: 5px;
                box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                background-image: url("https://images.pexels.com/photos/7130498/pexels-photo-7130498.jpeg?cs=srgb&dl=pexels-codioful-%28formerly-gradienta%29-7130498.jpg&fm=jpg");
                background-size: cover;
                background-position: center;
                color: white;
                padding: 10px;
                border-radius: 5px 5px 0 0;
                height: 55px;
            }
            .content {
                padding: 20px;
            }
            .footer {
                padding: 10px;
                text-align: center;
                color: #999999;
            }
            .otp{
                font-size: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>OTP Verification</h2>
            </div>
            <div class="content">
                <p>Hello ' . $name . ',</p>
                <p>Your OTP for verification is: <strong class="otp">' . $otp . '</strong></p>
                <p>Please use this OTP within the next 10 minutes to complete the verification process.</p>
            </div>
            <div class="footer">
                <p>If you did not request this OTP, please ignore this email.</p>
            </div>
        </div>
    </body>
    </html>
';

            if (!$mail->send()) {
                echo 'OTP could not be sent. Please try again later.';
            } else {
                echo 'OTP sent successfully! Please check your email.';
            }

            $insert = "INSERT INTO `register` (user_name, user_email, user_password) VALUES ('$name', '$email', '$pass')";
            mysqli_query($conn, $insert);
            $insertOtpQuery = "INSERT INTO `otp_data` (user_email, otp_code, otp_expiry) VALUES ('$email', '$otp', '$otp_expiry')";
            mysqli_query($conn, $insertOtpQuery);

            $_SESSION['email'] = $email; // SESSION EMAIL
            header('location:verify-otp.php');
        }
    }

}
?>

<!DOCTYPE html>

<html>
    <head>
        <?php include './partials/head-content.php'; ?>
        <link rel="stylesheet" href="../assets/css/styles.css">
        <!-- for the google OAuth -->
        <script src="https://apis.google.com/js/platform.js" async defer></script>
    </head>
    <body>
            <nav class="link">
                    <a href="login.php">Sign In</a>
            </nav>
            <video id="background-video" autoplay loop muted poster="https://assets.codepen.io/6093409/river.jpg">
                <source src="../assets/GradientBg.mp4" type="video/mp4">
                </video>

                

                <div class="form">
                    <h1 class="deez">Create an account</h1>
                    <form action="" method="POST">
                            <?php
                            if (isset($error)) {
                                foreach ($error as $error) {
                                    echo '<span color="white" class="error-msg">' . $error . '</span>';
                                }
                                ;
                            }
                            ;

                            ?>
                        <div class="nameinput">
                            Name
                            <br>
                            <input id="name" type="text" placeholder="Enter Your Name" name="username">
                        </div>
                        <div class="nameinput">
                            Email Address
                        <br>
                        <input id="email" type="email" placeholder="Enter You Email Id" name="email">
                        </div>
                        <div class="nameinput">
                            Password
                        <br>
                        <input id="pass" type="password" placeholder="Enter You Password" name="password">
                        </div>
                        <div class="nameinput">
                            Confirm Password
                        <br>
                        <input id="cnfrm-pass" type="password" placeholder="Enter Your Password" name="cpassword">
                        </div>
                        <input id="sbmitbtn" type="submit" value="Submit" name="submit">
                        <p style="text-align: center;">OR</p>
                        <div class="g-signin2" data-onsuccess="onGoogleSignIn" style="text-align: center;">Sign in with Google</div>
                    </form>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
            <script>
                    $("#sbmitbtn").on("click",function(){
        // var deez = $("#pass").value;
        var deez = document.getElementById("pass").value;
        var deeze = document.getElementById("email").value;
        var deezn = document.getElementById("name").value;
        var cnfpswd = document.getElementById("cnfrm-pass").value;
        

        if((deezn.length == 0)) {
            alert("Enter A Name");
            document.getElementById("pass").value = "";
            document.getElementById("cnfrm-pass").value = "";
        }
        if((deeze.length == 0)) {
            alert("Enter Valid Email Please");
            document.getElementById("pass").value = "";
            document.getElementById("cnfrm-pass").value = "";
        }
        if ((deez !== cnfpswd)) {
            alert("The Passwords entered differ!");
            document.getElementById("pass").value = "";
            document.getElementById("cnfrm-pass").value = "";
        }
        if((deez.length < 8)) {
            alert("Password must exceed 8 characters");
            document.getElementById("pass").value = "";
            document.getElementById("cnfrm-pass").value = "";
        }
    })
            </script>
    </body>
</html>