@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control" value="{{ request()->get('title') }}">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">                        
                        <option value="">--Select A Variant--</option>    
                        @foreach ($variants as $variant)                       
                            <optgroup label="{{$variant->title}}">                               
                                @foreach ($variant->product_variants as $pvVal)
                                <option style="font-weight:bold;color:black" value="{{$variant->title}}|{{$pvVal->id}}" {{ (request()->get('variant')==$variant->title."|".$pvVal->id)?'selected':'' }}>{{ ucwords($pvVal->variant) }}</option>                                    
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                            class="form-control" value="{{ request()->get('price_from') }}">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control" value="{{ request()->get('price_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control" value="{{ request()->get('date') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search">Search</i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Variant</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $index => $val)
                            <tr>
                                <td>{{ $index + $products->firstItem() }}</td>
                                <td>{{ $val->title }} <br> Created at : {{ $val->created_at }}</td>
                                <td>{{ substr($val->description, 0, 50) }}</td>
                                <td style="width:500px">
                                    <dl class="row mb-0 variant" style="height: 80px; overflow: hidden"
                                        id="variant{{ $index }}">
                                        @foreach ($val->product_variant_prices as $product_variant_price)
                                            @php
                                                //print_r($product_variant_price->product_variant_size)
                                            @endphp
                                            <dt class="col-sm-3 pb-0 text-nowrap">
                                                {{ strtoupper(optional($product_variant_price->product_variant_size)->variant) }}/
                                                {{ strtoupper(optional($product_variant_price->product_variant_color)->variant) }}/
                                                {{ ucwords(optional($product_variant_price->product_variant_style)->variant) }}
                                            </dt>
                                            <dd class="col-sm-9">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4 pb-0 text-nowrap">Price :
                                                        {{ number_format($product_variant_price->price, 2) }}</dt>
                                                    <dd class="col-sm-8 pb-0 text-nowrap">InStock :
                                                        {{ number_format($product_variant_price->stock, 2) }}</dd>
                                                </dl>
                                            </dd>
                                        @endforeach
                                    </dl>
                                    <button onclick="$('#variant{{ $index }}').toggleClass('h-auto')"
                                        class="btn btn-sm btn-link">Show
                                        more</button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.edit', $val->id) }}" class="btn btn-success">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No records found. </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row ">
                <div class="col-md-6">
                    <p class="text-sm text-gray-700 leading-5">
                        {!! __('Showing') !!}
                        <span class="font-medium">{{ $products->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $products->lastItem() }}</span>
                        {!! __('of') !!}
                        <span class="font-medium">{{ $products->total() }}</span>
                        {!! __('results') !!}
                    </p>
                </div>
                <div class="col-md-4">

                </div>
                <div class="col-md-2">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
