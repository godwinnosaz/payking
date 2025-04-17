
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to PayKing</title>
</head>
<body  style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <div  style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd; border-radius: 5px; overflow: hidden;">

               <div style="text-align: center; padding: 20px;">
            <img src={IMGSRC} alt="PayKing Logo" style="max-width: 300px;">
        </div>

  <div style="padding: 20px 30px; font-size: 14px; line-height: 1.6; color: #555555;">
        <p> Hello <span>@{USERNAME}</span></p>
        <p>Your password reset OTP  is</p>


        <div style="display: flex; justify-content: center; align-items: center;">
            <div style="width: 90%; height: 43px; background-color: #ff8500; text-align: center; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; align-items: center; padding-top: 20px; margin-bottom: 20px; font-size: 25px; color:#fff" class="signin">
                {CODE}
            </div>
        </div>

        <div class="msg">
            Use the code to reset your password in PayKing. <br>
            PayKing will never ask you to share this code with anyone. <br>
            Visit our site for more info <span style="color: #ff8500;"><a href="https://www.paykingweb.com" style="color: #ff8500;">www.paykingweb.com</a></span>
            <br>
            <br>
            <div class="refrain">
                Don't recognize this activity? <br>
                Do not share the code with anyone
            </div>
        </div>
        <br>
            <br>

       <div style="text-align: center; padding: 10px; background-color: #ff8500; color: #ffffff; font-size: 12px;">
            <p style="margin: 0;">Welcome to a Smarter Way to Pay Bills,</p>
            <p style="margin: 0; font-weight: bold;">The Payking Team</p>
        </div>
    </div>
    </div>
</body>

</html>