<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Department List
     */
    public function index()
    {
        $departments = Departments::latest()->get();

        return view(
            'departments.index',
            compact('departments')
        );
    }

    /**
     * Create Form
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store Department
     */
    public function store(Request $request)
    {
        $request->validate([

            'department_name' => 'required|max:255',

        ]);

        Departments::create([

            'department_name' => $request->department_name,
            'department_code' => $request->department_code,
            'description' => $request->description,
            'status' => $request->status ?? 1,

        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully');
    }


    /**
     * Edit Department
     */
    public function edit(Departments $department)
    {
        return view(
            'departments.edit',
            compact('department')
        );
    }

    /**
     * Update Department
     */
    public function update(Request $request, Departments $department)
    {
        $request->validate([

            'department_name' => 'required|max:255',

        ]);

        $department->update([

            'department_name' => $request->department_name,
            'department_code' => $request->department_code,
            'description' => $request->description,
            'status' => $request->status,

        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully');
    }
}