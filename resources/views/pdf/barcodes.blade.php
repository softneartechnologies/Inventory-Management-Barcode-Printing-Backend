<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Label Print Styles</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #fff;
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
}

/* Page setup for thermal printing */
@page {
    /* For thermal printer - use exact label dimensions */
    size: 4in 6in; /* Adjust based on your label roll size */
    margin: 0;
    padding: 0;
}

/* Alternative page sizes for different scenarios */
@page thermal-single {
    size: 4in 2in; /* Single label */
    margin: 2mm;
}

@page thermal-roll {
    size: 4in 6in; /* Multiple labels on roll */
    margin: 2mm;
}

/* For regular A4 printing (backup/preview) */
@page a4-print {
    size: {{ $orientation === 'horizontal' ? 'A4 landscape' : 'A4 portrait' }};
    margin: 15mm;
}

/* Sheet container - modified for thermal printing */
.sheet {
    display: flex;
    flex-direction: column; /* Stack labels vertically for thermal roll */
    justify-content: flex-start;
    gap: 5mm; /* Reduced gap for thermal printing */
    padding: 0;
    width: 100%;
}

/* For A4 preview mode */
.sheet.a4-preview {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 15px;
    padding: 10px;
}

/* Label box - optimized for thermal printing */
.label-box {
    border: 1px solid #000; /* Solid border for better thermal printing */
    padding: 2mm; /* Reduced padding for thermal */
    width: 98mm; /* Fixed width for 4" thermal printer (4" = ~101.6mm) */
    max-width: 98mm;
    min-height: 40mm; /* Minimum height for readability */
    box-sizing: border-box;
    page-break-inside: avoid;
    page-break-after: always; /* Force new label for each item */
    text-align: center;
    background: #fff;
    margin: 0 auto;
    
    /* Thermal printer optimizations */
    color: #000 !important;
    -webkit-font-smoothing: none;
    font-smoothing: none;
}

/* A4 preview mode styling */
.label-box.a4-mode {
    width: {{ $orientation === 'horizontal' ? '30%' : '45%' }};
    page-break-after: auto;
    border: 1px dashed #aaa;
    padding: 10px;
}

/* Header styles */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3mm;
    font-size: 8pt; /* Smaller for thermal printing */
}

.header img {
    height: 15px; /* Reduced for thermal */
    max-width: 20mm;
}

.print-time {
    font-size: 6pt; /* Very small for thermal */
    color: #000; /* Black for thermal printing */
}

.title {
    font-size: 10pt; /* Reduced for thermal */
    font-weight: bold;
    margin-bottom: 1mm;
}

.subtitle {
    font-size: 8pt; /* Reduced for thermal */
    color: #000; /* Black for thermal printing */
    margin-bottom: 2mm;
}

/* Label heading */
.label-box h4 {
    margin: 0 0 3mm;
    font-size: 9pt; /* Adjusted for thermal */
    font-weight: bold;
    color: #000;
}

/* Barcode image container */
.barcode-image {
    margin: 2mm auto;
    display: block;
    text-align: center;
}

/* Barcode sizes - optimized for thermal printing */
.small {
    width: 35mm; /* Reduced for thermal */
    height: 15mm;
    max-width: 90%; /* Ensure it fits in label */
}

.medium {
    width: 70mm; /* Adjusted for 4" thermal printer */
    height: 20mm;
    max-width: 90%;
}

.large {
    width: 80mm; /* Maximum for 4" printer */
    height: 25mm;
    max-width: 90%;
}

/* QR Code sizes - optimized for thermal */
.qr-small {
    width: 15mm;
    height: 15mm;
}

.qr-medium {
    width: 20mm;
    height: 20mm;
}

.qr-large {
    width: 25mm;
    height: 25mm;
}

/* Product information */
.product-info {
    font-size: 7pt; /* Smaller for thermal */
    text-align: left;
    margin-top: 2mm;
    line-height: 1.2;
    color: #000;
}

.product-info p {
    margin: 1mm 0;
    font-size: 7pt;
}

/* Thermal printer specific media query */
@media print {
    body {
        margin: 0;
        padding: 0;
    }
    
    .sheet {
        gap: 2mm;
    }
    
    .label-box {
        border: 1px solid #000;
        margin-bottom: 3mm;
    }
    
    /* Hide elements not needed for thermal printing */
    .no-print {
        display: none !important;
    }
}

/* Print control classes */
.thermal-mode .label-box {
    width: 98mm;
    page-break-after: always;
}

.a4-mode .label-box {
    width: {{ $orientation === 'horizontal' ? '30%' : '45%' }};
    page-break-after: auto;
}

/* Utility classes for different thermal label sizes */
.thermal-2x1 { /* 2" x 1" labels */
    width: 48mm;
    height: 23mm;
}

.thermal-4x2 { /* 4" x 2" labels */
    width: 98mm;
    height: 48mm;
}

.thermal-4x3 { /* 4" x 3" labels */
    width: 98mm;
    height: 73mm;
}

.thermal-4x6 { /* 4" x 6" labels */
    width: 98mm;
    height: 148mm;
}

/* Text sizing for different label sizes */
.thermal-2x1 .title { font-size: 8pt; }
.thermal-2x1 .product-info { font-size: 6pt; }
.thermal-2x1 .small { width: 30mm; height: 10mm; }

.thermal-4x2 .title { font-size: 10pt; }
.thermal-4x2 .product-info { font-size: 7pt; }

.thermal-4x3 .title { font-size: 12pt; }
.thermal-4x3 .product-info { font-size: 8pt; }

.thermal-4x6 .title { font-size: 14pt; }
.thermal-4x6 .product-info { font-size: 9pt; }


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

