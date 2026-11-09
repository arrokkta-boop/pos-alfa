<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $sale->invoice_number }} - Toko Retail ALFA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }
        .receipt {
            width: 80mm;
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            color: #000;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-family: sans-serif;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back {
            background: #475569;
            margin-left: 8px;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .receipt {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div>
        <div class="receipt">
            <div class="center">
                <div class="bold" style="font-size: 14px;">TOKO RETAIL ALFA</div>
                <div>Jl. Jendral Sudirman No. 88, Jakarta</div>
                <div>Telp: (021) 555-8899</div>
            </div>

            <div class="divider"></div>

            <div class="row">
                <span>No. Struk</span>
                <span class="bold">{{ $sale->invoice_number }}</span>
            </div>
            <div class="row">
                <span>Tanggal</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="row">
                <span>Kasir</span>
                <span>{{ $sale->user->name }}</span>
            </div>

            <div class="divider"></div>

            @foreach ($sale->saleDetails as $detail)
            <div class="item-row">
                <div class="bold">{{ $detail->product->product_name }}</div>
                <div class="row">
                    <span>{{ $detail->qty }} x Rp {{ number_format($detail->sell_price, 0, ',', '.') }}</span>
                    <span class="bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach

            <div class="divider"></div>

            <div class="row bold" style="font-size: 13px;">
                <span>TOTAL</span>
                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>TUNAI</span>
                <span>Rp {{ number_format($sale->pay_amount, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>KEMBALI</span>
                <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
            </div>

            <div class="divider"></div>

            <div class="center" style="font-size: 11px; margin-top: 10px;">
                <div class="bold">TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
                <div>Barang yang dibeli tidak dapat ditukar/dikembalikan</div>
                <div style="font-size: 9px; margin-top: 6px;">Layanan Pelanggan: 1500-888</div>
            </div>
        </div>

        <div class="actions">
            <button onclick="window.print()" class="btn">Cetak Struk</button>
            <a href="{{ route('kasir.pos') }}" class="btn btn-back">Kembali ke POS</a>
        </div>
    </div>
</body>
</html>
