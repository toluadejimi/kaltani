<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <title>Trash Bash Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <style>
        body {
            margin:0;
            padding:0;
            background:#f4f7fb;
            font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
            -webkit-text-size-adjust:100%;
            color:#1f2d3a;
        }
        a {
            color:#1a73e8;
            text-decoration:none;
        }
        p { margin:0; padding:0; }
        .wrapper {
            width:100%;
            table-layout:fixed;
            background-color:#f4f7fb;
            padding-bottom:30px;
        }
        .main {
            background:#ffffff;
            margin:0 auto;
            width:100%;
            max-width:600px;
            border-radius:6px;
            overflow:hidden;
            box-shadow:0 12px 32px -4px rgba(0,0,0,0.08);
        }
        .header {
            padding:18px;
            text-align:center;
            background:#ffffff;
        }
        .logo {
            max-width:160px;
            width:100%;
            height:auto;
        }
        .content {
            padding:20px 24px;
        }
        .greeting {
            font-size:16px;
            margin-bottom:8px;
        }
        .intro {
            font-size:14px;
            line-height:1.4;
            margin-bottom:16px;
        }
        .invoice-box {
            background:#f9f9fa;
            padding:14px;
            border-radius:6px;
            margin-bottom:20px;
            font-size:14px;
            line-height:1.3;
        }
        .invoice-row {
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            margin-bottom:6px;
        }
        .label {
            flex:1 1 140px;
            font-weight:600;
            min-width:120px;
        }
        .value {
            flex:2 1 200px;
            word-break:break-word;
        }
        .amount {
            font-size:17px;
            font-weight:700;
            margin-top:4px;
        }
        .buttons {
            text-align:center;
            margin:24px 0 10px;
        }
        .btn {
            display:inline-block;
            padding:12px 24px;
            border-radius:6px;
            font-weight:600;
            font-size:14px;
            text-decoration:none;
            margin:6px 4px;
        }
        .btn-primary {
            background:#1a73e8;
            color:#fff;
        }
        .btn-secondary {
            background:#020a16;
            color:#fff;
        }
        .small {
            font-size:12px;
            color:#6b7a93;
            margin-top:16px;
            text-align:center;
        }
        .divider {
            height:1px;
            background:#e2e8f0;
            margin:20px 0;
            border:none;
        }
        .footer {
            padding:14px 24px;
            font-size:11px;
            color:#6b7a93;
            text-align:center;
        }
        .social-icons {
            margin:10px 0 4px;
            text-align:center;
        }
        .social-icons img {
            width:26px;
            height:26px;
            margin:0 5px;
            vertical-align:middle;
        }

        @media screen and (max-width:640px){
            .content {
                padding:14px 16px !important;
            }
            .invoice-box {
                padding:10px !important;
                margin-bottom:14px !important;
            }
            .invoice-row {
                flex-direction:column;
                gap:4px !important;
                margin-bottom:4px !important;
            }
            .label, .value {
                flex:1 1 100% !important;
                font-size:13px;
            }
            .amount {
                font-size:16px;
            }
            .btn {
                width:100% !important;
                padding:10px 16px !important;
                margin:4px 0 !important;
                font-size:13px;
            }
            .greeting {
                margin-bottom:4px;
                font-size:15px;
            }
            .intro {
                font-size:13px;
                margin-bottom:10px;
            }
            .small {
                font-size:11px;
            }
            .header {
                padding:12px;
            }
            .footer {
                padding:10px 16px;
            }
        }
    </style>
</head>

<body>
<table class="wrapper" cellpadding="0" cellspacing="0" role="presentation" width="100%">
    <tr>
        <td align="center">
            <table class="main" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                <!-- Header -->
                <tr>
                    <td class="header">
                        <img src="https://kaltanimis.com/public/upload/5.png" alt="Trash Bash" class="logo" />
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td class="content">
                        <div class="greeting">
                            Dear {{ $data1['first_name'] ?? 'Customer' }},
                        </div>
                        <div class="intro">
                            We hope this message finds you well. Below is your monthly waste collection invoice from <strong>Trash Bash</strong>, powered by <strong>Kaltani</strong>.
                        </div>

                        <div class="invoice-box">
                            <div style="margin-bottom:6px; font-weight:700; font-size:14px;">Invoice Details</div>

                            <div class="invoice-row">
                                <div class="label">Customer Name:</div>
                                <div class="value">{{ $data1['name'] }}</div>
                            </div>

                            <div class="invoice-row">
                                <div class="label">Account Number:</div>
                                <div class="value">{{ $data1['customer_id'] }}</div>
                            </div>

                            <div class="invoice-row">
                                <div class="label">Bill Number:</div>
                                <div class="value">{{ $data1['bill_no'] }}</div>
                            </div>

                            <div class="invoice-row">
                                <div class="label">Billing Period:</div>
                                <div class="value">{{ $data1['period'] }}</div>
                            </div>

                            <div class="invoice-row">
                                <div class="label">Total Due:</div>
                                <div class="value amount">₦{{ number_format($data1['amount'], 2) }}</div>
                            </div>
                        </div>

                        <div class="buttons">
                            <!--[if mso]>
                  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $data1['pay_url'] }}" style="height:42px;v-text-anchor:middle;width:160px;" arcsize="10%" stroke="f" fillcolor="#1a73e8">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:13px;font-weight:600;">Pay Bill</center>
                  </v:roundrect>
                  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $data1['url'] }}" style="height:42px;v-text-anchor:middle;width:160px;margin-left:6px;" arcsize="10%" stroke="f" fillcolor="#020a16">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:13px;font-weight:600;">Download Bill</center>
                  </v:roundrect>
                <![endif]-->
                            <!--[if !mso]><!-- -->
                            <a href="{{ $data1['pay_url'] }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer" aria-label="Pay Bill">Pay Bill</a>
                            <a href="{{ $data1['url'] }}" class="btn btn-secondary" target="_blank" rel="noopener noreferrer" aria-label="Download Bill">Download Bill</a>
                            <!--<![endif]-->
                        </div>

                        <div class="small">
                            If the buttons above don’t work, copy and paste these links into your browser:<br>
                            Pay: <a href="{{ $data1['pay_url'] }}">{{ $data1['pay_url'] }}</a><br>
                            Download: <a href="{{ $data1['url'] }}">{{ $data1['url'] }}</a>
                        </div>

                        <hr class="divider">

                        <div style="text-align:center; font-size:14px; line-height:1.4;">
                            Questions? Email us at <a href="mailto:support@trashbash.com">support@trashbash.com</a> or call +234 704 109 3833.
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer">
                        <div class="social-icons">
                            <a href="https://facebook.com/" target="_blank" aria-label="Facebook"><img src="https://kaltanimis.com/public/upload/image-1.png" alt="Facebook"></a>
                            <a href="https://twitter.com/" target="_blank" aria-label="Twitter"><img src="https://kaltanimis.com/public/upload/image-2.png" alt="Twitter"></a>
                            <a href="https://skype.com/" target="_blank" aria-label="Skype"><img src="https://kaltanimis.com/public/upload/image-6.png" alt="Skype"></a>
                            <a href="https://whatsapp.com/" target="_blank" aria-label="WhatsApp"><img src="https://kaltanimis.com/public/upload/image-4.png" alt="WhatsApp"></a>
                        </div>
                        <div>
                            Trash Bash © All Rights Reserved<br>
                            14 Aliu Animashaun Ave, Lekki Phase 1 106104, Lagos
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
