<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Agreement Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.details th {
            text-align: left;
            padding: 5px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            width: 30%;
        }
        table.details td {
            padding: 5px;
            border: 1px solid #ddd;
            width: 70%;
        }
        table.schedules {
            margin-top: 20px;
            font-size: 11px;
        }
        table.schedules th {
            text-align: left;
            padding: 5px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        table.schedules td {
            padding: 5px;
            border: 1px solid #ddd;
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>Agreement Details</h1>
    </div>

    <div class="section">
        <div class="section-title">Investor Information</div>
        <table class="details">
            <tr>
                <th>Investor Name</th>
                <td>{{ $investorNames }}</td>
            </tr>
            <tr>
                <th>Investor ID</th>
                <td>{{ $investorId }}</td>
            </tr>
            <tr>
                <th>Investor Email</th>
                <td>{{ $investorEmail }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Asset Information</div>
        <table class="details">
            <tr>
                <th>Asset Name</th>
                <td>{{ $assetName }}</td>
            </tr>
            <tr>
                <th>Asset Type</th>
                <td>{{ $assetType }}</td>
            </tr>
            <tr>
                <th>Size (m²)</th>
                <td>{{ $area }}</td>
            </tr>
            <tr>
                <th>Unit Number</th>
                <td>{{ $flatNumber }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Financial Information</div>
        <table class="details">
            <tr>
                <th>Sqm Price</th>
                <td>{{ number_format($price, 2) }}$</td>
            </tr>
            <tr>
                <th>Total Price</th>
                <td>{{ number_format($totalPrice, 2) }}$</td>
            </tr>
            <tr>
                <th>Installment Period</th>
                <td>{{ $period }} Month(s)</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Payment Schedule & Payment History</div>
        <div style="width: 100%;">
            <div style="width: 48%; float: left; margin-right: 2%;">
                <h4>Payment Schedule</h4>
                <table class="schedules">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $index => $payment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $payment['payment_date'] }}</td>
                                <td>{{ number_format($payment['status'] ? $payment['amount'] : $payment['left_amount'], 2) }}$</td>
                                <td>{{ $payment['status'] ? 'Paid' : 'Unpaid' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No payment schedule available</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total Remaining</th>
                            <th colspan="2">
                                @php
                                    $totalRemaining = 0;
                                    foreach($payments as $payment) {
                                        if(!$payment['status']) {
                                            $totalRemaining += $payment['left_amount'];
                                        }
                                    }
                                    echo number_format($totalRemaining, 2) . '$';
                                @endphp
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="width: 48%; float: left;">
                <h4>Payment History</h4>
                <table class="schedules">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentsHistories as $index => $history)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $history['date'] }}</td>
                                <td>{{ number_format($history['amount'], 2) }}$</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No payment history available</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total Paid</th>
                            <th>
                                @php
                                    $totalPaid = 0;
                                    foreach($paymentsHistories as $history) {
                                        $totalPaid += $history['amount'];
                                    }
                                    echo number_format($totalPaid, 2) . '$';
                                @endphp
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

</body>
</html>
