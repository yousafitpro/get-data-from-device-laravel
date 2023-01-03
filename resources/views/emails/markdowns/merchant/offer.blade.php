

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

        <img src="{{auth()->user()->avatar()}}" style="width:80px; height: 80px; border-radius: 10px" alt="App Logo">

    @endcomponent
    <h1 style="text-align: center"> {{$companyName}} sent you a payment request</h1>
    <br>
    <h4 style="padding: 20px; text-align: center; color: lightgrey">Please find attached your invoice. Once you you review it, come back to this email and click pay button. Please note, convenience fee is extra charge that is charged by zpayd.com and is seperate from the enclosed bill</h4>
   <br>
    <h3 style="text-align: center">  NOTE FROM {{$companyName}}:</h3>
    <h4 style="text-align: center; padding:20px ">{{$transaction_details}} </h4>
    @component('mail::table')
        | Transaction ID       | Transaction Date         | Amount  |
        | :-------------: |:-------------:| :--------:|
        | {{$transaction_id}}      | {{$transaction_date}}      |   {{$amount}} ( {{$currency}} )    |
    @endcomponent
    <br>
    @component('mail::button', ['url' => $payurl,'color'=>"primary"])
        Pay Now
    @endcomponent



    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')


                    Thanks,<br>
                    {{ config('app.name2') }}


        @endcomponent
    @endslot
@endcomponent

