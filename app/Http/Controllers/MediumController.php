<?php

namespace App\Http\Controllers;

use App\Models\Medium;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MediumController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Media/index', [
            'media' => Medium::all() ?? []
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('Media/form', [
            'mode' => 'add'
        ]);
    }

    public function show($id): Response
    {
        $medium = Medium::findOrFail($id);
        return Inertia::render('Media/form', [
            'mode' => 'show',
            'medium' => $medium
        ]);
    }

    public function edit($id): Response
    {
        $medium = Medium::findOrFail($id);
        return Inertia::render('Media/form', [
            'mode' => 'edit',
            'medium' => $medium
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->input();

        $id = Medium::create($data)->id;
        return redirect("/media/$id");
    }

    public function update($id, Request $request): RedirectResponse
    {
        $medium = Medium::findOrFail($id);
        $medium->update($request->all());

        return redirect("/media/$id/edit");
    }

    public function delete($id): RedirectResponse
    {
        $medium = Medium::findOrFail($id);
        $medium->delete();

        return redirect("/media");
    }
}
