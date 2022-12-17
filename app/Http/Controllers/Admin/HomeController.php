<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Orders;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->Product = new Product();
        $this->Category = new Category();
        $this->Order = new Orders();
    }

    //hàm trả về trang home admin
    public function home()
    {
        return view('admin.pages.home');
    }

    //hàm trả về trang orders admin
    public function orders()
    {
        $orders = Orders::all();
        $products = Product::all();
        $cates = Category::all();
        $index = 1;
        return view('admin.pages.order', compact('orders', 'index', 'products', 'cates'));
    }

    //hàm show chi tiết sản phẩm
    public function showProductDetails($slug, $id)
    {
        $product = Product::find($id);
        $images = Image::where('id_product', $product->id)->get();
        $cate = Category::where('id', $product->id_cate)->get('name_cate');
        $pro_id = $product->id;
        return view('users.pages.product-detail', compact('pro_id', 'product', 'images', 'cate'));
    }

    //hàm post hóa đơn lên database
    public function postOrder(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'sdt' => 'required',
            'address' => 'required',
        ];
        $messages = [
            'name.required' => 'Phải nhập họ tên',
            'email.required' => 'Phải nhập email',
            'email.email' => 'Email không đúng định dạng',
            'sdt.required' => 'Phải nhập số điện thoại',
            'address.required' => 'Phải nhập địa chỉ',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('toast_error', 'Đặt hàng không thành công')
                ->withInput();
        }

        DB::table('orders')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->sdt,
            'address' => $request->address,
            'note' => $request->note,
            'id_user' => $request->user_id,
            'id_product' => $request->product_id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Bạn đã đặt hàng thành công. Hãy hoàn thành việc chuyển khoản đặt cọc vào tài khoản 9704-2292-0463-2172 để hoàn thành thủ tục. Từ 2 đến 3 tháng xe về thì công ty sẽ liên hệ cho bạn. Xin chân thành cảm ơn');
    }

    //Hàm sửa trạng thái của đơn hàng
    public function update($id)
    {
        $order = Orders::find($id);
        $order->status = '1';
        // $order->updated_at = new \DateTime();
        $order->save();
        return redirect()->route('admin_orders')->with('toast_success', 'Cập nhật status thành công');
    }

    //Hàm xóa đơn hàng
    public function delete($id)
    {
        $order = Orders::find($id);
        $order->delete();
        return redirect()->route('admin_orders')->with('toast_success', 'Xóa orders thành công');
    }
}
