<title>@yield('title') {{$business['title']}}</title>
<link rel="icon" type="image/png" href="{{asset('images/logo.png')}}"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{asset('theme/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('theme/css/azzara.min.css')}}">
<link rel="stylesheet" href="{{asset('css/mystyle.css')}}">
<link rel="stylesheet" href="{{asset('line-control-master/editor.css')}}">


<script src="{{asset('theme/js/plugin/webfont/webfont.min.js')}}"></script>
<script src="{{asset('qrcodejs/qrcode.js')}}"></script>
<script src="{{asset('html2canvas/html2canvas.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery 3 -->
<script src="{{asset('/')}}assets/vendor_components/jquery-3.3.1/jquery-3.3.1.js"></script>
<script>
    WebFont.load({
        google: {"families":["Open+Sans:300,400,600,700"]},
        custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands"], urls: ["{{asset('theme/css/fonts.css')}}"]},
        active: function() {
            sessionStorage.fonts = true;
        }
    });
</script>
<!-- CSS Just for demo purpose, don't include it in your project -->
<link  href="{{asset('theme/css/demo.css')}}">

<style>

    :root {
        --blue: #1e90ff;
        --white: #ffffff;
    }
    .onMouse:hover {
        background-color:#f28038;
        color: white;
    }
    .mybtn{
        border:solid 1px #f28038;
        color:#f28038
    }
</style>

