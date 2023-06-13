<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected $products;

    public function __construct(Product $products)
    {
        $this->products = $products;
    }

    public function index(Request $request)
    {
        $query = $this->products->orderBy('created_at', 'desc');
        $query->with('product_variant_prices');
        # Start Filter Section   
        if (($request->variant != null) || (($request->price_from != null) && ($request->price_to != null))) {
            $query->whereHas('product_variant_prices', function ($query) use ($request) {
                if (($request->price_from != null) && ($request->price_to != null)) {
                    $query->whereBetween('price', [$request->price_from, $request->price_to]);
                }
                if (($request->variant != null)) {
                    $variant = explode("|", $request->variant);
                    $variantType = $variant[0];
                    $variant = $variant[1];
                    if ($variantType == 'Color') {
                        $query->where('product_variant_one', $variant);
                    } elseif ($variantType == 'Size') {
                        $query->orWhere('product_variant_two', $variant);
                    } elseif ($variantType == 'Style') {
                        $query->orWhere('product_variant_three', $variant);
                    }
                }
            });
        }
        if ($request->title != null) {
            $query->where('title', 'LIKE', '%' . $request->title . '%');
        }
        if ($request->date != null) {
            $query->whereDate('created_at', $request->date);
        }
        $products = $query->paginate(10);

        $variants = Variant::with(['product_variants' => function ($ver) {
            $ver->groupBy('variant');
        }])->get();
        return view('products.index', compact('products', 'variants'));
    }


    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }


    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'title' => [
                        'required', 'min:3', 'max:1000',
                        Rule::unique('products')->where(function ($query) use ($request) {
                            return $query->where('title', $request->product_name)
                                ->where('sku', $request->sku);
                        })
                    ],
                    'sku'              => 'required',
                    'product_variant'  => 'required',
                ],
                [
                    'title.required' => 'product_name field is required.',
                    'title.unique' => 'This poduct has already been taken',
                    'sku.required' => 'sku field is required.',
                    'product_variant.required' => 'product_variant field is required.',
                ]
            );

            $productData = array(
                'title' => $request->title,
                'sku' => $request->sku,
                'description' => $request->description
            );

            $product = $this->products->create($productData);

            # Product Images
            if (count($request->input('document', [])) > 0) {
                foreach ($request->input('document', []) as $fileName) {
                    if ($fileName == null) continue;
                    $product->product_images()->create([
                        'file_path' => $fileName
                    ]);
                }
            }

            #product_variants

            if (count($request->product_variant) > 0) {
                $variantId = null;
                foreach ($request->product_variant as $variant) {
                    if (isset($variant['value'])) {
                        if ($variant['value'] == null) continue;
                        $variantId = $variant['option'];
                        foreach ($variant['value'] as $val) {
                            $product->product_variants()->create([
                                'variant' => $val,
                                'variant_id' => $variantId
                            ]);
                        }
                    }
                }
            }

            #product_variant_prices
            if (isset($request->product_preview) && count($request->product_preview) > 0) {
                foreach ($request->product_preview as $pVal) {
                    if ($pVal == null) continue;
                    $priceVariants = explode('/', $pVal['variant']);
                    if (isset($priceVariants[0])) {
                        $priceOne = ProductVariant::where('variant', $priceVariants[0])->latest()->first();
                    }
                    if (isset($priceVariants[1])) {
                        $priceTwo = ProductVariant::where('variant', $priceVariants[1])->latest()->first();
                    }
                    if (isset($priceVariants[2])) {
                        $priceThree = ProductVariant::where('variant', $priceVariants[2])->latest()->first();
                    }
                    $product->product_variant_prices()->create([
                        'product_variant_one' => isset($priceOne) ? $priceOne->id : null,
                        'product_variant_two' => isset($priceTwo) ? $priceTwo->id : null,
                        'product_variant_three' => isset($priceThree) ? $priceThree->id : null,
                        'price' => $pVal['price'],
                        'stock' => $pVal['stock']
                    ]);
                }
            }
            Session::flash('success', "Data inserted successfully!");
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        return redirect()->back();
    }
    public function uploads(Request $request)
    {
        $path = storage_path('tmp/uploads');

        (!file_exists($path)) && mkdir($path, 0777, true);

        $file = $request->file('file');
        // dd($file);
        $name = uniqid() . '_' . trim($file->getClientOriginalName());
        $file->move($path, $name);

        return response()->json([
            'name'          => $name,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }


    public function show($product)
    {
    }


    public function edit(Product $product)
    {
        $variants = Variant::all();
        $selectedVariants = ProductVariant::where('product_id', $product->id)->groupBy('variant_id')->get();

        return view('products.edit', compact('variants', 'product', 'selectedVariants'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            $request->validate(
                [
                    'title' => [
                        'required', 'min:3', 'max:1000',
                        Rule::unique('products')->ignore($product->id, 'id')->where(function ($query) use ($request) {
                            return $query->where('title', $request->title)
                                ->where('sku', $request->product_sku);
                        })
                    ],
                    'sku'              => 'required',
                    'product_variant'  => 'required',

                ],
                [
                    'title.required' => 'title field is required.',
                    'title.unique' => 'This poduct has already been taken',
                    'sku.required' => 'sku field is required.',
                    'product_variant.required' => 'product_variant field is required.',
                ]
            );

            $product->title = $request->title;
            $product->sku = $request->sku;
            $product->description = $request->description;
            $product->save();

            if (count($request->input('document', [])) > 0) {
                #remove existing file if new
                foreach ($product->product_images as $existingFile) {
                    $path = storage_path('tmp/uploads/');
                    $filePath = $path . $existingFile->file_path;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                $product->product_images()->delete();

                foreach ($request->input('document', []) as $fileName) {
                    //your file to be uploaded
                    $product->product_images()->create([
                        'file_path' => $fileName
                    ]);
                }
            }

            #product_variants
            $variantId = null;
            if (count($request->product_variant) > 0) {
                $product->product_variants()->delete();
                foreach ($request->product_variant as $variant) {
                    if (isset($variant['value'])) {
                        if ($variant['value'] == null) continue;
                        $variantId = $variant['option'];
                        foreach ($variant['value'] as $val) {
                            $product->product_variants()->create([
                                'variant' => $val,
                                'variant_id' => $variantId
                            ]);
                        }
                    }
                }
            }

            #product_variant_prices
            if (isset($request->product_preview) && count($request->product_preview) > 0) {
                $product->product_variant_prices()->delete();
                foreach ($request->product_preview as $pVal) {
                    if ($pVal == null) continue;
                    $priceVariants = explode('/', $pVal['variant']);
                    if (isset($priceVariants[0])) {
                        $priceOne = ProductVariant::where('variant', $priceVariants[0])->latest()->first();
                    }
                    if (isset($priceVariants[1])) {
                        $priceTwo = ProductVariant::where('variant', $priceVariants[1])->latest()->first();
                    }
                    if (isset($priceVariants[2])) {
                        $priceThree = ProductVariant::where('variant', $priceVariants[2])->latest()->first();
                    }
                    $product->product_variant_prices()->create([
                        'product_variant_one' => isset($priceOne) ? $priceOne->id : null,
                        'product_variant_two' => isset($priceTwo) ? $priceTwo->id : null,
                        'product_variant_three' => isset($priceThree) ? $priceThree->id : null,
                        'price' => $pVal['price'],
                        'stock' => $pVal['stock']
                    ]);
                }
            }

            Session::flash('success', "Data updated successfully!");
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        return redirect()->back();
    }


    public function destroy(Product $product)
    {
    }
}
