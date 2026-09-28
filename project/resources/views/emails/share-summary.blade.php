@extends('emails.layout')

@section('content')
<h1 style="margin:0 0 18px;font-size:24px;line-height:1.3;color:#071426;">{{ $title }}</h1>
<p>{{ $summary }}</p>

@if($details)
    <table cellpadding="8" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;border:1px solid #d7e5f8;">
        <tbody>
            @foreach($details as $label => $value)
                <tr style="border-bottom:1px solid #d7e5f8;">
                    <th align="left" style="color:#29415f;background-color:#f4f8ff;">{{ $label }}</th>
                    <td style="color:#0b1b33;">{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<p>This summary was shared from Campus Coin. It may contain personal financial information; only share it with someone you trust.</p>
@endsection
