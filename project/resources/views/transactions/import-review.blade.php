@extends('layouts.app')

@section('title', 'Review CSV Import - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
<li class="breadcrumb-item active">Review CSV import</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold">Review imported transactions</h1>
    <p class="text-secondary">Check each category suggestion and change it if needed. Nothing is saved until you confirm.</p>
    <div class="alert alert-info">Suggestions use your previous category choices and student-spending keyword rules. They are advisory; you can override every row.</div>
</div>

<form action="{{ route('transactions.import-csv.confirm') }}" method="POST">
    @csrf
    <input type="hidden" name="batch_id" value="{{ $batchId }}">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-dark"><tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Category suggestion / selection</th></tr></thead>
                <tbody>
                    @foreach($rows as $index => $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="text-capitalize">{{ $row['type'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['amount'] }}</td>
                            <td>
                                <select name="categories[{{ $index }}]" class="form-select @error('categories.'.$index) is-invalid @enderror" required>
                                    @foreach($categories->where('type', $row['type']) as $category)
                                        <option value="{{ $category->id }}" @selected($row['category_id'] === $category->id)>
                                            {{ $category->name }}{{ $row['suggested'] && $row['category_id'] === $category->id ? ' — suggested' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categories.'.$index)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel import</a>
            <button type="submit" class="btn btn-success">Confirm import ({{ count($rows) }} transactions)</button>
        </div>
    </div>
</form>
@endsection
