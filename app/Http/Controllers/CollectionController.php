<?php

namespace App\Http\Controllers;

use App\Models\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Log;

class CollectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Collections/index', [
            'collections' => Collection::all() ?? []
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('Collections/form', [
            'mode' => 'add'
        ]);
    }

    public function show($id): Response
    {
        $collection = Collection::findOrFail($id);
        return Inertia::render('Collections/form', [
            'mode' => 'show',
            'collection' => $collection
        ]);
    }

    public function edit($id): Response
    {
        $collection = Collection::findOrFail($id);
        return Inertia::render('Collections/form', [
            'mode' => 'edit',
            'collection' => $collection
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->input();

        $id = Collection::create($data)->id;
        return redirect("/collections/$id");
    }

    public function update($id, Request $request): RedirectResponse
    {
        $collection = Collection::findOrFail($id);

        $collection->update($request->all());

        return redirect("/collections/$id/edit");
    }

    public function delete($id): RedirectResponse
    {
        $collection = Collection::findOrFail($id);
        $collection->delete();

        return redirect("/collections?msg=Collection deleted");
    }
}
