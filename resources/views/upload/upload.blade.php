@extends('layout.main')

@section('content')
    <div class="container">
        <form action="/upload/pdf" method="post" enctype="multipart/form-data">
            {{csrf_field()}}
            <input type="file" name="pdf">
            <input type="submit" value="上传">
        </form>
    </div>


@endsection

@section('footer')
    @parent
@endsection