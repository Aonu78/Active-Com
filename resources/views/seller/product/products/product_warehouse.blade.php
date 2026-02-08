@extends('seller.layouts.app')

@section('panel_content')

    <div class="aiz-titlebar mt-2 mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Categories ') }} ({{$count_listed}})</h1>
        </div>
      </div>
    </div>

    <div class="row gutters-10 justify-content-center">
        @if (addon_is_activated('seller_subscription'))
            <div class="col-md-4 mx-auto mb-3" >
                <div class="bg-grad-1 text-white rounded-lg overflow-hidden">
                  <span class="size-30px rounded-circle mx-auto bg-soft-primary d-flex align-items-center justify-content-center mt-3">
                      <i class="las la-upload la-2x text-white"></i>
                  </span>
                  <div class="px-3 pt-3 pb-3">
                      <div class="h4 fw-700 text-center">{{ max(0, auth()->user()->shop->product_upload_limit - auth()->user()->products()->count()) }}</div>
                      <div class="opacity-50 text-center">{{  translate('Remaining Uploads') }}</div>
                  </div>
                </div>
            </div>
        @endif


        @if (addon_is_activated('seller_subscription'))
        @php
            $seller_package = \App\Models\SellerPackage::find(Auth::user()->shop->seller_package_id);
        @endphp
        <div class="col-md-4">
            <a href="{{ route('seller.seller_packages_list') }}" class="text-center bg-white shadow-sm hov-shadow-lg text-center d-block p-3 rounded">
                @if($seller_package != null)
                    <img src="{{ uploaded_asset($seller_package->logo) }}" height="44" class="mw-100 mx-auto">
                    <span class="d-block sub-title mb-2">{{ translate('Current Package')}}: {{ $seller_package->getTranslation('name') }}</span>
                @else
                    <i class="la la-frown-o mb-2 la-3x"></i>
                    <div class="d-block sub-title mb-2">{{ translate('No Package Found')}}</div>
                @endif
                <div class="btn btn-outline-primary py-1">{{ translate('Upgrade Package')}}</div>
            </a>
        </div>
        @endif

    </div>

    <div class="row">
        <div class="col-6">
            <div class="row gutters-10 justify-content-center">
                @foreach ($products as $product)
                    <div class="col-6 col-sm-4 mb-3">
                        <div class="card bg-white c-pointer product-card hov-container position-relative h-100">
                            
                            {{-- Stock Badge --}}
                            <span class="badge badge-success position-absolute top-0 start-0 m-1 fs-13">
                                In stock: {{ $product->stocks->sum('qty') }}
                            </span>

                            {{-- Image --}}
                            <img src="{{ uploaded_asset($product->thumbnail_img) }}"
                                class="card-img-top img-fit h-120px h-xl-180px h-xxl-210px mw-100 mx-auto"
                                alt="{{ $product->getTranslation('name') }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">

                            {{-- Product Details --}}
                            <div class="card-body p-2 p-xl-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="text-truncate fw-600 fs-14 mb-2">
                                        {{ $product->getTranslation('name') }}
                                    </div>
                                    <div><span>${{ $product->unit_price }}</span></div>
                                    <div><span>Profit: ${{ number_format($product->unit_price * ($commission_percentage / 100), 2) }}</span></div>
                                </div>

                                {{-- Action --}}
                                <div class="mt-2 text-center">
                                    <input type="checkbox" class="check-one d-none" name="id[]" value="{{ $product->id }}">
                                </div>
                            </div>

                            {{-- Overlay (optional hover effect) --}}
                            <div class="add-plus absolute-full rounded overflow-hidden hov-box"
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->getTranslation('name') }}"
                                data-product-price="{{ $product->unit_price }}"
                                onclick="toggleProductSelection({{ $product->id }}, '{{ $product->getTranslation('name') }}', '{{ $product->unit_price }}')">
                                <div class="absolute-full bg-dark opacity-50"></div>
                                <i class="las la-plus absolute-center la-4x text-white"></i>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-6 mb-3">
            <div class="">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="">
                            <div class="aiz-pos-cart-list mb-4 mt-3 c-scrollbar-light">
                                <ul class="list-group list-group-flush" id="product-selection">
                                    {{-- JS will append selected products here --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pos-footer mar-btm">
                    <div class="d-flex flex-column flex-md-row justify-content-between">
                        <div class="d-flex">
                            <button id="add-all-btn" type="button" class="btn btn-outline-info btn-block" onclick="addPost(1)">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                Add all to My Product
                            </button>
                        </div>
                        <div class="my-2 my-md-0">
                            <button id="add-selection-btn" type="button" class="btn btn-primary btn-block" onclick="addPost(0)">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                Add to My Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')
    <!-- Bulk Delete modal -->
    @include('modals.bulk_delete_modal')
@endsection

@section('script')
<script>
    let selectedProducts = {};

    // Called when user clicks on product "enlist" or "add" card button
    function toggleProductSelection(productId, name, price) {
        const listEl = document.getElementById('product-selection');
        
        if (selectedProducts[productId]) {
            // Remove from selection
            delete selectedProducts[productId];
            document.getElementById('selected-product-' + productId).remove();
        } else {
            // Add to selection
            selectedProducts[productId] = { name, price };
            
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.id = 'selected-product-' + productId;
            li.innerHTML = `
                <div>
                    <div class="fw-600">${name}</div>
                    <small>${price}</small>
                </div>
                <button class="btn btn-sm btn-danger" onclick="toggleProductSelection(${productId}, '${name}', '${price}')">
                    <i class="las la-times"></i>
                </button>
            `;
            listEl.appendChild(li);
        }
    }

    function addPost(type) {
        let productIds = [];

        if (type === 0) {
            // Selected
            productIds = Object.keys(selectedProducts);
        } else {
            // Add all not yet listed products (this assumes backend knows what to enlist)
            productIds = 'ALL';
        }

        if (productIds.length === 0) {
            alert("Please select at least one product.");
            return;
        }

        // Show spinner
        const btn = type === 0 ? document.getElementById('add-selection-btn') : document.getElementById('add-all-btn');
        btn.querySelector('.spinner-border').classList.remove('d-none');

        fetch(`{{ route('seller.products.enlist.product') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_ids: productIds })
        }).then(res => res.json())
        .then(data => {
            btn.querySelector('.spinner-border').classList.add('d-none');
            if (data.status === 'success') {
                AIZ.plugins.notify('success', '{{ translate('Products Enlisted successfully') }}');
                location.reload();
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something Went Wrong') }}');
            }
        });
    }
</script>

    <script type="text/javascript">

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;                        
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;                       
                });
            }
          
        });

        function update_featured(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('seller.products.featured') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Featured products updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    location.reload();
                }
            });
        }

        function update_published(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('seller.products.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Published products updated successfully') }}');
                }
                else if(data == 2){
                    AIZ.plugins.notify('danger', '{{ translate('Please upgrade your package.') }}');
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    location.reload();
                }
            });
        }

        function bulk_delete() {
            var data = new FormData($('#sort_products')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('seller.products.bulk-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
        function enlist_to_user_product(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: `/seller/unlist-product/${id}`,
                type: 'POST',
                data: {
                    id: id
                },
                success: function (response) {
                    // if (response == 1) {
                        // Change the button text to "Listed" and disable the button
                        $('#enlist-' + id).text('Unlisted').prop('disabled', true);
                    // }
                }
            });
        }

    </script>
@endsection
