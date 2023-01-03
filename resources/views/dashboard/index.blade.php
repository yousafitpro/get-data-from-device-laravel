@extends('layout.master')
@section('title',"Dashboard")
@section('content')


            <div class="page-header">
                <h4 class="page-title">Dashboard</h4>

            </div>
            @if(auth()->user()->hasRole('admin'))
            <div class="row">

                   <div class="col-sm-6 col-md-3">
                       <div class="card  card-round" style="min-height: 220px">
                           <div class="card-body myFlex">
                              <div style="text-align: center">
                                <img style="width: 80px;" src="{{asset('icons/ip.png')}}">
                                  <br>

                                  <h2 >IP Addresses</h2>

                                  <a href="{{route('ip.index')}}" class="btn btn-primary btn-sm btn-block" style="width: 120px">Manage
                                      <i class="fas fa-chevron-right" style="font-size: 10px; margin-left: 5px"></i>
                                  </a>
                              </div>
                           </div>
                       </div>

                   </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card  card-round" style="min-height: 220px">
                        <div class="card-body myFlex">
                            <div style="text-align: center">
                                <img style="width: 80px;" src="{{asset('icons/countries.jpg')}}">
                                <br>

                                <h2 >Countries</h2>

                                <a href="{{route('country.index')}}" class="btn btn-primary btn-sm btn-block" style="width: 120px">Manage
                                    <i class="fas fa-chevron-right" style="font-size: 10px; margin-left: 5px"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card  card-round" style="min-height: 220px">
                        <div class="card-body myFlex">
                            <div style="text-align: center">
                                <img style="width: 80px;" src="{{asset('icons/location-markers.png')}}">
                                <br>

                                <h2 >Visitors</h2>

                                <a href="{{route('ip.visitors')}}" class="btn btn-primary btn-sm btn-block" style="width: 120px">Manage
                                    <i class="fas fa-chevron-right" style="font-size: 10px; margin-left: 5px"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
             </div>
@endif
            @if(auth()->user()->hasRole('company'))
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body ">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Approved Payments</p>
                                        <h4 class="card-title">{{$data['offerPaymentsSum']+$data['linkPaymentsSum']}}  ( {{auth()->user()->company?auth()->user()->company->currency:''}} )</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-info bubble-shadow-small">
                                        <i class="far fa-newspaper"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Available Balance</p>
                                        <h4 class="card-title">{{my_wallet_balance(auth()->id())}}  ( {{auth()->user()->company?auth()->user()->company->currency:''}} )</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="far fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Deposit History</p>
                                        <h4 class="card-title">{{$data['fundsClearedSum']}} ( {{auth()->user()->company?auth()->user()->company->currency:''}} )</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                        <i class="far fa-check-circle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Refunds</p>
                                        <h4 class="card-title">{{$data['total_refund']}}  ( {{auth()->user()->company?auth()->user()->company->currency:''}} )</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-head-row">
                                <div class="card-title">Monthly Sales & Refunds</div>
                                <div class="card-tools">

                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="min-height: 375px">
                                <canvas id="statisticsChart"></canvas>
                            </div>
                            <div id="myChartLegend"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="card card-info bg-info-gradient">
                        <div class="card-body">
                            <h4 class="mb-1 fw-bold">Offers Acceptence</h4>
                            <div id="task-complete" class="chart-circle mt-4 mb-3"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif





</div>

    <script>
        Circles.create({
            id:           'task-complete',
            radius:       50,
            value:        '{{$data['merchant_offers_success_percentage']}}',
            maxValue:     100,
            width:        5,
            text:         function(value){return value + '%';},
            colors:       ['#36a3f7', '#fff'],
            duration:     400,
            wrpClass:     'circles-wrp',
            textClass:    'circles-text',
            styleWrapper: true,
            styleText:    true
        })
        //Notify
        var ctx = document.getElementById('statisticsChart').getContext('2d');

        var statisticsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [
                    {
                    label: "Monthly Sales",
                    borderColor: '#177dff',
                    pointBackgroundColor: 'rgba(23, 125, 255, 0.2)',
                    legendColor: '#177dff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 4,
                        pointHoverBorderWidth: 1,
                        pointRadius: 4,
                        backgroundColor: 'transparent',
                        fill: true,
                        borderWidth: 2,
                    data: @json($data['monthlySalesData'])
                },
                    {
                        label: "Offers Refund",
                        borderColor: '#f3545d',
                        pointBackgroundColor: 'rgba(243, 84, 93, 0.2)',
                        legendColor: '#f3545d',
                        pointBorderWidth: 2,
                        pointHoverRadius: 4,
                        pointHoverBorderWidth: 1,
                        pointRadius: 4,
                        backgroundColor: 'transparent',
                        fill: true,
                        borderWidth: 2,
                        data: @json($data['monthlyRefundData'])
                    }

                ]
            },
            options : {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                },
                tooltips: {
                    bodySpacing: 4,
                    mode:"nearest",
                    intersect: 0,
                    position:"nearest",
                    xPadding:10,
                    yPadding:10,
                    caretPadding:10
                },
                layout:{
                    padding:{left:15,right:15,top:15,bottom:15}
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            fontColor: "rgba(0,0,0,0.5)",
                            fontStyle: "500",
                            beginAtZero: false,
                            maxTicksLimit: 5,
                            padding: 20
                        },
                        gridLines: {
                            drawTicks: false,
                            display: false
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            zeroLineColor: "transparent"
                        },
                        ticks: {
                            padding: 20,
                            fontColor: "rgba(0,0,0,0.5)",
                            fontStyle: "500"
                        }
                    }]
                },
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend html-legend">');
                    for (var i = 0; i < chart.data.datasets.length; i++) {
                        text.push('<li><span style="background-color:' + chart.data.datasets[i].legendColor + '"></span>');
                        if (chart.data.datasets[i].label) {
                            text.push(chart.data.datasets[i].label);
                        }
                        text.push('</li>');
                    }
                    text.push('</ul>');
                    return text.join('');
                }
            }
        });
        // generate HTML legend

        var myLegendContainer = document.getElementById("myChartLegend");

        // generate HTML legend
        myLegendContainer.innerHTML = statisticsChart.generateLegend();




    </script>
@endsection
