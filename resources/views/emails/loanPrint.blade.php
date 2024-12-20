<!DOCTYPE html>
<html>
<head>
    <title>Payday</title>
</head>
<body>
<h1>{{ $mailData['title'] }}</h1>
<p>{!! nl2br(e($mailData['body'])) !!}</p>

<p>Thank you</p>
</body>
</html>
