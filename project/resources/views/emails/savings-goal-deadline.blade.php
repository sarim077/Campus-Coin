@extends('emails.layout')

@section('content')
<h1 style="margin:0 0 18px;font-size:24px;line-height:1.3;color:#071426;">Hello {{ $user->name }},</h1>
@if($notificationType === 'achieved')
    <p>Congratulations! You reached your savings goal by {{ $dueDate }}.</p>
    <p>You saved {{ $currencySymbol }}{{ number_format($savedAmount, 2) }} toward your {{ $currencySymbol }}{{ number_format($goalAmount, 2) }} goal.</p>
@else
    <p>Your savings goal is due tomorrow, {{ $dueDate }}.</p>
    <p>You have {{ $currencySymbol }}{{ number_format($savedAmount, 2) }} saved toward your {{ $currencySymbol }}{{ number_format($goalAmount, 2) }} goal. You are {{ $currencySymbol }}{{ number_format($shortfall, 2) }} away.</p>
@endif

<p style="margin:28px 0;">
    <a href="{{ route('student.dashboard') }}" style="display:inline-block;padding:12px 20px;border-radius:8px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">View your Campus Coin dashboard</a>
</p>
@endsection
