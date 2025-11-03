<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reminder Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }

        .status {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px;
            color: #888888;
        }

        h2 {
            color: #333333;
        }

        p {
            line-height: 1.6;
            color: #555555;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            margin-top: 20px;
            background-color: #1d72b8;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #155d8b;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #aaaaaa;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status">
            {{ $status }} - {{ $datetime }}
        </div>

        <h2>Halo {{ $nama }},</h2>
        <p>{{ $ceks }}</p>

        <a href="{{ $url }}" class="btn">Klik di sini untuk melihat</a>

        <div class="footer">
            Terima kasih,<br>
            Tim {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
