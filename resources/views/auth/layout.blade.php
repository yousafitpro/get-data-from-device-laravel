<!DOCTYPE html>
<html lang="en">
<head>
@include("layout.includes.css")

</head>


            @yield('content')


@include("layout.includes.js")

<script>
    $('.web-form').on('submit', function (e) {
        $(this).find("input[type='submit']").attr('disabled', true)
        $(this).find("button[type='submit']").attr('disabled', true)
        $(this).find("button[type='submit']").text('Loading..')
        setTimeout(function (){
            $(this).find("button[type='submit']").attr('disabled', false)
            $(this).find("button[type='submit']").text('SIGN IN')

        },1000)
    })
</script>
@php
    $toast=session('toast');

@endphp
<h1>

</h1>
<script>

    $(function () {
{{--        @if($toast)--}}

{{--        --}}{{--toastAlert('{{$toast['heading']}}', '{{$toast['message']}}', '{{$toast['type']}}');--}}

{{--            $.notify({--}}
{{--            icon: 'flaticon-alarm-1',--}}
{{--            title: '{{$toast['heading']}}',--}}
{{--            message: '{{$toast['message']}}',--}}
{{--        },{--}}
{{--            type: "{{$toast['type']}}",--}}
{{--            placement: {--}}
{{--            from: "top",--}}
{{--            align: "right"--}}
{{--        },--}}
{{--            time: 1000,--}}
{{--        });--}}

{{--        @endif--}}
    })
</script>
</body>
</html>
