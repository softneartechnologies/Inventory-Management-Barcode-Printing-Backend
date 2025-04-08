<!DOCTYPE html>
<html>
<head>
    <title>Product Codes</title>
    <style>
        body { font-family: sans-serif; }
        .code-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .code-box {
            text-align: center;
            width: 200px;
        }
        .code-box img {
            max-width: 100%;
        }
    </style>
</head>
<body>
<?php 
use Milon\Barcode\Facades\DNS1D;
use Milon\Barcode\Facades\DNS2D;


?>
    <h2>Generated Codes</h2>
    <div class="code-container">
        @for($i = 0; $i < $count; $i++)
            <div class="code-box">
                @if($type === 'barcode')
                    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1D::getBarcodePNG($data, 'C128') }}" alt="Barcode">
                @elseif($type === 'qrcode')
                    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS2D::getBarcodePNG($data, 'QRCODE') }}" alt="QR Code">
                @endif
                @if($format === 'with_details')
                    <p>{{ $data }}</p>
                @endif
            </div>
        @endfor
    </div>

</body>
</html>
