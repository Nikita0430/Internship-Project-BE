<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body {
            font-family: Roboto, "Helvetica Neue", sans-serif;
            background-color: #fff;
            max-height: 100vh;
        }
        .custom-header {
            max-height: 300px;
            background-color: #007bff;
            margin-bottom: 4%;
        }
        .custom-logo {
            max-height: 300px;
            height: match-parent; 
            object-fit: contain;
        }
        .custom-container {
            height: 100%;
        }
        .custom-row {
            height: 100%;
        }
        .custom-card {
            background-color: white;
            box-sizing: border-box;
            border-radius: 5px;
            margin: 10% 5% ;
            height: 80%;
            text-align: center;
            padding: 0;
        }
        .custom-col {
            margin: auto;
            margin-top: 0;
        }
        .custom-btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            margin-top: 3%;
            margin-bottom: 3%;
            padding: 10px 20px;
            text-decoration: none;
        }
        .text-content {
            margin: auto;
            padding: 5%;
            color: #000;
            text-align: start;
            max-width: 700px;
        }
        .footnote {
            margin-bottom: 5%;
        }

        .custom-heading {
            font-size: 24px;
            display: block;
            margin: auto;
            font-weight: 700;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="custom-container">
        <div class="custom-row">
            <div class="custom-card">
                <div class="custom-header">
                    <img src="{{ $message->embed(public_path('images/webociti_logo.png')) }}" alt="Logo" class="custom-logo">
                </div>
                <p class="custom-heading">Reset Password</p>
                <div class="custom-col">
                    <p class="text-content">
                        Hello,
                        <br><br>
                        We have received a request to reset the password for your account associated with Webociti.<br>
                        To proceed with the password reset, please click on the button below:
                    </p>
                    <a class="custom-btn" href="{{ env('FRONTEND_URL').'reset-password/'.$token }}">Reset Password</a>
                    <p class="text-content footnote">
                        Please note that the password reset link is valid for 24 hours, after which it will expire.<br><br>
                        Thanks,<br>
                        Team Webociti
                    </p>
                </div>
                <span style="color: #999">2023©</span> Webociti
            </div>
        </div>
    </div>
</body>
</html>
