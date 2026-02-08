@extends('seller.layouts.app')

@section('panel_content')

    <div class="aiz-titlebar mt-2 mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Money Withdraw') }}</h1>
        </div>
      </div>
    </div>

<div class="container my-4">
  <div class="row g-3">
    <!-- Pending Balance Card -->
    <div class="col-md-4">
      <div class="card text-white bg-primary h-100 shadow">
        <div class="card-body">
          <h5 class="card-title">Pending Balance</h5>
            @php
                $pending_total = \DB::table('orders')
                    ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                    ->where('orders.seller_id', Auth::id())
                    ->where('orders.payment_status', 'paid')
                    ->where('order_details.delivery_status', '!=', 'delivered')
                    ->sum('order_details.price');

                // Get commission percentage
                $commission_percent = get_setting('vendor_commission');
                $admin_earning = ($commission_percent / 100) * $pending_total;
            @endphp

            <h3 class="card-text fw-bold">
                ${{ number_format($pending_total + $admin_earning, 2) }}
            </h3>

        </div>
        <div class="card-footer bg-transparent border-0 text-center">
          <button class="btn btn-light w-100" onclick="show_request_modal()">Send Withdraw Request</button>
        </div>
      </div>
    </div>

    <!-- Wallet Money Card -->
    <div class="col-md-4">
      <div class="card bg-success text-white h-100 shadow">
        <div class="card-body">
          <h5 class="card-title">Wallet Money</h5>
          <h3 class="card-text fw-bold">{{single_price(Auth::user()->balance)}}</h3>
        </div>
        <div class="card-footer bg-transparent border-0 text-center">
          <button class="btn btn-light w-100" onclick="show_make_wallet_recharge_modal(1)">Offline Recharge Wallet</button>
        </div>
      </div>
    </div>

    <!-- Guarantee Money Card -->
    <div class="col-md-4">
      <div class="card bg-warning text-dark h-100 shadow">
        <div class="card-body">
          <h5 class="card-title">Guarantee Money</h5>
          <h3 class="card-text fw-bold">${{ number_format($shop->guarantee_money ?? 0, 2) }}</h3>
        </div>
        <div class="card-footer bg-transparent border-0 text-center">
          <button class="btn btn-dark w-100" onclick="show_make_wallet_recharge_modal(2)">Guarantee Recharge</button>
        </div>
      </div>
    </div>
  </div>
</div>
<input type="hidden" name="type" value="1">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Withdraw Request history')}}</h5>
        </div>
          <div class="card-body">
              <table class="table aiz-table mb-0">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>{{ translate('Date') }}</th>
                          <th>{{ translate('Amount')}}</th>
                          <th data-breakpoints="lg">{{ translate('Status')}}</th>
                          <th data-breakpoints="lg" width="60%">{{ translate('Message')}}</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach ($seller_withdraw_requests as $key => $seller_withdraw_request)
                          <tr>
                              <td>{{ $key+1 }}</td>
                              <td>{{ date('d-m-Y', strtotime($seller_withdraw_request->created_at)) }}</td>
                              <td>{{ single_price($seller_withdraw_request->amount) }}</td>
                              <td>
                                    @if (is_null($seller_withdraw_request->payment_decision))
                                        <span class="badge badge-inline badge-info">{{ translate('Pending') }}</span>
                                    @else
                                        @php
                                            $statusClass = $seller_withdraw_request->payment_decision === 'paid' ? 'badge-success' : 'badge-danger';
                                        @endphp
                                        <span class="badge badge-inline {{ $statusClass }}">
                                            {{ translate(ucfirst($seller_withdraw_request->payment_decision)) }}
                                        </span>
                                    @endif
                                </td>
                              <td>
                                  {{ $seller_withdraw_request->message }}
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
              <div class="aiz-pagination">
                  {{ $seller_withdraw_requests->links() }}
              </div>
          </div>
    </div>
@endsection

@section('modal')
<!-- Modal -->
<div class="modal fade" id="offline_wallet_recharge_modal" tabindex="-1" role="dialog" aria-labelledby="offlineWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="offlineWalletModalLabel">Recharge</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <form action="{{route('wallet_recharge.make_payment')}}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="1" id="recharge_type_input">

        <div class="modal-body gry-bg px-3 pt-3 mx-auto">
          <div class="row align-items-center gutters-5">

            <!-- Payment Option Radios -->
            <div class="col-6 col-md-4">
              <label class="aiz-megabox d-block mb-3">
                <input value="Usdt-TRC20" type="radio" name="payment_option" onchange="toggleManualPaymentData(16, 'Usdt-TRC20')" data-id="16" checked>
                <span class="d-block p-3 aiz-megabox-elem">
                  <img src="https://sellerstorebay.com/public/uploads/all/rNmgTLlb9J0MPXujXov09wsYJWh6SnmWvRjugXBc.jpg" class="img-fluid mb-2">
                  <span class="d-block text-center">
                    <span class="d-block fw-600 fs-15">Usdt-TRC20</span>
                  </span>
                </span>
              </label>
            </div>

            <div class="col-6 col-md-4">
              <label class="aiz-megabox d-block mb-3">
                <input value="Bank" type="radio" name="payment_option" onchange="toggleManualPaymentData(18, 'Bank')" data-id="18">
                <span class="d-block p-3 aiz-megabox-elem">
                  <img src="https://sellerstorebay.com/public/uploads/all/01j5NMJacSp2F0sZUq4ujhyzbkO6hITmTPji8TFD.jpg" class="img-fluid mb-2">
                  <span class="d-block text-center">
                    <span class="d-block fw-600 fs-15">Bank</span>
                  </span>
                </span>
              </label>
            </div>

          </div>

          <div id="manual_payment_data">
            <div class="card mb-3 p-3">
              <div class="d-flow" id="manual_payment_description">
                <div id="description_16"><h6>TS4cD4TkKXXXXXXXXXXXXXXXXXXXXXXXXXXXXX21321321</h6></div>
                <button type="button" class="btn btn-sm btn-primary" onclick="copyToClipboard('manual_payment_info_16')">Copy</button>
              </div>
            </div>

            <div class="card mb-3 p-3">
              <div class="form-group row mt-3">
                <label class="col-md-3 col-form-label">Amount <span class="text-danger">*</span></label>
                <div class="col-md-9">
                  <input type="number" id="amount-anchor" lang="en" class="form-control mb-3" min="0" step="0.01" name="amount" placeholder="Amount" required>
                </div>
              </div>

              <div class="form-group row transaction_photo">
                    <label class="col-md-3 col-form-label">Photo <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                    </div>
                </div>

            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary transition-3d-hover">Confirm</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>

    </div>
  </div>
</div>

    <div class="modal fade" id="request_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Send A Withdraw Request') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                @if (Auth::user()->balance > 5) 
                    <form class="" action="{{ route('seller.money_withdraw_request.store') }}" method="post">
                        @csrf
                        <div class="modal-body gry-bg px-3 pt-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>{{ translate('Amount')}} <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-md-9">
                                    <input type="number" lang="en" class="form-control mb-3" name="amount" min="{{ get_setting('minimum_seller_amount_withdraw') }}" max="{{ Auth::user()->balance }}" placeholder="{{ translate('Amount') }}" required>
                                </div>
                            </div>
                            <div class="row" style="">

                                <div class="col-md-3">
                                    <label>Opera Type</label>
                                </div>
                                <div class="col-md-9">
                                    <select name="type" class="form-control mb-3">
                                        <option value="1">User Balance</option>
                                        <option value="2">Guarantee</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="">

                                <div class="col-md-3">
                                    <label>Withdraw Type</label>
                                </div>
                                <div class="col-md-9">
                                    <select name="w_type" class="form-control mb-3" id="p">
                                        <option value="2">USDT</option>
                                        <option value="2">Bank</option>
                                        

                                    </select>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label>{{ translate('Message')}} <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-md-9">
                                    <textarea name="message" rows="8" class="form-control mb-3"></textarea>
                                </div>
                            </div>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-sm btn-primary">{{translate('Send')}}</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="p-5 heading-3">
                            {{ translate('You do not have enough balance to send withdraw request') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function show_request_modal(){
            $('#request_modal').modal('show');
        }

        function show_message_modal(id){
            $.post('{{ route('withdraw_request.message_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#message_modal .modal-content').html(data);
                $('#message_modal').modal('show', {backdrop: 'static'});
            });
        }
        function show_make_wallet_recharge_modal(mode) {
            const modal = $('#offline_wallet_recharge_modal');
            
            // Set hidden input value
            modal.find('input[name="type"]').val(mode);

            // Change modal title
            const title = mode === 1 ? 'Offline Recharge Wallet' : 'Guarantee Recharge';
            modal.find('.modal-title').text(title);

            // Show modal
            modal.modal('show');
        }

        // Copy to clipboard function
        function copyToClipboard(elementId) {
            var text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(function() {
                console.log('Copied to clipboard');
                alert('Copied to clipboard');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        // Initial call for default checked payment option
        $(document).ready(function() {
            const checkedRadio = $('input[name="payment_option"]:checked');
            if (checkedRadio.length) {
                toggleManualPaymentData(checkedRadio.data('id'), checkedRadio.val());
            }
        });
        function toggleManualPaymentData(id, paymentOption) {
        }
        // Your existing toggleManualPaymentData function goes here...

    </script>
@endsection
