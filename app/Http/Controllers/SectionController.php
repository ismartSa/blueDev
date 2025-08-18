<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\section;
use App\Http\Requests\StoresectionRequest;
use App\Http\Requests\UpdatesectionRequest;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoresectionRequest $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id)
    {
        $section = Section::findOrFail($id);

    // إرسال البيانات إلى مكون Vue
    return Inertia::render('Sections/Edit', [
        'section' => $section,
    ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatesectionRequest $request, section $section)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(section $section)
    {
        //
    }
}
