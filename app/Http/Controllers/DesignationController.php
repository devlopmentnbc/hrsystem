<?php

namespace App\Http\Controllers;

use App\Models\Designations;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Designation List
     */
    public function index()
    {
        $designations = Designations::latest()->get();

        return view(
            'designations.index',
            compact('designations')
        );
    }

    /**
     * Create Form
     */
    public function create()
    {
        return view('designations.create');
    }

    /**
     * Store Designation
     */
    public function store(Request $request)
    {
        $request->validate([

            'designation_name' => 'required|max:255',

        ]);

        Designations::create([

            'designation_name' => $request->designation_name,
            'description' => $request->description,
            'status' => $request->status ?? 1,

        ]);

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation created successfully');
    }


    /**
     * Edit Designation
     */
    public function edit(Designations $designation)
    {
        return view(
            'designations.edit',
            compact('designation')
        );
    }

    /**
     * Update Designation
     */
    public function update(Request $request, Designations $designation)
    {
        $request->validate([

            'designation_name' => 'required|max:255',

        ]);

        $designation->update([

            'designation_name' => $request->designation_name, 
            'description' => $request->description,
            'status' => $request->status,

        ]);

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation updated successfully');
    }
}