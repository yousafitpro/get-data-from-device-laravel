<div class="sidebar">

    <div class="sidebar-background"></div>
    <div class="sidebar-wrapper scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    <img src="{{auth()->user()->avatar()}}" alt="..." class="avatar-img rounded-circle">
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
								<span>
									{{substr(auth()->user()->name,0,20)}}
									<span class="user-level">Administrator</span>
									<span class="caret"></span>
								</span>
                    </a>
                    <div class="clearfix"></div>

                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            <li>
                                <a href="{{url('profile')}}">
                                    <span class="link-collapse">My Profile</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{url('profile')}}">
                                    <span class="link-collapse">Edit Profile</span>
                                </a>
                            </li>
{{--                            <li>--}}
{{--                                <a href="#settings">--}}
{{--                                    <span class="link-collapse">Settings</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav">
                <li class="nav-item active">
                    <a href="{{url('/')}}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
{{--                        <span class="badge badge-count">5</span>--}}
                    </a>
                </li>
{{--                <li class="nav-section">--}}
{{--							<span class="sidebar-mini-icon">--}}
{{--								<i class="fa fa-ellipsis-h"></i>--}}
{{--							</span>--}}
{{--                    <h4 class="text-section">Components</h4>--}}
{{--                </li>--}}
                @if(auth()->user()->hasRole('company'))
{{--                <li class="nav-item">--}}
{{--                    <a data-toggle="collapse" href="#base">--}}
{{--                        <i class="fas fa-layer-group"></i>--}}
{{--                        <p>Payments</p>--}}
{{--                        <span class="caret"></span>--}}
{{--                    </a>--}}
{{--                    <div class="collapse" id="base">--}}
{{--                        <ul class="nav nav-collapse">--}}
{{--                            <li>--}}
{{--                                <a href="components/avatars.html">--}}
{{--                                    <span class="sub-item">Pending</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="components/buttons.html">--}}
{{--                                    <span class="sub-item">Completed</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="components/gridsystem.html">--}}
{{--                                    <span class="sub-item">Refunded</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="components/panels.html">--}}
{{--                                    <span class="sub-item">Waiting</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}

{{--                        </ul>--}}
{{--                    </div>--}}
{{--                </li>--}}
                <li class="nav-item">
                    <a href="{{route('merchant.offers.index')}}">
                        <i class="fas fa-desktop"></i>
                        <p>Offers / Payments</p>
{{--                        <span class="badge badge-count badge-success">4</span>--}}
                    </a>
                </li>
                    <li class="nav-item">
                        <a href="{{route('aptpay.refunds')}}">
                            <i class="fas fa-recycle"></i>
                            <p>Refunds</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('aptpay.withdraws')}}">
                            <i class="fas fa-arrow-circle-down"></i>
                            <p>Withdraw History</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('merchant.company.settings')}}">
                            <i class="fas fa-link"></i>
                            <p>My Link</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{url('support/create-query')}}">
                            <i class="fas fa-question-circle"></i>
                            <p>Support</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasRole('admin'))
                    <li class="nav-item">
                        <a href="{{url('support/all-queries')}}">
                            <i class="fas fa-question-circle"></i>
                            <p>Support</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{url('admin/offers/log')}}">
                            <i class="fas fa-list-alt"></i>
                            <p>Offers & Payments Log</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{url('payee/suggested_list')}}">
                            <i class="fas fa-question-circle"></i>
                            <p>Suggested Payees</p>
                            {{--                        <span class="badge badge-count badge-success">4</span>--}}
                        </a>
                    </li>
                <li class="nav-item">
                    <a href="{{route('merchant.company.index')}}">
                        <i class="fas fa-users"></i>
                        <p>Companies</p>
                        <span class="badge badge-count badge-success"></span>
                    </a>
                </li>
                    @endif

            </ul>
        </div>
    </div>
</div>
