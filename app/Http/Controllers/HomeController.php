<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

use App\Models\Product;

use App\Models\Cart;

use App\Models\Order;

class HomeController extends Controller
{
    public function redirect()
    {
        $usertype = Auth::user()->usertype;

        if($usertype == '1')
        {
            return view('admin.home');
        }
        else
        {
            $product = product::all();
            return view('user.home', compact('product'));
        }
    }

    public function index()
    {
        if(Auth::id())
        {
            return redirect('redirect');
        }
        else
        {
            $product = product::all();
            return view('user.home', compact('product'));
        }

        $product = product::all();
        return view('user.home', compact('product'));
    }

    public function product_details($id)
    {
        $product = product::find($id);
        return view('user.product_details', compact('product'));
    }

    public function add_cart(Request $request, $id)
    {
        if(Auth::id())
        {
            $user = Auth::user();

            $product = product::find($id);            

            $cart = new cart;
            $cart->name = $user->name;
            $cart->email = $user->email;
            $cart->phone = $user->phone;            
            $cart->user_id = $user->id;
            $cart->product_title = $product->title;
            $cart->price = $product->price * $request->quantity;
            $cart->image = $product->image;
            $cart->product_id = $product->id;
            $cart->quantity = $request->quantity;
            $cart->save();

            return redirect()->back();
        }
        else
        {
            return redirect('login');
        }        
    }

    public function show_cart()
    {
        if(Auth::id())
        {
            $id = Auth::user()->id;
            $cart = cart::where('user_id', '=', $id)->get();
            return view('user.showcart', compact('cart'));
        }
        else
        {
            return redirect('login');
        }
    }

    public function remove_cart($id)
    {
        $cart = cart::find($id);
        $cart->delete();
        return redirect()->back();
    }
    
    public function show_order()
    {
        if(Auth::id())
        {
            $user = Auth::user();
            $userid = $user->id;
            $order = order::where('user_id', '=', $userid)->get();
            return view('user.order', compact('order'));
        }
        else
        {
            return redirect('login');
        }
    }

    public function cancel_order($id)
    {
        $order = order::find($id);
        $order->delivery_status = 'Order Cancelled';
        $order->save();

        return redirect()->back();
    }    

    public function cash_order(Request $request)
    {
        $user = Auth::user();
        $userid = $user->id;
        
        $data = cart::where('user_id', '=', $userid)->get();
        
        $request->validate([
            'address' => 'required|min:10|max:100',
            'postalcode' => 'required|regex:/^[0-9]{5}$/',
        ], [
            'address.required' => 'The address field is required.',
            'address.min' => 'The address must be at least :min characters long.',
            'address.max' => 'The address may not be greater than :max characters long.',
            'postalcode.required' => 'The postal code field is required.',
            'postalcode.regex' => 'The postal code must be a 5-digit number.',
        ]);

        foreach($data as $data)
        {
            $order = new order;
            $order->name = $data->name;
            $order->email = $data->email;
            $order->phone = $data->phone;            
            $order->user_id = $data->user_id;
            $order->product_title = $data->product_title;
            $order->quantity = $data->quantity;
            $order->price = $data->price;
            $order->image = $data->image;            
            $order->product_id = $data->product_id;
            $order->address = $request->address;
            $order->postalcode = $request->postalcode;
            $order->invoice_no = Str::upper(Str::random(16, true));            

            $order->save();

            $cart_id = $data->id;
            $cart = cart::find($cart_id);
            $cart->delete();

            $product = product::find($data->product_id);
            $product->quantity = $product->quantity - $data->quantity;        
            $product->save();      
        }

        return redirect()->back()->with('message', 'Order successfully received!');
    }    
}
