

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
<button onclick="sendRequest()">Send Request</button>

<script>
    var message = 'amit';
    var key= '123';
    var encrypted = CryptoJS.AES.encrypt(message, key);
    console.log(encrypted.toString());
    console.log(atob('asdasd'))
function sendRequest()
{

    var payload={
        'name':encrypted.toString(),
        _token:"{{ csrf_token() }}"
    }
    $.post("{{url('test-data')}}",payload, function (data) {
        console.log(data)
    });
}
</script>
