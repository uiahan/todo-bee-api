<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }} | Todo Bee</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            padding: 40px;
        }

        .invoice-box {
            max-width: 700px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .header p {
            margin: 0;
            font-size: 14px;
            color: #888;
        }

        .invoice-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 4px 0;
        }

        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <h1>Todo Bee</h1>
            <p>Invoice Pembayaran</p>
        </div>

        <hr>

        <div class="invoice-title">
            Invoice #{{ $invoice->invoice_number }}
        </div>

        <div class="info">
            <p><strong>Nama:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Tanggal:</strong> {{ $invoice->created_at->format('d-m-Y') }}</p>
        </div>

        <hr>

        <p class="amount"><strong>Jumlah Pembayaran:</strong> Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>

        <div class="footer">
            <p>Terima kasih telah menggunakan Todo Bee 💛</p>
        </div>
    </div>
</body>

</html>
