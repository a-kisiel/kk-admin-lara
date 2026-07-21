<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Medium;
use App\Models\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Intervention\Image\Format;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PieceController extends Controller
{
    public function index(): Response
    {
        $pieces = Piece::with(['media', 'collections', 'children'])
            ->whereNull('parent_id')
            ->orderBy('title', 'asc')
            ->get()
            ->append(['stub']);
        return Inertia::render('Pieces/index', [
            'pieces' => $pieces
        ]);
    }

    public function add(): Response
    {
        $media = [];
        $all_media = Medium::all();
        foreach($all_media as $medium)
            $media[] = [
                'label' => $medium->title,
                'value' => $medium->id
            ];

        $collections = [];
        $all_collections = Collection::all();
        foreach($all_collections as $collection)
            $collections[] = [
                'label' => $collection->title,
                'value' => $collection->id
            ];

        return Inertia::render('Pieces/form', [
            'mode' => 'add',
            'media' => $media,
            'collections' => $collections
        ]);
    }

    public function show($id): Response
    {
        $piece = Piece::with(['media', 'collections', 'children'])
            ->findOrFail($id);
        return Inertia::render('Pieces/form', [
            'imgUrl' => Storage::url(''),
            'mode' => 'show',
            'piece' => $piece
        ]);
    }

    public function edit($id): Response
    {
        $piece = Piece::with(['media', 'collections', 'children'])
            ->findOrFail($id);

        $media = [];
        $all_media = Medium::all();
        foreach($all_media as $medium)
            $media[] = [
                'label' => $medium->title,
                'value' => $medium->id
            ];

        $collections = [];
        $all_collections = Collection::all();
        foreach($all_collections as $collection)
            $collections[] = [
                'label' => $collection->title,
                'value' => $collection->id
            ];

        return Inertia::render('Pieces/form', [
            'imgUrl' => Storage::url(''),
            'mode' => 'edit',
            'piece' => $piece,
            'media' => $media,
            'collections' => $collections
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->input();
        $all = $request->all();
        Log::debug($all);

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');

            $data['hash'] = $piece->getNewHash();
        }

        $piece = Piece::create($data);

        Log::debug("created $piece->id");

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $piece->addImage($uncompressed, $data['hash'], 'jpg');
            $piece->addImage($compressed, $data['hash'], 'webp');
        }

        $piece->updateMedia($data['media']);
        $piece->updateCollections($data['collections']);

        return redirect("/pieces/$piece->id/edit");
    }

    public function update($id, Request $request): RedirectResponse
    {
        $data = $request->input();

        $all = $request->all();
        Log::debug($all);
        
        $piece = Piece::with(['media', 'collections'])
            ->findOrFail($id);

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');

            $data['hash'] = $piece->getNewHash();
            $piece->addImage($uncompressed, $data['hash'], 'jpg');
            $piece->addImage($compressed, $data['hash'], 'webp');
        }

        $piece->update($data);

        $piece->updateMedia($data['media']);
        $piece->updateCollections($data['collections']);

        return redirect("/pieces/$id/edit");
    }

    public function delete($id): RedirectResponse
    {
        $piece = Piece::with(['media', 'collections', 'children'])
            ->findOrFail($id);

        $piece->media()->detach($piece->media->pluck('id'));
        $piece->collections()->detach($piece->collections->pluck('id'));

        Piece::where('id', $id)
            ->orWhere('parent_id', $id)
            ->delete();

        return redirect("/pieces");
    }
}
