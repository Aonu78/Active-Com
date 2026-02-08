@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Wallet Transaction Report')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <form action="{{ route('wallet-history.index') }}" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ translate('Wallet Transaction') }}</h5>
                    </div>
                    @if(Auth::user()->user_type != 'seller')
                    <div class="col-md-3 ml-auto">
                        <select id="demo-ease" class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="user_id">
                            <option value="">{{ translate('Choose User') }}</option>
                            @foreach ($users_with_wallet as $key => $user)
                                <option value="{{ $user->id }}" @if($user->id == $user_id) selected @endif >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm aiz-date-range" id="search" name="date_range"@isset($date_range) value="{{ $date_range }}" @endisset placeholder="{{ translate('Daterange') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-md btn-primary" type="submit">
                            {{ translate('Filter') }}
                        </button>
                    </div>
                </div>
            </form>
            <div class="card-body">

                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Payment Method') }}</th>
                            <th>{{ translate('Receipt') }}</th>
                            <th class="text-right">{{ translate('Approval') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wallets as $key => $wallet)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $wallet->user->name ?? translate('User Not found') }}</td>
                                <td>{{ $wallet->created_at->format('d-m-Y') }}</td>
                                <td>{{ single_price($wallet->amount) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $wallet->payment_method)) }}</td>

                                {{-- Receipt --}}
                                <td>
                                    @if ($wallet->reciept)
                                        <a href="{{ asset('public/' . $wallet->reciept) }}" target="_blank">
                                            <img src="{{ asset('public/' . $wallet->reciept) }}" alt="receipt" width="50">
                                        </a>
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                </td>

                                {{-- Approval Button --}}
                                <td class="text-right">
                                    @if ($wallet->approval)
                                        <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                                    @else
                                        <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                data-target="#approveWalletModal"
                                                data-id="{{ $wallet->id }}">
                                            {{ translate('Approve') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="aiz-pagination mt-4">
                    {{ $wallets->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="approveWalletModal" tabindex="-1" role="dialog" aria-labelledby="approveWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approveWalletModalLabel">{{ translate('Confirm Approval') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        {{ translate('Are you sure you want to approve this wallet recharge?') }}
      </div>
      <div class="modal-footer">
        <form id="approveForm" method="POST">
          @csrf
          <input type="hidden" name="id" id="wallet_id">
          <input type="hidden" name="status" value="1">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ translate('Yes, Approve') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    jQuery('#approveWalletModal').on('show.bs.modal', function (event) {
        const button = jQuery(event.relatedTarget);
        const walletId = button.data('id');
        jQuery('#wallet_id').val(walletId);
    });

    jQuery('#approveForm').on('submit', function (e) {
        e.preventDefault();
        const form = jQuery(this);
        const formData = form.serialize();

        fetch("{{ route('offline_recharge_request.approved') }}", {
            method: "POST",
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            const status = data.trim().charAt(0); // Get the first character after trimming whitespace

            if (status === '1') {
                AIZ.plugins.notify('success', 'Wallet recharge approved successfully.');
                location.reload(); // Optionally add a delay
            } else {
                AIZ.plugins.notify('danger', 'Approval failed. Please try again.');
            }
        })
        .catch(error => {
            console.error(error);
            AIZ.plugins.notify('danger', 'An error occurred while approving.');
        });
    });
});
</script>


@endsection