@if(session('message'))
    <p class="text-danger">* {{session('message')}}</p>
@endif
@if(session('successMessage'))
    <p class="text-success">{{session('successMessage')}}</p>
@endif
@foreach($errors->all() as $error)
    <p class="text-danger">* {{$error}}</p>
@endforeach
