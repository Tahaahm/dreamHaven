<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification List</title>
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
            max-height: 300px; /* Set the max height */
            overflow-y: auto; /* Enable vertical scrolling */
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
        }

        table tbody tr {
            border-bottom: 1px solid #e2e6f2;
        }

        table tbody tr:last-child {
            border-bottom: none;
        }

        .notification {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notification .details {
            display: flex;
            flex-direction: column;
        }

        .notification .details small {
            color: #8c94a9;
            font-size: 12px;
        }

        .timestamp {
            color: #8c94a9;
            font-size: 12px;
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
            <h2>Notifications</h2>
            <div class="btn-group">
                <button class="filters">Filters</button>
                <button class="add-notification">Add Notification</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th><input type="checkbox" name="select_all"></th>
                    <th>Notification</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($notifications as $notification)
                    <tr>
                        <td><input type="checkbox" name="select"></td>
                        <td class="notification">
                            <div class="details">
                                <span>{{ $notification->title }}</span>
                                <small>{{ $notification->message }}</small>
                            </div>
                        </td>
                        <td class="timestamp">{{ $notification->sent_at->format('Y-m-d h:i A') }}</td>
                        <td class="actions">
                            <!-- Actions like "Mark as Read", "Delete" etc. -->
                            <a href="{{ route('notifications.markAsRead', $notification->id) }}">Mark as Read</a>
                            <a href="{{ route('notifications.delete', $notification->id) }}">Delete</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
