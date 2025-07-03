<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice #{{ $invoice->invoice_number }}</title>
  <style>
    body { font-family: sans-serif; font-size: 14px; }
    .header { margin-bottom: 30px; }
  </style>
</head>
<body>
  <h2>Invoice #{{ $invoice->invoice_number }}</h2>
  <p>Nama: {{ $user->name }}</p>
  <p>Email: {{ $user->email }}</p>
  <p>Jumlah: Rp {{ number_format($invoice->amount) }}</p>
  <p>Tanggal: {{ $invoice->created_at->format('d-m-Y') }}</p>
</body>
</html>
