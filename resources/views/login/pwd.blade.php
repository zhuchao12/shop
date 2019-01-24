@extends('layout.bst')


@section('header')
    @parent
    <p style="color: red;">This is Child header.</p>
@endsection

@section('content')
    <form method="post" action="/pwd2">
        {{csrf_field()}}
        <div class="form-group">
            <label for="exampleInputEmail1"> 用户名：</label>
            <input type="text" class="form-control" id="exampleInputName" placeholder="name"  name="name">
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">新密码:</label>
            <input type="password" class="form-control" id="exampleInputPassword1" placeholder="password"  name="password">
        </div>
        <button type="submit" class="btn btn-success form-control">修改</button>

    </form>

@endsection


@section('footer')
    @parent
@endsection