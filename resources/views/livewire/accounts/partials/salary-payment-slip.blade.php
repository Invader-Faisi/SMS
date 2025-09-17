<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Salary Slip</title>
    <style>
        /* Global */
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 24px;
            font-size: 14px;
            color: #111;
        }

        /* Outer container has single outer border */
        .salary-container {
            border: 1px solid #000;
            padding: 18px;
            width: 100%;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 { margin: 0 0 6px 0; font-size: 18px; }
        .header h2 { margin: 0; font-size: 16px; font-weight: normal; }

        /* Employee info table (no internal borders) */
        .info-table {
            width: 100%;
            border-collapse: separate; /* ensures only outer border if set */
            margin: 12px 0 18px 0;
        }
        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .info-table .label { width: 18%; font-weight: 600; }
        .info-table .value { width: 32%; }

        /* Two columns layout (tables) */
        .columns {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Each column is a table with outer border only */
        .break-table {
            width: 100%;
            max-width: 49%;
            border: 1px solid #000;      /* outer border */
            border-collapse: separate;   /* prevents inner borders */
            border-spacing: 0;
            box-sizing: border-box;
        }
        /* Make it full width on small screens */
        @media (max-width: 700px) {
            .break-table { max-width: 100%; }
        }

        .break-table thead td {
            padding: 10px 12px;
            font-weight: 700;
            background: #f6f6f6;
        }
        .break-table tbody td {
            padding: 8px 12px;
        }

        /* Left column label and value */
        .break-table td.label { width: 65%; }
        .break-table td.value { width: 35%; text-align: right; }

        /* Totals styling (no inner border, just bold) */
        .total { font-weight: 700; padding-top: 8px; }

        /* Paid button placeholder (looks like a badge in PDF) */
        .paid-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #16a34a;
            color: #fff;
            font-weight: 700;
            border-radius: 3px;
            margin-top: 14px;
            text-decoration: none;
        }

        /* ensure small numbers align and have some spacing */
        .amount { min-width: 80px; display: inline-block; text-align: right; }
    </style>
</head>
<body>
<div class="salary-container">
    <!-- Header -->
    <div class="header">
        <h1>DigiPaeds School System</h1>
        <h2>Salary Slip - {{ \Carbon\Carbon::parse($salary->payment_date)->format('F Y') }}</h2>
        <div>Date: {{ \Carbon\Carbon::parse($salary->updated_at)->format('d-m-Y') }}</div>
    </div>

    <!-- Employee info as table (no inner borders) -->
    <table class="info-table" role="presentation">
        <tr>
            <td class="label">Name:</td>
            <td class="value">{{ $salary->teacher->name ?? $salary->staff->name }}</td>

            <td class="label">Mobile:</td>
            <td class="value">{{ $salary->teacher->mobile ?? $salary->staff->mobile }}</td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td class="value">{{ $salary->teacher->address ?? $salary->staff->address }}</td>

            <td class="label">Designation:</td>
            <td class="value">{{ $salary->teacher->designation ?? $salary->staff->designation }}</td>
        </tr>
    </table>

    <!-- Two columns: Earnings | Deductions -->
    <div class="columns">
        <!-- Earnings Table -->
        <table class="break-table" role="presentation" aria-label="Earnings">
            <thead>
            <tr><td colspan="2">Salary</td></tr>
            </thead>
            <tbody>
            <tr>
                <td class="label">Basic Pay</td>
                <td class="value">{{ number_format($salary->structure->basic_salary ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label">House Allowance</td>
                <td class="value">{{ number_format($salary->structure->house_allowance ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Medical Allowance</td>
                <td class="value">{{ number_format($salary->structure->medical_allowance ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Transport Allowance</td>
                <td class="value">{{ number_format($salary->structure->transport_allowance ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Other Allowance</td>
                <td class="value">{{ number_format($salary->structure->other_allowance ?? 0, 2) }}</td>
            </tr>

            <!-- Gross (bottom of this column) -->
            <tr class="total">
                <td class="label">Gross Salary</td>
                <td class="value">{{ number_format($salary->gross_salary ?? 0, 2) }}</td>
            </tr>
            </tbody>
        </table>

        <!-- Deductions Table -->
        <table class="break-table" role="presentation" aria-label="Deductions">
            <thead>
            <tr><td colspan="2">Deductions</td></tr>
            </thead>
            <tbody>
            @if(!empty($deductions) && count($deductions) > 0)
                @foreach($deductions as $deduction)
                    <tr>
                        <td class="label">{{ $deduction->name }} ({{$deduction->amount}}) x ({{$deduction->multiples}})</td>
                        <td class="value">{{ number_format(($deduction->amount ?? 0) * ($deduction->multiples ?? 1), 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="label">—</td>
                    <td class="value">0.00</td>
                </tr>
            @endif

            <!-- Net (bottom of this column) -->
            <tr class="total">
                <td class="label">Net Salary</td>
                <td class="value">{{ number_format($salary->net_salary ?? 0, 2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
