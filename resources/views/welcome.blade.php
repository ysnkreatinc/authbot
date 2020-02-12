@extends('layouts.app')


@section('content')
    
    

    <div class="container">
        <div class="justify-content-center">
            <h2 class="pt-5 font-weight-bold text-uppercase">ChatBot By Kreatinc - Mode Development</h2>

            <div class="card mt-5 p-3" style="width: 45rem;">
                <div class="card-body">
                <h5 class="card-title">Login</h5>
                <p>With Kreatinc, link your Pages with our intelligente Bot.</p>
                <a href="{{ route('login.facebook') }}" class="btn btn-primary"><i class="fa fa-facebook"></i> Continue with Facebook</a>
                </div>
            </div>
        </div>

    </div>

@endsection