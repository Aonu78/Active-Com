@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">Update Shop View Count</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">Shop: {{ $shop->name }}</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form action="{{ route('sellers.views.update') }}" method="POST">
                @csrf
                <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                <div class="form-group">
                    <label for="no_of_views">Number of Views</label>
                    <input type="number" name="no_of_views" id="no_of_views" class="form-control" value="{{ $shop->no_of_views }}" required min="0">
                </div>
                <div class="form-group mt-3">
                    <label for="created_at">Created At (Optional)</label>
                    <input type="datetime-local" name="created_at" id="created_at" class="form-control"
                        value="{{ $shop->created_at ? \Carbon\Carbon::parse($shop->created_at)->format('Y-m-d\TH:i') : '' }}">
                </div>
                <button type="submit" class="btn btn-primary mt-3">Update Views</button>
            </form>
        </div>
    </div>
</div>
@endsection
