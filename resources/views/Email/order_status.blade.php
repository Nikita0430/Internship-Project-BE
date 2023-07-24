<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
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
                <h2 class="custom-heading">Order Update</h2>
                <div class="custom-col">
                    <p class="text-content">
                        Hello,
                        <br><br><br>
                        We wish to inform you that your Order <a href="{{ env('FRONTEND_URL').'order/view/'.$order->id }}" class="order-link">{{$order['order_no']}}</a> is {{$order['status']}} on {{ \Carbon\Carbon::today()->format('d F Y') }}.
                        <br><br>
                        Thankyou for being a part of Webociti.
                        <br><br><br>
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

