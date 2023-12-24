<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Email Template</title>

  <style>
    body {
      margin: 0;
      padding: 0;
    }

    /* Set a max-width for the container */
    .container {
      font-family: Helvetica, Arial, sans-serif;
      width: 70%;
      margin: 50px auto;
      padding: 20px 0;
      overflow: auto;
      line-height: 2;
    }

    /* Add styles for the header */
    .header {
      border-bottom: 1px solid #eee;
    }

    .header a {
      font-size: 1.4em;
      color: #57c7a0;
      text-decoration: none;
      font-weight: 600;
    }

    /* Adjust font size for smaller screens */
    @media only screen and (max-width: 600px) {
      .container {
        width: 90%;
      }
    }

    /* Add styles for the OTP box */
    .otp-box {
      background: #57c7a0;
      margin: 0 auto;
      width: max-content;
      padding: 0 10px;
      color: #fff;
      border-radius: 4px;
    }

    /* Add more responsive styles as needed */
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <a href="" style="font-size:1.4em;color: #57c7a0;text-decoration:none;font-weight:600">Bleepus</a>
    </div>
    <p style="font-size:1.1em">Hi, {{$name}}</p>
    <p>Thank you for choosing Bleepus. Use the following OTP to complete your Sign Up procedures. 
    
    OTP is valid for 30 minutes</p>
    <h2 class="otp-box">{{$otp}}</h2>
    <p style="font-size:0.9em;">Regards,<br />Bleepus</p>
    <hr style="border:none;border-top:1px solid #eee" />
    <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
      <p>Bleepus Technology Inc</p>
      <p>Cradley Heath,</p>
      <p>England,</p>
      <p>United Kingdom.</p>
      <a href="https://bleepus.com/">https://bleepus.com/</a>
    </div>
  </div>
</body>
</html>
