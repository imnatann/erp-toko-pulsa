@extends('layouts.app')

@section('title', 'Login Internal')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Login Internal</div>
            <h1>Masuk ke ERP operasional toko.</h1>
            <p>Gunakan akun internal untuk membuka dashboard, ticketing digital, dan workflow validasi.</p>
        </div>
    </section>

    <form method="post" action="{{ route('login.store') }}" class="panel stack" style="max-width: 520px;">
        @csrf

        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <label>
            <input type="checkbox" name="remember" value="1" style="width: auto;"> Ingat sesi login
        </label>

        <div class="actions">
            <button class="button button-primary" type="submit">Masuk</button>
        </div>
    </form>
@endsection
