<?php

namespace App\Http\Controllers\Admin\Category;

use App\Model\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        // return response()->json($request->toArray());
        $categories = Category::latest()->get();
        return view('admin.category.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'type' => 'required',
            'icon' => 'required|image',
        ]);
        if ($validator->fails()) {
            return redirect::back()->withErrors($validator)->withInput();
        } else {
            $category = new Category;
            $category->type = $request->input('type');
            if ($request->hasfile('icon')) {
                $path = 'images/category';
                $icon = $request->file('icon');
                $my_icon = rand() . '.' . $icon->getClientOriginalExtension();
                $upload = $icon->storeAs($path, $my_icon, 'public');
                $category->icon = $path . '/' .  $my_icon;
            }
            $category->save();
            if (is_null($category)) {
                return redirect(route('category.index'))->with('error', 'Data has not been inserted');
            } else {
                return redirect(route('category.index'))->with('success', 'Data has been inserted successfully.');
            }
        }
    }

    public function show(Category $category)
    {
        //
    }


    public function edit(Category $category)
    {
        return view('admin.category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'icon' => 'image',
        ]);
        if ($validator->fails()) {
            return redirect::back()->withErrors($validator)->withInput();;
        } else {
            $category = Category::find($id);
            $category->type = $request->input('type');
            if ($request->hasfile('icon')) {
                $path = '/' . $category->icon;
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                $path = 'images/category';
                $icon = $request->file('icon');
                $my_icon = rand() . '.' . $icon->getClientOriginalExtension();
                $upload = $icon->storeAs($path, $my_icon, 'public');
                $category->icon = $path . '/' . $my_icon;
            }
            $result = $category->update();
            if ($result) {
                return redirect(route('category.index'))->with('success', 'Data has been updated successfully.');
            } else {
                return redirect(route('category.index'))->with('error', 'Data has not been updated.');
            }
        }
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        $path = '/' . $category->icon;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $result = $category->delete();
        if ($result) {
            return redirect(route('category.index'))->with('success', 'Data has been deleted successfully.');
        } else {

            return redirect(route('category.index'))->with('error', 'Data has not been deleted');
        }
    }
}
