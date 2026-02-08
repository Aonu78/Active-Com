@extends('backend.layouts.app')

@section('content')


<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Guarantee Money')}}</h5>
</div>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5>Add Guarantee Money for {{ $shop->name }}</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('sellers.guarantee_money', encrypt($shop->id)) }}">
                @csrf
                <div class="form-group">
                    <label for="guarantee_money">Amount</label>
                    <input type="number" name="guarantee_money" class="form-control" id="guarantee_money" value="{{ old('guarantee_money', $shop->guarantee_money) }}" required>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Save</button>
            </form>
        </div>
    </div>
</div>


@endsection
