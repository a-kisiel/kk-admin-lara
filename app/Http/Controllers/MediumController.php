<?php

namespace App\Http\Controllers;

use App\Models\Medium;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MediumController extends Controller
{
    public function index(): Response
    {
        $all = Medium::withCount(['pieces', 'supportPieces'])->get();

        $media = [];
        $types = config('enums.media_types');
        foreach($types as $type)
            $media[$type] = [];

        foreach($all as $medium) {
            $type = $types[$medium->type];
            $media[$type][] = $medium;
        }

        return Inertia::render('Media/index', [
            'media' => $media
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('Media/form', [
            'mode' => 'add',
            'mediaTypes' => config('enums.media_types')
        ]);
    }

    public function show($id): Response
    {
        $medium = Medium::findOrFail($id)->append('typeLabel');
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
            'medium' => $medium,
            'mediaTypes' => config('enums.media_types')
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
        $medium->pieces()->detach();
        $medium->books()->detach();
        $medium->sketches()->detach();

        DB::table('pieces')->where('support_id', $id)->update(['support_id' => null]);

        $medium->delete();

        return redirect("/media");
    }
}
