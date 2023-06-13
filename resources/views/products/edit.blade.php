@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
    </div>
    <form id="create_form" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data"
        class="needs-validation" novalidate>
        @method('PUT')
        @csrf
        <section>
            <div class="row">
                <div class="col-md-6">
                    <!--                    Product-->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Product</h6>
                        </div>
                        <div class="card-body border">
                            <div class="form-group">
                                <label for="product_name">Product Name</label>
                                <input type="text" name="title" id="product_name" required
                                    value="{{ old('title', $product->title) }}" placeholder="Product Name"
                                    class="form-control">
                                @if ($errors->has('title'))
                                    @error('title')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                @else
                                    <div class="invalid-feedback">
                                        Please enter a product name.
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="product_sku">Product SKU</label>
                                <input type="text" name="sku" id="product_sku" required
                                    value="{{ old('sku', $product->sku) }}" placeholder="Product Name" class="form-control">
                                @if ($errors->has('sku'))
                                    @error('sku')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                @else
                                    <div class="invalid-feedback">
                                        Please enter a sku.
                                    </div>
                                @endif
                            </div>
                            <div class="form-group mb-0">
                                <label for="product_description">Description</label>
                                <textarea name="description" id="product_description" required rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <!--                    Media-->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                        </div>
                        <div class="card-body border">
                            {{-- <div id="file-upload" class="needsclick dropzone dz-clickable" > --}}
                            {{-- <div class="dz-default dz-message"><span>Drop files here to upload</span></div> --}}
                            <div class="needsclick dropzone" id="document-dropzone">

                            </div>
                            <br>
                            @foreach ($product->product_images as $product_image)
                                <img class="img-thumbnail" data-dz-thumbnail="" alt="profile-pic-male.jpg"
                                    src="{{ env('APP_URL') }}/storage/tmp/uploads/{{ $product_image->file_path }}"
                                    width="150" />
                            @endforeach
                            {{-- </div> --}}
                        </div>

                    </div>
                </div>
                <!--                Variants-->
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                        </div>
                        <div class="card-body pb-0" id="variant-sections">
                            @foreach ($selectedVariants as $key => $val)
                                {{-- {{dd($val->id)}} --}}
                                <div class="row grid_area">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Option</label>
                                            <select id="select2-option-{{ $key }}" data-index="{{ $key }}"
                                                name="product_variant[{{ $key }}][option]"
                                                class="form-control custom-select select2 select2-option">
                                                <option value="1" {{ $val->variant_id == 1 ? 'selected' : '' }}>
                                                    Color
                                                </option>
                                                <option value="2" {{ $val->variant_id == 2 ? 'selected' : '' }}>
                                                    Size
                                                </option>
                                                <option value="6" {{ $val->variant_id == 6 ? 'selected' : '' }}>
                                                    Style
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="d-flex justify-content-between">
                                                <span>Value</span>
                                                <a href="#" class="remove-btn" data-index="{{ $key }}"
                                                    onclick="removeVariant(event, this);">Remove</a>
                                            </label>
                                            <select id="select2-value-{{ $key }}"
                                                data-index="{{ $key }}"
                                                name="product_variant[{{ $key }}][value][]"
                                                class="select2 select2-value form-control custom-select"
                                                multiple="multiple">
                                                @php
                                                    $productVariants = \App\Models\ProductVariant::where('variant_id', $val->variant_id)
                                                        ->groupBy('variant_id')
                                                        ->groupBy('variant')
                                                        ->get();
                                                @endphp
                                                @foreach ($productVariants as $productVariant)
                                                    @php
                                                        $variant_id = $productVariant->variant_id;
                                                        $variant = $productVariant->variant;
                                                        $selectedVals = \App\Models\ProductVariant::where('product_id', $product->id)
                                                            ->where('variant_id', $variant_id)
                                                            ->where('variant', $variant)
                                                            ->first();
                                                    @endphp
                                                    <option value="{{ $productVariant->variant }}"
                                                        {{ isset($selectedVals) ? 'selected' : '' }}>
                                                        {{ $variant }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer bg-white border-top-0" id="add-btn">
                            <div class="row d-flex justify-content-center">
                                <button class="btn btn-primary add-btn" onclick="addVariant(event);">
                                    Add another option
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow">
                        <div class="card-header text-uppercase">Preview</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="text-center">
                                            <th width="33%">Variant</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variant-previews">
                                        @foreach ($product->product_variant_prices as $key => $val)
                                            <tr>
                                                <th>
                                                    <input type="hidden"
                                                        name="product_preview[{{ $key }}][variant]"
                                                        value="{{ optional($val->product_variant_color)->variant }}/{{ optional($val->product_variant_size)->variant }}/{{ optional($val->product_variant_style)->variant }}">
                                                    <span
                                                        class="font-weight-bold">{{ optional($val->product_variant_color)->variant }}/{{ optional($val->product_variant_size)->variant }}/{{ optional($val->product_variant_style)->variant }}</span>
                                                </th>
                                                <td>
                                                    <input type="text" class="form-control" value="{{ $val->price }}"
                                                        name="product_preview[{{ $key }}][price]" required>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        value="{{ $val->stock }}"
                                                        name="product_preview[{{ $key }}][stock]">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-lg btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
        </section>
    </form>
@endsection

@push('page_js')
    {{-- <script type="text/javascript" src="{{ asset('js/product.js') }}"></script> --}}
    <script type="text/javascript">
        var uploadedDocumentMap = {}
        Dropzone.options.documentDropzone = {
            url: "{{ route('uploads') }}",
            maxFilesize: 2, // MB
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(file, response) {
                $('#create_form').append('<input type="hidden" name="document[]" value="' + response.name + '">')
                uploadedDocumentMap[file.name] = response.name
            },
            removedfile: function(file) {
                file.previewElement.remove()
                var name = ''
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name
                } else {
                    name = uploadedDocumentMap[file.name]
                }
                $('#create_form').find('input[name="document[]"][value="' + name + '"]').remove()
            },
            init: function() {

                @if (isset($product) && $product->product_images)
                    var files =
                        {!! json_encode($product->product_images) !!}
                    for (var i in files) {
                        var file = files[i]

                        // this.options.addedfile.call(this, file)
                        // file.previewElement.classList.add('dz-complete')
                        // $('#create_form').append('<input type="hidden" name="document[]" value="' + file.file_name +
                        //     '">')
                    }
                @endif
            }
        }



        var currentIndex = {{ $product->product_variants->count() - 1 }};

        var indexs = [];

        $(document).ready(function() {

            //addVariantTemplate();
            $("#file-upload").dropzone({
                url: "{{ route('file-upload') }}",
                method: "post",
                addRemoveLinks: true,
                success: function(file, response) {
                    //
                },
                error: function(file, response) {
                    //
                }
            });

            $(".select2-value").select2({
                tags: true,
                // tokenSeparators: [',', ' ']
            })

            $(".select2-value").on('change', function() {

                updateVariantPreview();
            });

        });


        function addVariant(event) {
            event.preventDefault();
            addVariantTemplate();
        }

        function getCombination(arr, pre) {

            pre = pre || '';

            if (!arr.length) {
                return pre;
            }

            return arr[0].reduce(function(ans, value) {
                return ans.concat(getCombination(arr.slice(1), pre + value + '/'));
            }, []);
        }

        function updateVariantPreview() {

            var valueArray = [];

            $(".select2-value").each(function() {
                valueArray.push($(this).val());
            });

            var variantPreviewArray = getCombination(valueArray);


            var tableBody = '';

            $(variantPreviewArray).each(function(index, element) {
                tableBody += `<tr>
                        <th>
                                        <input type="hidden" name="product_preview[${index}][variant]" value="${element}">
                                        <span class="font-weight-bold">${element}</span>
                                    </th>
                        <td>
                                        <input type="text" class="form-control" value="0" name="product_preview[${index}][price]" required>
                                    </td>
                        <td>
                                        <input type="text" class="form-control" value="0" name="product_preview[${index}][stock]">
                                    </td>
                      </tr>`;
            });

            $("#variant-previews").empty().append(tableBody);
        }

        function addVariantTemplate() {

            $("#variant-sections").append(`<div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Option</label>
                                        <select id="select2-option-${currentIndex}" data-index="${currentIndex}" name="product_variant[${currentIndex}][option]" class="form-control custom-select select2 select2-option">
                                            <option value="1">
                                                Color
                                            </option>
                                            <option value="2">
                                                Size
                                            </option>
                                            <option value="6">
                                                Style
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="d-flex justify-content-between">
                                            <span>Value</span>
                                            <a href="#" class="remove-btn" data-index="${currentIndex}" onclick="removeVariant(event, this);">Remove</a>
                                        </label>
                                        <select id="select2-value-${currentIndex}" data-index="${currentIndex}" name="product_variant[${currentIndex}][value][]" class="select2 select2-value form-control" multiple="multiple">
                                        </select>
                                    </div>
                                </div>
                            </div>`);


            $(`#select2-option-${currentIndex}`).select2({
                placeholder: "Select Option",
                theme: "bootstrap4"
            });

            $(`#select2-value-${currentIndex}`)
                .select2({
                    tags: true,
                    multiple: true,
                    placeholder: "Type tag name",
                    allowClear: true,
                    theme: "bootstrap4"

                })
                .on('change', function() {

                    updateVariantPreview();
                });

            indexs.push(currentIndex);

            currentIndex = (currentIndex + 1);

            if (indexs.length >= 3) {
                $("#add-btn").hide();
            } else {
                $("#add-btn").show();
            }
        }

        function removeVariant(event, element) {

            event.preventDefault();

            var jqElement = $(element);

            var position = indexs.indexOf(jqElement.data('index'))

            indexs.splice(position, 1)

            jqElement.parent().parent().parent().parent().remove();

            if (indexs.length >= 3) {
                $("#add-btn").hide();
            } else {
                $("#add-btn").show();
            }

            updateVariantPreview();
        }
    </script>
@endpush
