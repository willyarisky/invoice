<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice['invoice_no'] ?? '' }}</title>
    <style>
        @page {
            margin: 20mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: #1c1917;
        }

        .page {
            margin: 0 auto;
            padding: 0px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            display: block;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 700;
            color: #1c1917;
            margin: 0;
        }

        .company-meta {
            text-align: right;
            font-size: 14px;
            color: #1c1917;
        }

        .company-name {
            font-weight: 600;
        }

        .company-meta p {
            margin: 0 0 8px;
        }

        .company-meta p:last-child {
            margin-bottom: 0;
        }

        .section {
            margin-top: 24px;
            border-top: 1px solid #e7e5e4;
            padding-top: 24px;
            display: flex;
            gap: 24px;
            justify-content: space-between;
        }

        .bill-to,
        .invoice-details {
            flex: 1;
        }

        .label {
            font-size: 12px;
            text-transform: uppercase;
            color: #a8a29e;
            margin: 0 0 8px;
        }

        .bill-name {
            font-size: 14px;
            font-weight: 600;
            color: #1c1917;
            margin: 4px 0;
        }

        .bill-line {
            font-size: 12px;
            color: #78716c;
            margin: 2px 0;
        }

        .invoice-details {
            max-width: 35%;
            margin-left: auto;
            font-size: 14px;
            color: #57534e;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .detail-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #a8a29e;
            padding-right: 20px;
        }

        .detail-value {
            font-weight: 600;
            color: #1c1917;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 14px;
            color: #57534e;
        }

        thead th {
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 12px;
            font-weight: 700;
            color: #1c1917;
            border-bottom: 2px solid #e7e5e4;
            padding: 12px 0;
        }

        tbody td {
            padding: 12px 0;
            border-bottom: 1px solid #e7e5e4;
        }

        tbody td:first-child {
            color: #1c1917;
            font-weight: 600;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .invoice-footer {
            margin-top: 24px;
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .invoice-footer.no-notes {
            justify-content: flex-end;
        }

        .notes {
            flex: 1;
            margin-top: 120px;
        }

        .notes-title {
            font-size: 14px;
            font-weight: 600;
            color: #1c1917;
            margin: 0;
        }

        .notes-body {
            margin-top: 8px;
            font-size: 14px;
            color: #57534e;
            line-height: 1.6;
        }

        .totals-box {
            width: 100%;
            max-width: 42%;
            margin-left: auto;
            font-size: 14px;
            color: #57534e;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .totals-row .value {
            font-weight: 600;
            color: #1c1917;
        }

        .totals-row.total {
            border-top: 1px solid #e7e5e4;
            padding-top: 8px;
            font-size: 16px;
            font-weight: 700;
            color: #1c1917;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="invoice-card">
            <div class="invoice-header">
                <div class="brand">
                    @if ($companyLogo !== '')
                        <img src="{{ $companyLogo }}" alt="{{ $brandName }} logo" class="brand-logo">
                    @else
                        <h1 class="brand-name">{{ $brandName }}</h1>
                    @endif
                </div>
                <div class="company-meta">
                    <p class="company-name">{{ $brandName }}</p>
                    @if (!empty($companyAddressHtml))
                        <p class="company-address">{!! $companyAddressHtml !!}</p>
                    @endif
                    @if ($companyEmail !== '')
                        <p class="company-email">{{ $companyEmail }}</p>
                    @endif
                </div>
            </div>

            <div class="section" style="margin-top: 32px; padding-top: 32px;">
                <div class="bill-to">
                    <p class="label">Bill To</p>
                    <p class="bill-name">{{ $invoice['customer_name'] ?? 'Customer' }}</p>
                    @if (!empty($customerAddressHtml))
                        <p class="bill-line">{!! $customerAddressHtml !!}</p>
                    @endif
                </div>
                <div class="invoice-details">
                    <div class="detail-row">
                        <span class="detail-label">Invoice Number</span>
                        <span class="detail-value">{{ $invoiceNo }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Invoice Date</span>
                        <span>{{ $issued }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Due Date</span>
                        <span>{{ $due }}</span>
                    </div>
                </div>
            </div>

            <table style="margin-top: 32px;">
                <thead>
                    <tr>
                        <th style="text-align:left;">Items</th>
                        <th class="text-center">Quantity</th>
                        <th style="text-align:left;">Price</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '' }}</td>
                            <td class="text-center">{{ $item['qty'] ?? 0 }}</td>
                            <td>{{ $item['unit_label'] ?? '' }}</td>
                            <td class="text-right">{{ $item['subtotal_label'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="invoice-footer {{ !empty($hasNotes) ? '' : 'no-notes' }}">
                @if (!empty($hasNotes))
                    <div class="notes">
                        <p class="notes-title">Notes</p>
                        <div class="notes-body">{!! $notesHtml ?? '' !!}</div>
                    </div>
                @endif
                <div class="totals-box">
                    <div class="totals-row" style="padding-bottom: 5px;">
                        <span>Subtotal</span>
                        <span class="value">{{ $subtotalLabel ?? '' }}</span>
                    </div>
                    @if (!empty($hasTax))
                        <div class="totals-row" style="padding-bottom: 5px;">
                            <span>{{ $taxLabel ?? 'Tax' }}</span>
                            <span class="value">{{ $taxAmountLabel ?? '' }}</span>
                        </div>
                    @endif
                    <div class="totals-row" style="padding-bottom: 8px;">
                        <span>Amount due</span>
                        <span class="value">{{ $amountDueLabel ?? '' }}</span>
                    </div>
                    <div class="totals-row total">
                        <span>Total</span>
                        <span class="value">{{ $totalLabel ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
