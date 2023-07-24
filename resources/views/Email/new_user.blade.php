<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Webociti</title>
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
            margin-bottom: 5%;
        }
        .order-link {
            color: #007bff;
            text-decoration: none;
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
                <div class="custom-col">
                    <p class="text-content">
                        Dear {{ $user->clinic->name }},
                        <br><br>
                        We hope this email finds you in good health and high spirits. On behalf of the entire Webociti team, we extend a warm welcome to you!
                        <br><br>
                        Your journey with us starts today, and we are excited to have you on board. 
                        <br><br>
                        To get started, we have set up your clinic profile account. Below are your login credentials:
                        <br>
                        <span style="margin-left:40px; font-weight: 800;">Username/Profile ID:</span> {{ $user->email }}<br>
                        <span style="margin-left:40px; font-weight: 800;">Temporary Password:</span> {{ $password }}
                        <br><br>
                        Please follow these simple steps to access your account securely:<br>
                        <span style="margin-left:40px; display:inline-block;">1. Go to our secure login portal: <a href="{{ env('FRONTEND_URL').'login' }}">{{ env('FRONTEND_URL').'login' }}</a></span><br>
                        <span style="margin-left:40px; display:inline-block;">2. Enter your username and temporary password.</span><br>
                        <span style="margin-left:40px; display:inline-block;">3. You will be prompted to our dashboard. We recommend you to reset your password for a secure login in future.</span><br>
                        <br><br>
                        Thank you,
                        <br>
                        Team Webociti.
                    </p>
                </div>
                <span style="color: #ccc">2023©</span> Webociti
            </div>
        </div>
    </div>
</body>
</html>

