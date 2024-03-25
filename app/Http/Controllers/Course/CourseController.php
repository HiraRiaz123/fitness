<?php

namespace App\Http\Controllers\Admin\Course;

use App\Model\Course;
use App\Model\CustomNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index()
    {
        // return response()->json($request->toArray());
        $courses = Course::latest()->get();
        return view('admin.course.index', compact('courses'));
    }


    public function create()
    {
        return view('admin.course.create');
    }


    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'weeks' => 'required|integer|min:1',
            'is_paid' => 'required|integer|min:0|max:3',
            'icon' => 'required|image',
        ]);
        if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator)->withInput();;
        } else {
        $course = new Course;
        $course->category_id = $request->input('category_id');
        $course->title = $request->input('title');
        $course->description = $request->input('description');
        $course->weeks = $request->input('weeks');
        $course->is_paid = $request->input('is_paid');
        if ($request->hasfile('icon')) {
            $path = 'images/course';
            $icon = $request->file('icon');
            $my_icon = rand() . '.' . $icon->getClientOriginalExtension();
            $upload = $icon->storeAs($path, $my_icon, 'public');
            $course->icon = $path . '/' . $my_icon;
        }
        $course->save();
        CustomNotification::create([
            'title' => "Good News",
            'description' => "Hope you're having a great day.You will be benefiting from our courses and excercising regularly. A good news for you is that we are lauching a new course " .$course->title. " Hope you get benefit from it.",
        ]); 
        if (is_null($course)) {
            return redirect(route('course.index'))->with('error', 'Data has not been inserted');
        } else {
            return redirect(route('course.index'))->with('success', 'Data has been inserted successfully.');
        }
    }
}

    public function show($id)
    {
        //
    }


    public function edit(Course $course)
    {
        //...Use Helper......
        $categories = getAllCategories();
        return view('admin.course.edit', compact('course', 'categories'));
    }


    public function update(Request $request, $id)
    {
         $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'weeks' => 'required|integer|min:1',
            'is_paid' => 'required|integer|min:0|max:3',
            'icon' => 'image',
        ]);
      if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator)->withInput();;
        } else {
        $course = Course::find($id);
        $course->category_id = $request->input('category_id');
        $course->title = $request->input('title');
        $course->description = $request->input('description');
        $course->weeks = $request->input('weeks');
        $course->is_paid = $request->input('is_paid');
        if ($request->hasfile('icon')) {
            $path = '/' . $course->icon;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $path = 'images/course';
            $icon = $request->file('icon');
            $my_icon = rand() . '.' . $icon->getClientOriginalExtension();
            $upload = $icon->storeAs($path, $my_icon, 'public');
            $course->icon = $path . '/' . $my_icon;
        }
        $result = $course->update();
        if ($result) {
            return redirect(route('course.index'))->with('success', 'Data has been updated successfully');
        } else {
            return redirect(route('course.index'))->with('error', 'Data has not been  updated.');
        }
        }
    }


    public function destroy($id)
    {
        $course = Course::find($id);
        $path = '/' . $course->icon;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        $result = $course->delete();
        if ($result) {
            return redirect(route('course.index'))->with('success', 'Data has been deleted successfully');
        } else {
            return redirect(route('course.index'))->with('error', 'Data has not been  deleted.');
        }
    }
}
