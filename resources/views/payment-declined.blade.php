<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment Declined</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body {
            margin:0;
            font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
            background: #f4f7fb;
            color: #1f2d3a;
            display:flex;
            align-items:center;
            justify-content:center;
            min-height:100vh;
            padding:16px;
        }
        .card {
            background:#ffffff;
            max-width:500px;
            width:100%;
            border-radius:12px;
            box-shadow:0 16px 40px -10px rgba(0,0,0,0.08);
            padding:32px;
            text-align:center;
        }
        .icon {
            font-size:64px;
            margin-bottom:8px;
            display:inline-block;
            background: #ef4444;
            color:#fff;
            width:96px;
            height:96px;
            line-height:96px;
            border-radius:50%;
        }
        h1 {
            margin:16px 0 4px;
            font-size:24px;
        }
        p {
            margin:8px 0 20px;
            font-size:15px;
            line-height:1.5;
        }
        .btn {
            display:inline-block;
            padding:14px 28px;
            border-radius:6px;
            font-weight:600;
            text-decoration:none;
            cursor:pointer;
            margin:6px;
            font-size:15px;
        }
        .retry {
            background:#405cf5;
            color:#fff;
        }
        .help {
            background:#f1f4f8;
            color:#1f2d3a;
            border:1px solid #d8e2f1;
        }
        .small {
            font-size:12px;
            color:#6b7a93;
            margin-top:16px;
        }
    </style>
</head>
<body>
<div class="card" aria-label="Payment declined">
    <div class="icon" aria-hidden="true">✕</div>
    <h1>Payment Declined</h1>
    <p>We're sorry, your payment could not be processed, Please try again or </p>

    <a href="mailto:support@trashbash.com" class="btn help">Contact Support</a>

    <div class="small">
        Common causes: insufficient funds, incorrect card details, or network issues. If the problem persists, reply to this message or call +234 704 109 3833.
    </div>
</div>
</body>
</html>
