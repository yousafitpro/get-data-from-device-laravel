




<!--   Core JS Files   -->

<script src="{{asset('theme/js/core/jquery.3.2.1.min.js')}}"></script>
<script src="{{asset('theme/js/core/popper.min.js')}}"></script>
<script src="{{asset('theme/js/core/bootstrap.min.js')}}"></script>

<!-- jQuery UI -->
<script src="{{asset('theme/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js')}}"></script>
<script src="{{asset('theme/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js')}}"></script>

<!-- jQuery Scrollbar -->
<script src="{{asset('theme/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js')}}"></script>

<!-- Moment JS -->
<script src="{{asset('theme/js/plugin/moment/moment.min.js')}}"></script>



<!-- jQuery Sparkline -->
<script src="{{asset('theme/js/plugin/jquery.sparkline/jquery.sparkline.min.js')}}"></script>

<!-- Chart Circle -->
<script src="{{asset('theme/js/plugin/chart-circle/circles.min.js')}}"></script>

<!-- Datatables -->
<script src="{{asset('theme/js/plugin/datatables/datatables.min.js')}}"></script>

<!-- Bootstrap Notify -->
<script src="{{asset('theme/js/plugin/bootstrap-notify/bootstrap-notify.min.js')}}"></script>

<!-- Bootstrap Toggle -->
<script src="{{asset('theme/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js')}}"></script>

<!-- jQuery Vector Maps -->
<script src="{{asset('theme/js/plugin/jqvmap/jquery.vmap.min.js')}}"></script>
<script src="{{asset('theme/js/plugin/jqvmap/maps/jquery.vmap.world.js')}}"></script>

<!-- Google Maps Plugin -->
<script src="{{asset('theme/js/plugin/gmaps/gmaps.js')}}"></script>

<!-- Sweet Alert -->
<script src="{{asset('theme/js/plugin/sweetalert/sweetalert.min.js')}}"></script>

<!-- Azzara JS -->
<script src="{{asset('theme/js/ready.min.js')}}"></script>

<!-- Azzara DEMO methods, don't include it in your project! -->
<script src="{{asset('theme/js/setting-demo.js')}}"></script>
<script src="{{asset('line-control-master/editor.js')}}"></script>
{{--<script src="{{asset('theme/js/demo.js')}}"></script>--}}

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $("#txtEditor").Editor();
    });

</script>
@include('layout.includes.toast')
