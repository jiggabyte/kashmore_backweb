@extends('../../layouts.templater')

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
                <div class="card-header">{{ __('Confirm Password') }}</div>

                <div class="card-body">
                    {{ __('Please confirm your password before continuing.') }}

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Confirm Password') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
