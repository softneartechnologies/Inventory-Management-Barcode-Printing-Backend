<!DOCTYPE html>
<html>
<head>
    <title>Product Codes</title>
    <style>
        body { font-family: sans-serif; }
        .code-container {
            /*display: flex;*/
            /*flex-wrap: wrap;*/
            /*gap: 20px;*/
        }
        .code-box {
            text-align: center;
            width: 200px;
            display: flex;
        }
        .code-box img {
            /*max-width: 100%;*/
        }
    </style>
    
</head>
<body>
<?php 
use Milon\Barcode\Facades\DNS1D;
use Milon\Barcode\Facades\DNS2D;


?>
    <h2>Generated Codes</h2>
   
    
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            /*display: flex;*/
            flex-wrap: wrap;
            justify-content: {{ $orientation === 'horizontal' ? 'space-between' : 'flex-start' }};
        }
        .headerSubTitle {
          font-family: 'Equestria', 'Permanent Marker', cursive;
          text-align: center;
          font-size: 12pt;
        }

        .pdf-border {
            border: 1px solid #000;
            padding: 15px;
            margin: 10px;
        }
        .barcode-item {
            /* margin: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            display: inline-block;
            text-align: center;
            page-break-inside: avoid; */
            margin: 10px;
            padding: 10px;
            /* border: 1px solid #ddd; */
            display: inline-block;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            page-break-inside: avoid;
        }
        .barcode-item.vertical {
            width: 180px;
        }
        .barcode-item.horizontal {
            width: 550px;
        }
        .product-info {
            margin-top: 5px;
            font-size: {{ $orientation === 'horizontal' ? '14px' : '12px' }};
            text-align: left;
            padding: 5px;
        }
        .product-info p {
            margin: 3px 0;
            line-height: 1.3;
        }
        .product-info p strong {
            color: #333;
            font-weight: bold;
        }
        img {
            /*max-width: 100%;*/
            height: auto;
        }
        @page {
            margin: {{ $orientation === 'horizontal' ? '15px' : '10px' }};
            size: {{ $orientation === 'horizontal' ? 'A4 landscape' : 'A4 portrait' }};
        }
        
        .small { width: 105px; height: 40px; }
        .medium { width: 145px; height: 45px; }
        .large { width: 180px; height: 50px; }

        .qr-small { width: 60px; height: 60px; }
        .qr-medium { width: 90px; height: 90px; }
        .qr-large { width: 120px; height: 120px; }
        
    </style>
    

<!--<div class="pdf-wrapper">-->
<div class="pdf-border">
    <div class="container">
        <div class="row">
        @for($i = 0; $i < $count; $i++)
        <br>
         <br>
    
           <div class="under-line"></div>

                @if($format === 'with_details')
                  @php
                        
                        $dataArray = json_decode($data, true);
                        
                    @endphp
                
                @if(is_array($dataArray))
                    @foreach($dataArray as $key => $value)
                        @if(!is_null($value))
                        @if($key =='product_name')
                        <div class="headerSubTitle">
                            <p><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                            </div>
                        @else
                        
                           <p><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                           @endif
                        @endif
                    @endforeach
                @endif

                       <!--<p><strong>{{ ucfirst($data) }}:</strong></p>-->

                   @else
                   
                   <p><strong>SKU: {{ ucfirst($sku) }}</strong></p>
                @endif
                
                
                @if($type === 'barcode')
                
               
                    @if($size == 'small')
                   
                    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1D::getBarcodePNG($data, 'C128') }}" alt="Barcode" style="object-fit: contain;" class="small">
                   <br>

                    @elseif($size === 'medium')
                        
                        
                    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1D::getBarcodePNG($data, 'C128') }}" alt="Barcode" style="object-fit: contain;" class="medium">
                   <br>
                    
                    
                    @elseif($size === 'large')
                    
                   
                    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1D::getBarcodePNG($data, 'C128') }}" alt="Barcode" style="object-fit: contain;" class="large">
                   <br>
                   
                    @endif
                    
                    
                @elseif($type === 'qrcode')
                    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS2D::getBarcodePNG($data, 'QRCODE') }}" alt="QR Code" style="width: 100px; height: 100px; object-fit: contain;">
                     <br>
                @endif


                <!--@if($type === 'barcode')-->
                <!--    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1D::getBarcodePNG($data, 'C128') }}" alt="Barcode">-->
                <!--@elseif($type === 'qrcode')-->
                <!--    <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS2D::getBarcodePNG($data, 'QRCODE') }}" alt="QR Code">-->
                <!--@endif-->

                
           

        @endfor
        </div>
    </div>
</div>


</body>
</html>
