<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;

use App\Product;
use App\ProductCategory;
use App\ProductType;
use App\ProductRate;
use App\ProductManufacturer;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = $this->getCategoryDataForSelectOption();
        $types = ProductType::select('type_code', 'name')->orderby('name')->get()->toArray();

        return view('product.create', compact('categories', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        DB::transaction(function()
        {

         // Create Product
          $product = Product::create($this->getProductAttributes());

          // Get manufacturer_id and unset manufacturer
          if (request()->manufacturer)
          {
            $manufacturer = ProductManufacturer::firstOrCreate(['name' => request()->manufacturer]);
            $product->manufacturer()->associate($manufacturer);
            $product->save();
          }

          // create rates
          foreach (request()->rates as $rate)
          {
            if (!empty($rate['time']))
            {
              $product_rate[] = new ProductRate ([
                'hours' => $rate['time'] * $rate['period'],
                'rate' => $rate['rate']
              ]);
            }
            $product->rates()->saveMany($product_rate);
          }

          // create product_category map
          $product->categories()->attach(request()->input('categories'));

        session()->flash('status', "Product: $product->name was created successfully!");
        });

        return redirect('/product');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function getCategoryDataForSelectOption()
    {
      $categories = ProductCategory::where('parent_id', null)->with(['children' => function ($query) {
        $query->select('name', 'id', 'parent_id');
      }])->select('name', 'id', 'parent_id')->get();

      return $categories;
    }
    //
    // public function validateCategories()
    // {
    //   $categories = request()->validate([
    //     'categories.*' => ['integer', 'exists:product_categories,id']
    //   ]);
    //
    //   return $categories;
    // }

    public function getProductAttributes()
    {
      $attributes = [
        'type' => request('type'),
        'name' => request('name'),
        'description' => request('description'),
        'product_key' => request('product_key'),
        'part_number' => request('part_number'),
        'por_id' => request('por_id'),
        'header' => request('header'),
        'quantity' => request('quantity'),
        'slug' => request('slug'),
        'model' => request('model'),
        'inactive' => request('inactive'),
        'hide_on_website' => request('hide_on_website')
      ];

      $attributes['slug'] = $attributes['slug'] ?: str_slug($attributes['name']);

      return $attributes;

    }

    public function validateRates()
    {
      $rates = request()->validate([
        'rates.*.time' => ['numeric', 'required_with:rates.*.rate', 'nullable'],
        'rates.*.period' => ['required_with:rates.*.time', 'numeric'],
        'rates.*.rate' => ['numeric', 'required_with:rates.*.time', 'nullable'],
      ]);

      return $rates;
    }


}
