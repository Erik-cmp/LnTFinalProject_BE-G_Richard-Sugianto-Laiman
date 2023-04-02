<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;

class AdminController extends Controller
{
    public function view_category()
    {
        $data = category::all();

        return view('admin.category', compact('data'));
    }

    public function view_product()
    {
        $category = category::all();
        return view('admin.product', compact('category'));
    }

    public function add_category(Request $request)
    {
        $data = new category;
        $data->category_name = $request->category;        
        $data->save();

        return redirect()->back()->with('message', 'Category Added Successfully');
    }
    
    public function delete_category($id)
    {
        $data = category::find($id);
        $data->delete();

        return redirect()->back()->with('message', 'Category Deleted Successfully');
    }    

    public function add_product(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:80',
            'description' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',        
            'category' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ], [
            'title.required' => 'The product title is required.',
            'title.min' => 'The product title must be at least :min characters long.',
            'title.max' => 'The product title may not be greater than :max characters long.',
            'description.required' => 'The product description is required.',
            'price.required' => 'The product price is required.',
            'price.numeric' => 'The product price must be a number.',
            'quantity.required' => 'The product quantity is required.',
            'quantity.integer' => 'The product quantity must be an integer.',
            'category.required' => 'The product category is required.',
            'image.required' => 'The product image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The file must be a type of :values.',
            'image.max' => 'The file may not be larger than :max kilobytes.',
        ]);
    
        $product = new product;
        
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;        
        $product->category = $request->category;

        $image = $request->image;
        $imagename = time().'.'.$image->getClientOriginalExtension();
        $request->image->move('product', $imagename);
        $product->image = $imagename;

        $product->save();

        return redirect()->back()->with('message', 'Product Added Successfully');
    }

    public function show_product()
    {
        $product = product::all();
        return view('admin.show_product', compact('product'));
    }

    public function delete_product($id)
    {
        $product = product::find($id);
        $product->delete();

        return redirect()->back()->with('message', 'Product Deleted Successfully');
    }
    
    public function update_product($id)
    {
        $product = product::find($id);
        $category = category::all();

        return view('admin.update_product', compact('product', 'category'));
    }

    public function update_product_confirm(Request $request, $id)
    {
        $product = product::find($id);
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;        
        $product->category = $request->category;

        $image = $request->image;

        if($image)
        {
            $imagename = time().'.'.$image->getClientOriginalExtension();
            $request->image->move('product', $imagename);
            $product->image = $imagename;
        }

        $product->save();

        return redirect()->back()->with('message', 'Product Updated Successfully');        
    }    
}
