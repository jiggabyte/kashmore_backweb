@extends('../layouts.templater')

@section('homer')

    <div style="background-image: url('{{ asset('img/bg.jpg') }}'); background-size:cover;" class="back-div">
        <div class="overlay-bg"></div>
        <div class="login-reg">

            @guest
                <a class="" href="{{ route('login') }}"><button class="btn btn-primary btn-success">{{ __('Login') }}</button></a>
                @if (Route::has('register'))
                    <a class="" href="{{ route('register') }}"><button class="btn btn-primary btn-info">{{ __('Register') }}</button></a>
                @endif
            @else

                <li class="nav-item dropdown profile-r">
                    <a id="navbarDropdown" style="font-weight:bold;color:black!important;" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            @endguest
        </div>
        <img src="{{ asset('img/taxi-cab.png') }}" class="car-lay" />
        <div class="text-lay">
            <h1>Ride Safe, Ride with Comfort</h1>
            <br />
            <p class="book-p">Get to your destination in style.</p>
            <p class="book-p">Move Around Town Safely with Comfort.</p>
            <a href="{{ url('/ride') }}"><button class="btn btn-primary btn-warning book-b">Book Now !</button></a>
        </div>


        <div class="spacer"></div>

    </div>

@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class=" col-md-4" style="background-color:;">

            <img id="sign" class="mobile-png" src="{{ asset('img/mobile.png') }}" alt="" />

        </div>
        <div class="col-md-8">
            <br />
            <br />
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address  or Your Phone number') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address and/or phone number.') }}
                        </div>
                    @endif

                    {{ __('Before proceeding, please check your email/phone number for a verification link.') }}
                    {{ __('If you did not receive the email or sms') }}.
                    <br />
                    <br />
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('Click here to request another email') }}</button>.
                    </form>
                    <br />
                    <br />
                    <form class="d-inline" method="POST" action="{{ url('/sms-resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('Click here to request another sms') }}</button>.
                    </form>
                    <br />
                    <br />
                    <form class="d-inline" method="POST" action="{{ url('/sms-verify') }}" >
                        <div class="form-group row" >
                            <label for="sms" class="col-md-4 col-form-label text-md-right">{{ __('SMS Code') }}</label>

                            <div class="col-md-6">
                                <input id="sms" type="text" style="width:100px;" class="form-control" name="sms" value="{{ old('sms') }}" required autocomplete="sms" autofocus>
                                <br />
                                @include('flash::message')

                        @csrf
                        <button type="submit" class="btn btn-primary align-baseline">{{ __('Verify') }}</button>.
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
