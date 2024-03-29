<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>

    <style>
        body {
            background: #f5f5f5;
        }
        .container {
            margin: auto;
            width: 50%;
            border: 1px solid #000000;
            padding: 51px;
            background:  #ffffff;
            border-radius: 10px;
            text-align: center;
        }

        .button-send-mail {        
            margin-top: 10px;
            padding: 20px 25px 20px 25px;
            background: #CC85B3;
            color: #ffffff;
            border-radius: 7px;
            border: none;
            font-weight: 900;
            font-size: 15px;
        }

        .wrap-text {
            word-wrap: break-word;
        }

        .cursor {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Hi {!! $name !!}</p>
        <p>Kami menerima permohonan atur ulang kata sandi akun Anda. Untuk menyelesaikan proses pergantian kata sandi, mohon menggunakan tombol dibwah ini</p>
        <a class="cursor" href="{{ $url.'/'.$token }}">
            <button class="button-send-mail">Reset Password</button>
        </a>
        <p>atau dapat copy link dibawah ini :</p>
        <div class="wrap-text">{{ $url.'/'.$token }}</div>
    </div>
</body>
</html>