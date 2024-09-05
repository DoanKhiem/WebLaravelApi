<!DOCTYPE html>
<html>
<head>
    <title>Laravel 11 Generate PDF Example - Payday</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" >
</head>
<body>
<h1>{{ $title }}</h1>
<p>{{ $date }}</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut labore et dolore magna aliqua.</p>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Package</th>
        <th>Amount</th>
    </tr>
    @foreach($loans as $item)
    <tr>
        <td>{{ $item->id }}</td>
        <td>{{ $item->customer->first_name }} {{ $item->customer->last_name }}</td>
        <td>{{ $item->package->title }}</td>
        <td>{{ $item->amount }}</td>
    </tr>
    @endforeach
</table>

</body>
</html>
