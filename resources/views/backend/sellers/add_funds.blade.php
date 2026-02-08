@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ translate('Add or Subtract Seller Funds') }}</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Seller Information') }}</h5>
        </div>

        <div class="card w-50 mx-auto mt-5">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Manage Seller Funds</h5>
            </div>
            <div class="card-body">
                <p><strong>Current Balance:</strong> $<span id="currentBalance">{{ $balance }}</span></p>
                
                <div class="mb-3">
                    <label for="fundAmount" class="form-label">Amount</label>
                    <input type="number" class="form-control" id="fundAmount" placeholder="Enter amount">
                </div>

                <div class="d-flex justify-content-between">
                    <button class="btn btn-success w-48" onclick="add_funds_btn()">Add Fund</button>
                    <button class="btn btn-danger w-48" onclick="subtract_funds_btn()">Subtract Fund</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function add_funds_btn() {
        let amount = parseFloat($('#fundAmount').val());
        let user_id = {{ $user->id }};
        if (isNaN(amount) || amount <= 0) {
            AIZ.plugins.notify('danger', 'Please enter a valid amount.');
            return;
        }

        $.post('{{ route('sellers.add_funds.store') }}', {
            _token: '{{ csrf_token() }}',
            user_id: user_id,
            amount: amount
        }, function (response) {
            if (response.status === 'success') {
                $('#currentBalance').text(response.new_balance);
                $('#fundAmount').val('');
                AIZ.plugins.notify('success', response.message);
            } else {
                AIZ.plugins.notify('danger', 'Failed to add funds.');
            }
        }).fail(function () {
            AIZ.plugins.notify('danger', 'Server error. Please try again.');
        });
    }

    function subtract_funds_btn() {
        let amount = parseFloat($('#fundAmount').val());
        let user_id = {{ $user->id }};
        if (isNaN(amount) || amount <= 0) {
            AIZ.plugins.notify('danger', 'Please enter a valid amount.');
            return;
        }

        $.post('{{ route('sellers.subtract_funds.store') }}', {
            _token: '{{ csrf_token() }}',
            user_id: user_id,
            amount: amount
        }, function (response) {
            if (response.status === 'success') {
                $('#currentBalance').text(response.new_balance);
                $('#fundAmount').val('');
                AIZ.plugins.notify('success', response.message);
            } else {
                AIZ.plugins.notify('danger', response.message || 'Failed to subtract funds.');
            }
        }).fail(function () {
            AIZ.plugins.notify('danger', 'Server error. Please try again.');
        });
    }
</script>

@endsection
