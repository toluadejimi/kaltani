<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trash Bash Invoice</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .invoice-container {
            max-width: 700px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            background-color: #1f1f52;
            color: white;
            padding: 8px 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 20px;
            border-radius: 5px;
        }

        .company-info {
            margin-top: 5px;
            font-size: 14px;
        }

        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 16px;
            border-bottom: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #000;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .totals {
            text-align: right;
        }

        .total-row {
            background-color: #1f1f52;
            color: white;
            font-weight: bold;
        }

        .status-unpaid {
            color: #038221;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 13px;
            color: #444;
        }

        .qr-code {
            position: absolute;
            bottom: 20px;
            right: 40px;
        }

    </style>
</head>
<body>

<div class="invoice-container">
    <div class="header">
        <div class="logo">TRASH BASH</div>
        <div class="company-info">
            Trash Bash Waste Management Services<br>
            Lekki, Lagos.<br> +234-800-TRASHBASH
        </div>
    </div>

    <div class="section-title">Customer Information</div>
    <p><strong>Customer ID:</strong> {{$invoice['customer_id']}}<br>
        <strong>Name:</strong> {{$invoice['name']}}<br>
        <strong>Phone Number:</strong> {{$invoice['phone']}}</p>

    <div class="section-title">Billing Details</div>
    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th>Unit Price (&#8358;)</th>
            <th>Total (&#8358;)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Monthly Waste Collection</td>
            <td>{{number_format($invoice['total'])}}</td>
            <td>{{number_format($invoice['total'])}}</td>
        </tr>
        <tr>
            <td colspan="2" class="totals">Subtotal</td>
            <td>&#8358;{{number_format($invoice['total'])}}</td>
        </tr>
        <tr class="total-row">
            <td colspan="2" class="totals">Total Amount Paid</td>
            <td>&#8358;{{number_format($invoice['total'])}}</td>
        </tr>
        </tbody>
    </table>


    <table style="width: 100%; margin-top: 20px; border-collapse: collapse; border: none;">
        <tr>
            <td style="vertical-align: top; border: none;">
                <p>
                    <strong>Status:</strong> <span class="status-unpaid">PAID</span><br>
                    <strong>Next Due Date:</strong> {{$invoice['due_date']}}<br>
                    <strong>Payment Method:</strong> Bank Transfer
                </p>
            </td>
            <td style="text-align: right; vertical-align: top; width: 100px; border: none;">
                <img src="data:image/png;base64,{{$invoice['qr_code']}}" width="90" alt="QR Code">
            </td>
        </tr>
    </table>



    <div class="footer">
        Thank you for partnering with Trash Bash to keep Abia State clean!
    </div>
</div>

</body>
</html>
