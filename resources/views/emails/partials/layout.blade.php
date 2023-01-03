<div style="background-color: #edf2f7;">
    @include('emails.partials.header')
    @include('emails.partials.css')
 <div style="margin-right: 5%;margin-left: 5%; background-color: white; padding: 20px">
     @yield('content')
 </div>
    <br>
    @include("emails.partials.footer")
</div>

