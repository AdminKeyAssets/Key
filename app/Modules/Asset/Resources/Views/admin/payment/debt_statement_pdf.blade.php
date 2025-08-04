<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debt Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            text-decoration: underline;
        }
        .info-section {
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .info-line {
            margin-bottom: 8px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .payment-table th,
        .payment-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: center;
        }
        .payment-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .total-section {
            margin: 20px 0;
            font-weight: bold;
            font-size: 14px;
        }
        .closing-section {
            margin-top: 40px;
            line-height: 1.8;
        }
        .signature-section {
            margin-top: 60px;
            line-height: 1.8;
        }
        .dashed-line {
            border-top: 1px dashed #333;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Debt Statement</h1>
    </div>

    <div class="info-section">
        <div class="info-line"><strong>To:</strong> {{ $investorNames }}</div>
        <div class="info-line"><strong>Asset:</strong> {{ $assetName }}@if($flatNumber), Flat #{{ $flatNumber }}@endif</div>
        <div class="info-line"><strong>Developer:</strong> {{ $developerName }} - {{ $projectName }}</div>
        <div class="info-line"><strong>Date:</strong> {{ $currentDate }}</div>
    </div>

    <div class="dashed-line"></div>

    <p>According to the payment schedule for your property (Flat #{{ $flatNumber }}, {{ $projectName }}), the following payments are overdue:</p>

    <table class="payment-table">
        <thead>
            <tr>
                <th>Due Date</th>
                <th>Scheduled Amount</th>
                <th>Paid Amount</th>
                <th>Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overduePayments as $payment)
            <tr>
                <td>{{ $payment['payment_date'] }}</td>
                <td>${{ number_format($payment['amount'], 2) }}</td>
                <td>${{ number_format($payment['paid_amount'], 2) }}</td>
                <td>${{ number_format($payment['left_amount'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <div class="total-section">
        <strong>TOTAL OUTSTANDING BALANCE: ${{ number_format($totalOutstanding, 2) }}</strong>
    </div>

    <div class="closing-section">
        <p>We kindly request that you settle the outstanding amount at your earliest convenience.</p>
    </div>

    <div class="signature-section">
        <p><strong>Sincerely,</strong></p>
        <p><strong>{{ $developerName }}</strong></p>
        @if($developerContact)
        <p>{{ $developerContact }}</p>
        @endif
        @if($managerName || $managerContact)
        <p>(Asset Manager: {{ $managerName }}@if($managerContact) - {{ $managerContact }}@endif)</p>
        @endif
    </div>
</body>
</html>
