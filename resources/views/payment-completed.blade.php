<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment Successful</title>
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
            background: #22c55e;
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
        .primary {
            background:#1a73e8;
            color:#fff;
        }
        .secondary {
            background:#f1f4f8;
            color:#1f2d3a;
            border:1px solid #d8e2f1;
        }
        .small {
            font-size:12px;
            color:#6b7a93;
            margin-top:16px;
        }
        .receipt {
            background:#020a16;
            color:#fff;
        }
    </style>
</head>
<body>
<div class="card" aria-label="Payment successful">
    <div class="icon" aria-hidden="true">✓</div>
    <h1>Payment Completed</h1>
    <p>
        Thank you, <br>
        Your payment of <strong>₦{{ number_format($trx_ref->amount, 2) }}</strong>
        has been successfully received.
    </p>

    <a href="/download-pdf?ref={{ $trx_ref->trans_id }}" class="btn primary" target="_blank" rel="noopener">
        Download Receipt
    </a>

    <div class="small">
        If you have any questions, email us at
        <a href="mailto:support@trashbash.com">support@trashbash.com</a>
        or call +234 704 109 3833.
    </div>
</div>

<script>
    (function(){
        // Only run fetch if this was loaded via AJAX request context
        if (window.location.search.includes('ajax=1')) {
            fetch(window.location.pathname, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.html) {
                        document.body.innerHTML = data.html;
                    }
                })
                .catch(err => console.error('Error loading payment page:', err));
        }
    })();
</script>
</body>
</html>
