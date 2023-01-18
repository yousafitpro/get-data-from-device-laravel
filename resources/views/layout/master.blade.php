<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout.includes.css')
    <!-- Chart JS -->
    <script src="{{asset('theme/js/plugin/chart.js/chart.min.js')}}"></script>

    @yield('css')


</head>
<style>
    .content{
        padding-top: 30px;
    }

    * {
        /*box-sizing: border-box;*/
    }
    form {
        width: 100%;
        margin: 0 auto;
    }
    .form-field {
        width: 100%;
        height: 40px;
        position: relative;
        background: white;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 0 3px 0px rgba(0, 0, 0, .3);
        padding: 0 10px;
    }
    iframe {
        width: 100%;
        height: 100%;
    }
    .form-field-group {
        display: flex;
        flex-flow: wrap;
    }
    .form-field-group div {
        flex: 0 0 50%;
    }
    .form-field-group div:first-child {
        border-radius: 4px 0 0 4px;
    }
    .form-field-group div:last-child {
        border-radius: 0 4px 4px 0;
    }
    .form-button {
        border: 1px solid #1f8ab0;
        background-color: #3b495c;
        border-color: #3b495c;
        color: #ced5e0;
        font-family: inherit;
        border-radius: 4px;
        font-size: 16px;
        height: 35px;
        width: 100%;
    }
    :root{
        --primary:#f28038;
        --blue:#f28038
    }
/*adasd*/
</style>
<body>
<!-- Chart Circle -->
<script src="{{asset('theme/js/plugin/chart-circle/circles.min.js')}}"></script>
<div class="wrapper">

    <!--
    Tip 1: You can change the background color of the main header using: data-background-color="blue | purple | light-blue | green | orange | red"
-->
   @include("layout.navbar")
    <!-- Sidebar -->
    @include("layout.sidebar")
    <div class="main-panel">
        <div class="content">
            <div class="page-inner">
       @yield('content')
            </div>
        </div>
    </div>

    @include('layout.includes.js')
@yield('script')
{{--@yield('js')--}}
<!-- Insert this div where you want Railz Connect to be initialized -->



{{--<!-- Start of Railz Connect script -->--}}
{{--<script src="https://connect.railz.ai/v1/railz-connect.js"></script>--}}
{{--<script>--}}
{{--    var widget = new RailzConnect();--}}
{{--    widget.mount({--}}
{{--        parentElement: document.getElementById('railz-connect'),--}}
{{--        widgetId: 'wid_prod_9f401b23-86f0-483d-a3de-32895abddb63'--}}
{{--    });--}}
{{--</script>--}}
{{--<!-- End of Railz Connect script -->--}}
{{--<!-- Insert this div where you want Railz Connect to be initialized -->--}}
{{--<div id="railz-connect"></div>--}}
<script>
    $(document).ready(function() {
        $('.js-example-basic-single').select2();
    });
</script>
    <script>
        $('#myTable').DataTable({
            "order": []
        })
        $('#myTable1').DataTable({
            "order": []
        })
        $('#myTable2').DataTable({
            "order": []
        })
        $('#myTable3').DataTable({
            "order": []
        })
        $('#myTable4').DataTable({
            "order": []
        })
        $('#myTable5').DataTable({
            "order": []
        })
        $('#myTable6').DataTable({
            "order": []
        })
        $('#myTable7').DataTable({
            "order": []
        })
        $('#myTable8').DataTable({
            "order": []
        })
        $('#myTable9').DataTable({
            "order": []
        })
        $('#myTable10').DataTable({
            "order": []
        })
        $('#myTable11').DataTable({
            "order": []
        })
        $('#myTable12').DataTable({
            "order": []
        })
    </script>
</div>
</body>
</html>
