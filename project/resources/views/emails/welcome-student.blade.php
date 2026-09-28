@extends('emails.layout')

@section('content')
<h1 style="margin:0 0 18px;font-size:24px;line-height:1.3;color:#071426;">Welcome to Campus Coin, {{ $user->name }}!</h1>
<p>Your student account is ready. You can now track income and spending, create category budgets, and follow savings goals in one place.</p>
<p>Your account is set to {{ config('regions.'.$user->region.'.name', 'your selected region') }} ({{ config('regions.'.$user->region.'.currency', 'local currency') }}).</p>
<p style="margin:28px 0;">
    <a href="{{ route('login') }}" style="display:inline-block;padding:12px 20px;border-radius:8px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">Sign in to Campus Coin</a>
</p>
<p>Your financial information is private to your account. You can update your profile and set your allowance or savings goal any time.</p>
@endsection
