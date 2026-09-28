@extends('emails.layout')

@section('content')
<h1 style="margin:0 0 18px;font-size:24px;line-height:1.3;color:#071426;">Hello {{ $user->name }},</h1>
<p>An administrator ({{ $adminName }}) reset the password for your Campus Coin account.</p>
<p>For your security, the new password is not included in this email. If you did not expect this change, use the password recovery page or contact your administrator.</p>
<p style="margin:28px 0;">
    <a href="{{ route('password.request') }}" style="display:inline-block;padding:12px 20px;border-radius:8px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">Open password recovery</a>
</p>
@endsection
