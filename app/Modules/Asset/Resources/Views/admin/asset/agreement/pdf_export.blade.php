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
        table.details th, table.details td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        table.details th {
            background-color: #f5f5f5;
            width: 30%;
            text-align: left;
        }
        table.details td {
            width: 70%;
        }
        table.schedules th, table.schedules td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        table.schedules th {
            background-color: #f5f5f5;
            text-align: left;
            font-size: 11px;
        }
        table.schedules td {
            font-size: 11px;
        }

        /* outer wrapper for two‐column */
        table.outer {
            margin-top: 20px;
        }
        table.outer td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        table.outer td + td {
            padding-left: 4%;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Agreement Details</h1>
    <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
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

    <table class="outer">
        <caption class="section-title" style="text-align: left">Payment Schedule & Payment History</caption>
        <tr>
            <!-- left column -->
            <td style="width:48%;">
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
                    @forelse($payments as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $p['payment_date'] }}</td>
                            <td>
                                {{ number_format(
                                    $p['status']
                                      ? $p['amount']
                                      : $p['left_amount'],
                                    2
                                  ) }}$
                            </td>
                            <td>{{ $p['status'] ? 'Paid' : 'Unpaid' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No payment schedule available</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2">Total Remaining</th>
                        <th colspan="2">
                            @php
                                $totalRemaining = collect($payments)
                                    ->where('status', 0)
                                    ->sum('left_amount');
                            @endphp
                            {{ number_format($totalRemaining, 2) }}$
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </td>

            <!-- right column -->
            <td style="width:48%;">
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
                    @forelse($paymentsHistories as $i => $h)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $h['date'] }}</td>
                            <td>{{ number_format($h['amount'], 2) }}$</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No payment history available</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2">Total Paid</th>
                        <th>
                            @php
                                $totalPaid = collect($paymentsHistories)
                                    ->sum('amount');
                            @endphp
                            {{ number_format($totalPaid, 2) }}$
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
