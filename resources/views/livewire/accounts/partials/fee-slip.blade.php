<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fee Slip</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .student-info { margin-bottom: 20px; }
        .student-info img { width: 80px; height: 80px; border-radius: 50%; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .totals { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DigiPaeds School System</h1>
        <h2>Fee Payment Slip</h2>
        <p>Date: {{ $date }}</p>
    </div>

    <div class="student-info"> 
        <p><strong>Student ID :</strong> {{ $student->student_id }}</p> 
        <p><strong>Class :</strong> {{ $student->class }} - {{$student->section}}</p> 
        <p><strong>Name :</strong> {{ $student->name }} <strong> S/O :</strong> {{ $student->parent->name }}</p> 
        <p><strong>Mobile :</strong> {{ $student->parent->mobile }} <strong> Address :</strong> {{ $student->parent->address }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fee Type</th>
                <th>Amount</th>
                <th>Pending</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fees as $fee)
                <tr>
                    <td>{{ $fee->feeStructure->name }}</td>
                    <td>{{ $fee->amount }}</td>
                    <td>{{ $fee->pending_amount }}</td>
                    <td>{{ ucfirst($fee->status) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td>Total</td>
                <td>{{ $totalAmount }}</td>
                <td>{{ $totalPending }}</td>
                <td>Paid: {{ $paidFee }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
