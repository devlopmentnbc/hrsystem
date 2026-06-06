<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employees;
use App\Models\Designations;
use App\Models\Departments;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employees::latest()->get();

        return view(
            'employees.index',
            compact('employees')
        );
    }

    public function create()
    {
        $designations = Designations::where('status', 1)->get();
        $departments = Departments::where('status', 1)->get();
   
        return view('employees.create',
            compact('designations', 'departments')
        );
    }

    function store(Request $request)
    {
        $request->validate([

            'employee_name' => 'required|max:255',
            'employee_code' => 'required|unique:employees,employee_code',
            'designation_id' => 'required|exists:designations,id',
            'department_id' => 'required|exists:departments,id',
            'gender' => 'required|in:male,female',
            'date_joined' => 'required',

        ]);

        Employees::create([

            'employee_name' => $request->employee_name,
            'employee_code' => $request->employee_code,
            'designation_id' => $request->designation_id,
            'department_id' => $request->department_id,
            'epf_number' => $request->epf_number,
            'nic_number' => $request->nic_number,
            'date_joined' => $request->date_joined,
            'date_resigned' => $request->date_resigned,
            'gender' => $request->gender,
            'address' => $request->address,
            'remarks' => $request->remarks,
            'status' => $request->status ?? 1,
            'last_updated_by' => auth()->id(),
            'last_updated_at' => now(),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully');
    }

    function edit(Employees $employee)
    {
        $designations = Designations::where('status', 1)->get();
        $departments = Departments::where('status', 1)->get();

        return view('employees.edit',
            compact('employee', 'designations', 'departments')
        );
    }

    function update(Request $request, Employees $employee)
    {
        $request->validate([

            'employee_name' => 'required|max:255',
            'employee_code' => 'required|unique:employees,employee_code,' . $employee->id,
            'designation_id' => 'required|exists:designations,id',
            'department_id' => 'required|exists:departments,id',    
            'gender' => 'required|in:male,female',
            'date_joined' => 'required',

        ]);
        $employee->update([

            'employee_name' => $request->employee_name,
            'employee_code' => $request->employee_code,
            'designation_id' => $request->designation_id,
            'department_id' => $request->department_id,
            'epf_number' => $request->epf_number,
            'nic_number' => $request->nic_number,
            'date_joined' => $request->date_joined,
            'date_resigned' => $request->date_resigned,
            'gender' => $request->gender,
            'address' => $request->address,
            'remarks' => $request->remarks,
            'status' => $request->status,
            'last_updated_by' => auth()->id(),
            'last_updated_at' => now(), 
        ]);
        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee updated successfully');
    }
}
