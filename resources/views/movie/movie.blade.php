@extends('layout.main')

@section('content')
    <h1>Movie</h1>
    @foreach($seat as $k=>$v)
        @if($v==1)
            <button class="btn-default btn-danger"><a href="/movie/buy/{{$k}}" class="{{$k}}">座位</a></button>
        @else
            <button class="btn-default btn-info"><a href="/movie/buy/{{$k}}" class="{{$k}}">座位</a></button>
        @endif
    @endforeach

@endsection

@section('footer')
@endsection