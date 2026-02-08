@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Create Manual Order') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('manual_order.store', $id) }}" method="POST" enctype="multipart/form-data" id="manual-order-form">
            @csrf
            <input type="hidden" id="percentage_commission" name="percentage_commission" value="{{ get_setting('vendor_commission') }}">
            <div class="form-group row">
                <label class="col-md-3 col-from-label">{{ translate('Customer') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="user_id" data-live-search="true" required>
                        <option value="">{{ translate('Select Customer') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            {{-- <div class="form-group row">
                <label class="col-md-3 col-from-label">{{ translate('Shipping Address') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="shipping_address_id" data-live-search="true" required>
                        <option value="">{{ translate('Select Address') }}</option>
                        @foreach($shipping_addresses as $address)
                            <option value="{{ $address->id }}" data-user="{{ $address->user_id }}">
                                {{ $address->address }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div> --}}
            
            <div class="form-group row">
                <label class="col-md-3 col-from-label">{{ translate('Products') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <div class="product-list">
                        <div class="row gutters-5 mb-3 product-item">
                            <div class="col-md-10">
                                <select class="form-control aiz-selectpicker product-select" name="products[0][id]" data-live-search="true" required>
                                    <option value="">{{ translate('Select Product') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                            data-price="{{ $product->unit_price }}" 
                                            data-image="{{ uploaded_asset($product->thumbnail_img) }}"
                                            data-content="<div class='d-flex align-items-center'>
                                                <img src='{{ uploaded_asset($product->thumbnail_img) }}' class='mr-2' width='30' height='30'>
                                                <span>{{ $product->getTranslation('name') }}</span>
                                                <span class='ml-auto'>{{ single_price($product->unit_price) }}</span>
                                            </div>">
                                            {{ $product->getTranslation('name') }} - {{ single_price($product->unit_price) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="selected-product-info mt-2 d-flex align-items-center" style="display: none!important;">
                                    <img src="" class="mr-2 product-thumb" width="40" height="40">
                                    <div>
                                        <div class="product-name font-weight-bold"></div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="product-price text-primary"></div>
                                        <span class="product-profit text-success"></span>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <input type="number" class="form-control" name="products[0][quantity]" placeholder="{{ translate('Quantity') }}" value="1" min="1" required>
                            </div>
                            <div class="">
                                <input type="hidden" class="form-control" name="products[0][variation]" placeholder="{{ translate('Variation') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-icon btn-sm btn-circle btn-light remove-product" disabled>
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-soft-primary btn-sm" id="add-product">
                        <i class="las la-plus"></i> {{ translate('Add Product') }}
                    </button>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-from-label">{{ translate('Payment Method') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="payment_option" required>
                        <option value="">{{ translate('Select Payment Method') }}</option>
                        <option value="cash_on_delivery">{{ translate('Cash on Delivery') }}</option>
                        <option value="wallet">{{ translate('Wallet Payment') }}</option>
                        @if(get_setting('paypal_payment') == 1)
                            <option value="paypal">{{ translate('Paypal') }}</option>
                        @endif
                        @if(get_setting('stripe_payment') == 1)
                            <option value="stripe">{{ translate('Stripe') }}</option>
                        @endif
                        @if(get_setting('manual_payment') == 1)
                            @foreach(\App\Models\ManualPaymentMethod::all() as $method)
                                <option value="{{ $method->heading }}">{{ $method->heading }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="shipping_name" value="{{ old('shipping_name', $address->name ?? '') }}" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Email') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="email" class="form-control" name="shipping_email" value="{{ old('shipping_email', $address->email ?? '') }}" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Address') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <textarea class="form-control" name="shipping_address" rows="2" required>{{ old('shipping_address', $address->address ?? '') }}</textarea>
                </div>
            </div>

            <!-- Country Select -->
            {{-- Country Select --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Country') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="shipping_country" id="country" data-live-search="true" required>
                        <option value="">{{ translate('Select Country') }}</option>
                        @foreach (\App\Models\Country::where('status', 1)->get() as $country)
                            <option value="{{ $country->id }}" {{ old('shipping_country') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- State Select --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('State') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="shipping_state" id="state" data-live-search="true" required {{ old('shipping_country') ? '' : 'disabled' }}>
                        <option value="">{{ translate('Select State') }}</option>
                        @if (old('shipping_country'))
                            @foreach (\App\Models\State::where('country_id', old('shipping_country'))->get() as $state)
                                <option value="{{ $state->id }}" {{ old('shipping_state') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- City Select --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('City') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="shipping_city" id="city" data-live-search="true" required {{ old('shipping_state') ? '' : 'disabled' }}>
                        <option value="">{{ translate('Select City') }}</option>
                        @if (old('shipping_state'))
                            @foreach (\App\Models\City::where('state_id', old('shipping_state'))->get() as $city)
                                <option value="{{ $city->id }}" {{ old('shipping_city') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Postal Code') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="shipping_postal_code" value="{{ old('shipping_postal_code', $address->postal_code ?? '') }}" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="shipping_phone" value="{{ old('shipping_phone', $address->phone ?? '') }}" required>
                </div>
            </div>
            
            <div class="form-group row" id="manual_payment_info" style="display: none;">
                <label class="col-md-3 col-from-label">{{ translate('Transaction Details') }}</label>
                <div class="col-md-9">
                    <input type="text" class="form-control mb-2" name="trx_id" placeholder="{{ translate('Transaction ID') }}">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="photo" class="selected-files">
                    </div>
                    <div class="file-preview"></div>
                </div>
            </div>
            
            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{ translate('Create Order') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Filter addresses based on selected user
        $('[name="user_id"]').on('change', function() {
            var userId = $(this).val();
            $('[name="shipping_address_id"] option').show();
            if(userId) {
                $('[name="shipping_address_id"] option').not('[data-user="'+userId+'"]').hide();
                $('[name="shipping_address_id"]').val('').selectpicker('refresh');
            }
        });
        
        // Show/hide manual payment info
        $('[name="payment_option"]').on('change', function() {
            if($(this).val() && $(this).val() != 'cash_on_delivery' && $(this).val() != 'wallet') {
                $('#manual_payment_info').show();
            } else {
                $('#manual_payment_info').hide();
            }
        });
        $(document).ready(function() {
    // Load states when country changes
    $('#country').on('change', function() {
        var countryId = $(this).val();
        var stateSelect = $('#state');
        var citySelect = $('#city');
        
        stateSelect.html('<option value="">{{ translate("Select State") }}</option>');
        citySelect.html('<option value="">{{ translate("Select City") }}</option>');
        
        if(countryId) {
            stateSelect.prop('disabled', false);
            
            $.ajax({
                url: "{{ route('get-states') }}",
                type: "POST",
                data: {
                    country_id: countryId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success && response.states.length > 0) {
                        $.each(response.states, function(key, state) {
                            stateSelect.append('<option value="'+state.id+'">'+state.name+'</option>');
                        });
                    }
                    stateSelect.selectpicker('refresh');
                },
                error: function(xhr) {
                    AIZ.plugins.notify('danger', 'Error loading states');
                }
            });
        } else {
            stateSelect.prop('disabled', true);
            citySelect.prop('disabled', true);
            stateSelect.selectpicker('refresh');
            citySelect.selectpicker('refresh');
        }
    });

    // Load cities when state changes
    $('#state').on('change', function() {
        var stateId = $(this).val();
        var citySelect = $('#city');
        
        citySelect.html('<option value="">{{ translate("Select City") }}</option>');
        
        if(stateId) {
            citySelect.prop('disabled', false);
            
            $.ajax({
                url: "{{ route('get-cities') }}",
                type: "POST",
                data: {
                    state_id: stateId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success && response.cities.length > 0) {
                        $.each(response.cities, function(key, city) {
                            citySelect.append('<option value="'+city.id+'">'+city.name+'</option>');
                        });
                    }
                    citySelect.selectpicker('refresh');
                },
                error: function(xhr) {
                    AIZ.plugins.notify('danger', 'Error loading cities');
                }
            });
        } else {
            citySelect.prop('disabled', true);
            citySelect.selectpicker('refresh');
        }
    });
});
        // Add product row
        // Initialize product index counter
// Initialize product index counter
// Initialize product index counter
var productIndex = 1;

// Function to update product info display
function updateProductInfo(selectElement) {
    var container = selectElement.closest('.col-md-10').find('.selected-product-info');
    var selectedOption = selectElement.find('option:selected');
    var commission = parseFloat($('#percentage_commission').val());

    if(selectedOption.val()) {
        var price = parseFloat(selectedOption.data('price'));
        var image = selectedOption.data('image');
        var profit = (price * commission / 100).toFixed(2);

        container.show();
        
        container.find('.product-thumb').attr('src', image);
        container.find('.product-name').text(selectedOption.text().split(' - ')[0]);
        container.find('.product-price').text('Price: $' + price.toFixed(2));
        container.find('.product-profit').text('Profit: $' + profit);

        // container.find('.product-thumb').attr('src', selectedOption.data('image'));
        // container.find('.product-name').text(selectedOption.text().split(' - ')[0]);
        // container.find('.product-price').text(selectedOption.text().split(' - ')[1]);
    } else {
        container.hide();
    }
}

// Add product row
$('#add-product').click(function() {
    // Get the original product item (before Bootstrap Select initialization)
    var originalRow = $('.product-item:first');
    
    // Clone just the original HTML structure (not the initialized select)
    var newRow = $(originalRow[0].outerHTML.replace(/products\[0\]/g, 'products[' + productIndex + ']'));
    
    // Clear values
    newRow.find('select').val('');
    newRow.find('input').val('');
    newRow.find('input[name*="quantity"]').val('1');
    newRow.find('.remove-product').prop('disabled', false);
    newRow.find('.selected-product-info').hide();
    
    // Remove any existing Bootstrap Select initialization
    newRow.find('.bootstrap-select').replaceWith(function() {
        return $(this).find('select');
    });
    
    // Add to DOM
    $('.product-list').append(newRow);
    
    // Initialize Bootstrap Select on the new select element
    var newSelect = newRow.find('select');
    newSelect.selectpicker();
    
    // Add change event for the new select
    newSelect.on('changed.bs.select', function(e) {
        updateProductInfo($(this));
    });
    
    productIndex++;
});

// Remove product row
$(document).on('click', '.remove-product', function() {
    if($('.product-item').length > 1) {
        $(this).closest('.product-item').remove();
    }
});

// Initialize for first product select
$('.product-select').on('changed.bs.select', function(e) {
    updateProductInfo($(this));
});

// Update on page load if any product is selected
$('.product-select').each(function() {
    if($(this).val()) {
        updateProductInfo($(this));
    }
});

});
</script>
@endsection