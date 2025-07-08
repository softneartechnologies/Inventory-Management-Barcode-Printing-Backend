<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Label Print Styles</title>
   


 <style>
    html, body {
      margin: 1;
      /* padding: 1; */
      width: 52mm;
      height: 30mm;
      font-family: Arial, sans-serif;
      background: #fff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      box-sizing: border-box;
    }

    @page {
      size: 52mm 35.8mm;
      margin: 1;
      /* margin-bottom: 2mm; */
    }

    @media print {
      body {
        /* margin: 0.5;
        padding: 0.5; */
        
      }

      .label-box {
        page-break-after: always;
      }

      .no-print {
        display: none !important;
      }
    }

    .sheet {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 52mm;
      height: 40mm;
      /* padding-bottom: 1px; */
      margin: auto;
      box-sizing: border-box;

      /* margin-right: 1px; */
    }

    .label-box {
      /* width: 52mm; */
      /* height: 45mm; */
      /* width: 100%; */
      /* height: 50%; */
      display: flex;
      justify-content: center;
      align-items: center;
      box-sizing: border-box;
    }

    .barcode-image {
      width: 96.5%;
      /* height: 100%; */
      text-align: center;
      /* margin-right: 0.3px;
      margin-left: 0.3px; */
      /* margin-top: -5px; */
      /* padding-top: -2px; */
    }


    .barcode-image img.medium {
      /* width: 100%; */
      /* width: 50mm; */
      /* height: 30mm; */
      /* height: 100%; */
      /* height: 30mm; */
      object-fit: contain;
      justify-content: center;
      /* margin-right: 1px;
      margin-left: 1px; */
    }

    .barcode-image img.small {
      width: 95%;
      height: 100%;
      object-fit: contain;
      justify-content: center;
    }

    .medium {
      width: 50mm;
      height: 30mm;
      border: 2px solid;
       text-align: center;
      margin-right: -2px;
      margin-left: -1px;
      margin-top: -1.5px;
    }

    .small {
      width: 50mm;
      height: 25mm;
      border: 2px solid;
    }

    .qr-small { width: 15mm; height: 15mm; }
    .qr-medium { width: 20mm; height: 20mm; }
    .qr-large { width: 25mm; height: 25mm; }

    .title {
      font-size: 8pt;
      font-weight: bold;
      /* margin-bottom: 1mm; */
    }

    .product-info {
      font-size: 7pt;
      text-align: center;
      color: #000;
    }

    .product-info p {
      /* margin: 1mm 0; */
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
                 <!-- @if($type === 'barcode')
                <div class="print-time">Barcode No: {{ $i+1 }}</div>
                 @elseif($type === 'qrcode')
                 <div class="print-time">QR No: {{ $i+1 }}</div>
                 @endif -->
                 <!-- <br> -->
            </div>
            <!-- <div class="title">{{ $sku }}</div> -->
            <!--<div class="subtitle">Hayathnagar, Hyd-70, 8247524795</div>-->
            <!-- <div class="print-time">In Time: {{ $printDate }}</div> -->
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
                <div class="title">{{ $sku }}</div>
            </div>
        </div>
    @endfor
</div>

    <script>
        // Set current date
        document.getElementById('current-date').textContent = new Date().toLocaleDateString();
        
        function testPrint() {
            // Create a test page with printer settings info
            const testWindow = window.open('', '_blank');
            testWindow.document.write(`
                <html>
                <head><title>Printer Test</title></head>
                <body style="font-family: Arial; padding: 20px;">
                    <h2>Deli DL-740C Printer Settings</h2>
                    <p><strong>Recommended Settings:</strong></p>
                    <ul>
                        <li>Paper Size: 4" x 2" (101.6mm x 50.8mm)</li>
                        <li>Print Quality: 203 DPI</li>
                        <li>Print Speed: Medium (adjust based on quality needs)</li>
                        <li>Darkness: 15-20 (adjust for your labels)</li>
                        <li>Media Type: Direct Thermal Labels</li>
                    </ul>
                    <p><strong>Driver Settings:</strong></p>
                    <ul>
                        <li>Set custom paper size in printer properties</li>
                        <li>Margins: 0mm all sides</li>
                        <li>Scale: 100%</li>
                        <li>Print in grayscale/black & white</li>
                    </ul>
                    <button onclick="window.print()" style="margin-top: 20px; padding: 10px;">Print This Test Page</button>
                </body>
                </html>
            `);
        }
    </script>
    <script>
        class ThermalPrintHelper {
    constructor() {
        this.currentMode = 'thermal'; // 'thermal' or 'a4'
        this.labelSize = '4x2'; // Default label size
    }

    // Switch between thermal and A4 preview modes
    setMode(mode) {
        const sheet = document.querySelector('.sheet');
        const labelBoxes = document.querySelectorAll('.label-box');
        
        if (mode === 'thermal') {
            this.currentMode = 'thermal';
            sheet.classList.remove('a4-preview');
            labelBoxes.forEach(box => {
                box.classList.remove('a4-mode');
                box.classList.add('thermal-mode');
            });
        } else if (mode === 'a4') {
            this.currentMode = 'a4';
            sheet.classList.add('a4-preview');
            labelBoxes.forEach(box => {
                box.classList.add('a4-mode');
                box.classList.remove('thermal-mode');
            });
        }
    }

    // Set thermal label size
    setLabelSize(size) {
        const labelBoxes = document.querySelectorAll('.label-box');
        
        // Remove existing thermal size classes
        labelBoxes.forEach(box => {
            box.classList.remove('thermal-2x1', 'thermal-4x2', 'thermal-4x3', 'thermal-4x6');
            box.classList.add(`thermal-${size}`);
        });
        
        this.labelSize = size;
    }

    // Print function optimized for thermal printer
    print() {
        // Set thermal mode before printing
        this.setMode('thermal');
        
        // Add thermal-specific styles
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                @page {
                    size: ${this.getPageSize()};
                    margin: 2mm;
                }
                body { margin: 0; padding: 0; }
                .label-box { 
                    page-break-after: always;
                    margin-bottom: 0;
                }
                .label-box:last-child {
                    page-break-after: auto;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Print
        window.print();
        
        // Remove temporary style
        document.head.removeChild(style);
    }

    // Get page size based on label size
    getPageSize() {
        const sizes = {
            '2x1': '2in 1in',
            '4x2': '4in 2in',
            '4x3': '4in 3in',
            '4x6': '4in 6in'
        };
        return sizes[this.labelSize] || '4in 2in';
    }

    // Configure printer settings (display instructions)
    showPrinterSettings() {
        const settings = {
            'Deli DL-740C': {
                paperSize: 'Custom 4" x 2" (101.6mm x 50.8mm)',
                printQuality: '203 DPI',
                printSpeed: 'Medium',
                darkness: '15-20',
                mediaType: 'Direct Thermal Labels',
                margins: '0mm all sides'
            }
        };
        
        console.log('Recommended Printer Settings:', settings['Deli DL-740C']);
        return settings['Deli DL-740C'];
    }

    // Test print function
    testPrint() {
        const testLabel = `
            <div class="label-box thermal-4x2">
                <div class="header">
                    <span class="title">TEST LABEL</span>
                    <span class="print-time">${new Date().toLocaleString()}</span>
                </div>
                <div class="barcode-image">
                    <img src="data:image/svg+xml;base64,${this.generateTestBarcode()}" class="medium" alt="Test Barcode">
                </div>
                <div class="product-info">
                    <p><strong>Test Product</strong></p>
                    <p>SKU: TEST123</p>
                    <p>Price: $0.00</p>
                </div>
            </div>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Test Print</title>
                    <style>${this.getThermalCSS()}</style>
                </head>
                <body>
                    <div class="sheet">
                        ${testLabel}
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    // Generate a simple test barcode (placeholder)
    generateTestBarcode() {
        // Simple SVG barcode for testing
        const svg = `
            <svg width="200" height="50" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="50" fill="white"/>
                <g fill="black">
                    <rect x="10" y="5" width="2" height="35"/>
                    <rect x="15" y="5" width="1" height="35"/>
                    <rect x="20" y="5" width="3" height="35"/>
                    <rect x="25" y="5" width="1" height="35"/>
                    <rect x="30" y="5" width="2" height="35"/>
                    <rect x="35" y="5" width="1" height="35"/>
                    <rect x="40" y="5" width="2" height="35"/>
                </g>
                <text x="100" y="48" text-anchor="middle" font-family="Arial" font-size="8">TEST123</text>
            </svg>
        `;
        return btoa(svg);
    }

    // Get thermal CSS
    getThermalCSS() {
        return `
            /* Your thermal CSS from above goes here */
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
            @page { size: 4in 2in; margin: 2mm; }
            .label-box { width: 98mm; height: 48mm; border: 1px solid #000; padding: 2mm; }
            .title { font-size: 10pt; font-weight: bold; }
            .product-info { font-size: 7pt; }
            .barcode-image img { max-width: 90%; }
        `;
    }
}

// Usage example:
const thermalPrinter = new ThermalPrintHelper();

// Function to add to your existing code
function initializeThermalPrinting() {
    // Add print mode toggle buttons
    const controlsHTML = `
        <div class="print-controls" style="margin: 10px; text-align: center;">
            <button onclick="thermalPrinter.setMode('thermal')" class="btn btn-primary">Thermal Mode</button>
            <button onclick="thermalPrinter.setMode('a4')" class="btn btn-secondary">A4 Preview</button>
            <select onchange="thermalPrinter.setLabelSize(this.value)" style="margin: 0 10px;">
                <option value="2x1">2" x 1" Labels</option>
                <option value="4x2" selected>4" x 2" Labels</option>
                <option value="4x3">4" x 3" Labels</option>
                <option value="4x6">4" x 6" Labels</option>
            </select>
            <button onclick="thermalPrinter.print()" class="btn btn-success">Print</button>
            <button onclick="thermalPrinter.testPrint()" class="btn btn-info">Test Print</button>
        </div>
    `;
    
    // Insert controls before the sheet
    const sheet = document.querySelector('.sheet');
    if (sheet) {
        sheet.insertAdjacentHTML('beforebegin', controlsHTML);
    }
    
    // Set initial thermal mode
    thermalPrinter.setMode('thermal');
    thermalPrinter.setLabelSize('4x2');
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeThermalPrinting);



    </script>
</body>
</html>

