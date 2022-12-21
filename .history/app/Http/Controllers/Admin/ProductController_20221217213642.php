<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Requests\ProductValidate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->Product = new Product();
        $this->Category = new Category();
        $this->Image = new Image();
    }

    //Hàm return về view product admin
    public function product()
    {
        $data = DB::table('category')->join('product', 'category.id', '=', 'product.id_cate')->get();
        $cate = DB::table('category')->get();
        $stt = 1;
        return view('admin.pages.product', compact('data', 'cate', 'stt'));
    }

    //Hàm thêm sản phẩm
    public function addProduct(ProductValidate $request)
    {
        $input = $request->validated();
        $add_product = $this->Product->addProduct($input);
        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = $input['name'] . '-image-' . time() . rand(1, 1000) . '.' . $image->extension();
                $image->move(public_path('storage/'), $imageName);
                $this->Image->addImage([
                    'id_product' => DB::table('product')->latest('id')->first()->id,
                    'url' => $imageName
                ]);
            }
        }
        if ($add_product) {
            toast('Add product successfully!', 'success')->autoClose(1500);
            return redirect()->route('admin_product');
        }
    }

    //Hàm thêm ảnh sản phẩm
    public function addImage(Request $request, $id)
    {
        $product = Product::find($id);
        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = $product->name . '-image-' . time() . rand(1, 1000) . '.' . $image->extension();
                $image->move(public_path('storage/'), $imageName);
                $this->Image->addImage([
                    'id_product' => $product->id,
                    'url' => $imageName
                ]);
            }
            toast('Add image successfully', 'success')->autoClose(1500);
            return redirect()->back();
        }
    }

    //Hàm sửa sản phẩm
    public function edit_product(ProductValidate $request)
    {
        $input = $request->validated();
        DB::table('product')->where('id', $request->id)->update($input);
        return redirect()->back()->with('toast_success', 'Change product successfully!');
    }

    //Hàm xóa sản phẩm
    public function deleteProduct($id)
    {
        $this->deleteAllImage($id);
        if ($this->Product->deleteProduct($id)) {
            // return redirect('admin/product')->with('success', 'Delete Successfully');
            toast('Delete product successfully!', 'success')->autoClose(1500);
            return redirect('admin/product');
        } else {
            return redirect('admin/product')->with('error', 'unsuccessfully');
        }
    }

    //Hàm show ảnh sản phẩm
    public function image($id)
    {
        $product = Product::find($id);
        $img = $product->images;
        return view('admin.pages.image', compact('img'));
    }

    //Hàm xóa tất cả ảnh sản phẩm
    public function deleteAllImage($id)
    {
        $images = image::where('id_product', '=', $id)->get();
        $delete = image::where('id_product', '=', $id)->delete();
        foreach ($images as $img) {
            $url = $img->url;
            Storage::disk('public')->delete($url);
        };
        return redirect()->back();
    }

    //Hàm xóa từng ảnh của sản phẩm
    public function deleteImage($id)
    {
        $image = image::where('id', '=', $id)->get();
        Storage::disk('public')->delete($image[0]->url);
        if (image::where('id', '=', $id)->delete()) {
            toast('Delete image successfully', 'success')->autoClose(1500);
            return redirect()->back();
        }
    }
}