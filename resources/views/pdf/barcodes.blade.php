<!DOCTYPE html>
<html>
<head>
    <title>Barcode Labels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background: #fff;
        }

        @page {
            size: {{ $orientation === 'horizontal' ? 'A4 landscape' : 'A4 portrait' }};
            margin: 15mm;
        }

        .sheet {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 15px;
        }

        .label-box {
            border: 1px dashed #aaa;
            padding: 10px;
            /* width: {{ $orientation === 'horizontal' ? '30%' : '45%' }}; */
            /* width: {{ $orientation === 'horizontal' ? '98%' : '85%' }};
            width: {{ $orientation === 'vertical' ? '98%' : '85%' }}; */
            width: '98%' : '85%';
            /* width: {{ $orientation === 'vertical' ? '98%' : '85%' }}; */
            box-sizing: border-box;
            page-break-inside: avoid;
            text-align: center;
            background: #fff;
        }

        .label-box h4 {
            /* margin: 0 0 8px; */
            font-size: 12px;
        }

        .barcode-image {
            margin: 5px 5px;
        }

        .small {
            width: 70%;
            height: 40px;
        }
        .medium {
            width: 80%;
            height: 45px;
        }
        .large {
            width: 95%;
            height: 50px;
        }

        .qr-small {
            width: 60px;
            height: 60px;
        }
        .qr-medium {
            width: 90px;
            height: 90px;
        }
        .qr-large {
            width: 120px;
            height: 120px;
        }

        .product-info {
            font-size: 11px;
            text-align: left;
            margin-top: 5px;
        }

        .product-info p {
            margin: 2px 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .header img {
            height: 30px;
        }

        .print-time {
            font-size: 10px;
            color: #555;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

@php
    $printDate = now()->format('l, F d Y h:i A');
@endphp

<div class="sheet">
    @for($i = 0; $i < $count; $i++)
        <div class="label-box">
            {{-- Header with logo and print time --}}
            <div class="header">
                 @if($type === 'barcode')
                <div class="print-time">Barcode No: {{ $i+1 }}</div>
                 @elseif($type === 'qrcode')
                 <div class="print-time">QR No: {{ $i+1 }}</div>
                 @endif
            </div>
            <div class="title">{{ $sku }}</div>
            <!--<div class="subtitle">Hayathnagar, Hyd-70, 8247524795</div>-->
            <div class="print-time">In Time: {{ $printDate }}</div>
            {{-- Print product data --}}
            @if($format === 'with_details')
                @php $dataArray = json_decode($data, true); @endphp
                @if(is_array($dataArray))
                    <div class="product-info">
                        @foreach($dataArray as $key => $value)
                            @if(!is_null($value))
                                <p>
                                    <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}
                                </p>
                            @endif
                        @endforeach
                    </div>
                @endif
            @else
                <!--<h4>SKU: {{ $sku }}</h4>-->
            @endif

            {{-- Render barcode or QR --}}
            <div class="barcode-image">
                @if($type === 'barcode')
                    @php
                        $barcodeBase64 = Milon\Barcode\Facades\DNS1D::getBarcodePNG($sku, 'C128');
                    @endphp
                    <img src="data:image/png;base64,{{ $barcodeBase64 }}" class="{{ $size }}" alt="Barcode">
                @elseif($type === 'qrcode')
                    @php
                        $qrBase64 = Milon\Barcode\Facades\DNS2D::getBarcodePNG($sku, 'QRCODE');
                        $qrClass = 'qr-' . $size;
                    @endphp
                    <br>
                    
                        <h4>SKU: {{ $sku }}</h4>
                    
                    <img src="data:image/png;base64,{{ $qrBase64 }}" class="{{ $qrClass }}" alt="QR Code">
                @endif
            </div>
        </div>
    @endfor
</div>

</body>
</html>
