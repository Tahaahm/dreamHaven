<?php

// app/Http/Controllers/ReportController.php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'report' => 'required|string',
            'property_id' => 'required|exists:properties,id',
        ]);

        // Create a new report
        Report::create([
            'user_id' => auth()->id(), // Assuming you have authentication
            'property_id' => $request->input('property_id'),
            'reason' => $request->input('report'),
        ]);

        return redirect()->back()->with('success', 'Report submitted successfully!');
    }
}
