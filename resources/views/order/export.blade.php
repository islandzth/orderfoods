<!DOCTYPE html>
<html>
<head>
    <title>Order Foods</title>
</head>
<body>
    <h1>Order Foods</h1>

    <form action="{{ route('exportToExcel') }}" method="GET">
        <div>
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date">
        </div>
        <div>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date">
        </div>
        <button type="submit">Export to Excel</button>
    </form>


</body>
</html>
