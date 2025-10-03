<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .allin {
            
            margin-top: 10px;
            margin-left: 225px;
        }

        .container {
            width: 90%;
            margin: 40px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .header .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-group button {
            background-color: #4a7cfe;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-group button.filters {
            background-color: #e2e6f2;
            color: #4a7cfe;
        }

        .table-container {
            max-height: 300px; /* Adjust the height as needed */
            overflow-y: auto;
            border: 1px solid #e2e6f2; /* Optional: add a border to the scroll area */
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background-color: #f4f7fa;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
        }

        table th {
            color: #5e6d82;
            font-size: 14px;
            position: sticky; /* Make header sticky */
            top: 0;
            background-color: #f4f7fa; /* Match the header background */
            z-index: 1;
        }

        table tbody tr {
            border-bottom: 1px solid #e2e6f2;
        }

        table tbody tr:last-child {
            border-bottom: none;
        }

        .details {
            display: flex;
            flex-direction: column;
        }

        .details small {
            color: #8c94a9;
            font-size: 12px;
        }

        .date {
            color: #4a7cfe;
            font-weight: bold;
        }

        .time {
            background-color: #b7e5d5;
            color: #2c8c75;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .actions {
            cursor: pointer;
            color: #8c94a9;
        }
    </style>
</head>
<body>
@include('layouts.sidebar')

<div class="allin">
    <div class="container">
        <div class="header">
            <h2>Schedule</h2>
            <div class="btn-group">
                <button class="filters">Filters</button>
                <button class="add-schedule">Add Schedule</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" name="select_all"></th>
                        <th>Task</th>
                        <th>Details</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                        <tr>
                            <td><input type="checkbox" name="select"></td>
                            <td>{{ $appointment->task }}</td>
                            <td class="details">
                                <span>{{ $appointment->details }}</span>
                                <small>{{ $appointment->location }}</small>
                            </td>
                            <td class="date">{{ $appointment->date }}</td>
                            <td><span class="time">{{ $appointment->time }}</span></td>
                            <td class="actions">...</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>