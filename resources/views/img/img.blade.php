<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h2>照片</h2>
      @foreach($img as $v)
            <img src="{{env('IMG_HOST')}}/storage/{{$v->path}}" alt="暂无图片" weigh='50' hight='200' >
        @endforeach
</body>
</html>