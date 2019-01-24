@extends('layout.main')

@section('content')
    <h1>Movie</h1>
    @foreach($seat as $k=>$v)
        @if($v==1)
            <button class="btn-default btn-danger">座位</button>
        @else
            <button class="btn-default btn-info">座位</button>
        @endif
    @endforeach

@endsection

@section('footer')
@endsection