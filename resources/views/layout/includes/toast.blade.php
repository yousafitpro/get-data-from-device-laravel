@php
    $toast=session('toast');
@endphp
<script>
    $(function () {
        @if($toast)
        $.notify({
            icon: 'flaticon-alarm-1',
            title: '{{$toast['heading']}}',
            message: '{{$toast['message']}}',
        },{
            type: '{{$toast['type']}}',
            placement: {
                from: "top",
                align: "right"
            },
            time: 1000,
        });
        @endif
    })

</script>
