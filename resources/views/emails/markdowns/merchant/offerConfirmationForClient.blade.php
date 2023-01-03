

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
            <h2>Hello {{$offer['name']}}</h2><br>
        @endcomponent
    @endslot
    @component('mail::header', ['url' => config('app.url')])
        <img src="{{ $offer['user']->avatar()}}" style="width:80px; height: 80px; border-radius: 10px" alt="App Logo">
    @endcomponent
    <h1 style="text-align: center"> {{$offer['user']['company']['short_name']}}</h1>
    <h3 style="text-align: center">  NOTE FROM {{$offer['user']['company']['short_name']}}:</h3>
    @component('mail::table')
        | Transaction ID       | Transaction Date         | Amount  | Fee  |
        | :-------------: |:-------------:| :--------:|:--------:|
        | {{ $offer['transaction_id']}}      | {{ today_date()}}      | {{ $offer['amount']}} ({{$offer['user']['company']['currency']}}) | {{ $offer['amount']/100*$offer['commission']}} ({{$offer['user']['company']['currency']}})     |
    @endcomponent
    @slot('footer')
        @component('mail::footer')
                    Thanks,<br>
                    {{ config('app.name2') }}
        @endcomponent
    @endslot
@endcomponent

