

<style>
    .button{
        padding: 10px;
        padding-left: 20px;
        padding-right: 20px;
        background-color: darkorange;
    }

</style>
@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <h2>Hello {{$name}}</h2><br>
        @endcomponent
    @endslot
    @component('mail::header', ['url' => config('app.url')])

        <img src="{{$image_url}}" style="width:80px; height: 80px; border-radius: 10px" alt="App Logo">

    @endcomponent
<br>
    <h3 style="text-align: center">This email is now your username for your merchant profile. Login password will be given to you via seperate email.
        For any other questions relating to your account please email us at support@zpayd.com </h3>


<br>

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')


                    Thanks,<br>
                    {{ config('app.name2') }}


        @endcomponent
    @endslot
@endcomponent

