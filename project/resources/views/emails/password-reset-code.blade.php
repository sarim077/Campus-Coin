@extends('emails.layout')

@section('content')
<h1 style="margin:0 0 18px;font-size:24px;line-height:1.3;color:#071426;">Hello {{ $user->name }},</h1>
<p>We received a request to reset the password for your Campus Coin account. Enter this verification code on the password recovery page:</p>
<div style="margin:24px 0;padding:18px;text-align:center;border:1px solid #d7e5f8;border-radius:10px;background-color:#f4f8ff;color:#1d4ed8;font-size:30px;font-weight:700;letter-spacing:8px;">{{ $code }}</div>
<p>This code expires in 10 minutes and can only be used once.</p>
<p>If you did not request a reset, you can ignore this email.</p>
@endsection
