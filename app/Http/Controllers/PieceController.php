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
    public function index(Request $request): Response
    {
        $params = $request->all();

        if (empty($params['page']))
            $params['page'] = 1;
        if (empty($params['sort']))
            $params['sort'] = 'alphabetical';

        $query = Piece::with(['media', 'collections', 'children'])
            ->whereNull('parent_id');

        if (!empty($params['medium_id']))
            $query->whereHas('media', function ($q) use ($params) {
                $q->where('media.id', $params['medium_id']);
            });

        if (!empty($params['collection_id']))
            $query->whereHas('collections', function ($q) use ($params) {
                $q->where('collections.id', $params['collection_id']);
            });

        if (!empty($params['is_active'])) {
            $a = $params['is_active'] === 'yes';
            $query->where('active', $a);
        }

        if (!empty($params['is_wallpaper'])) {
            $w = $params['is_wallpaper'] === 'yes';
            $query->where('is_wallpaper', $w);
        }

        if ($params['sort'] === 'first')
            $query->orderBy('created_at', 'asc');
        elseif ($params['sort'] === 'latest')
            $query->orderBy('created_at', 'desc');
        elseif ($params['sort'] === 'alphabetical')
            $query->orderByRaw('lower(title) asc');
        
        $pieces = $query->paginate(20);

        $total_pieces = $pieces->total();

        $pieces = $pieces->append(['stub']);

        $piece_type = array_flip(config('enums.media_types'))['Piece'];
        $media = Medium::where('type', $piece_type)->get();

        $collections = Collection::all();

        return Inertia::render('Pieces/index', [
            'pieces' => $pieces,
            'total_pieces' => $total_pieces,
            'params' => $params,
            'media' => $media,
            'collections' => $collections
        ]);
    }

    public function add(): Response
    {
        $types = array_flip(config('enums.media_types'));

        $media = 
        $support_media = [];

        $all_media = Medium::all();
        foreach($all_media as $medium) {
            $m = [
                'label' => $medium->title,
                'value' => $medium->id
            ];
            if ($medium->type === $types['Piece'])
                $media[] = $m;
            if ($medium->type === $types['Support'])
                $support_media[] = $m;
        }

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
            'supportMedia' => $support_media,
            'collections' => $collections
        ]);
    }

    public function show($id): Response
    {
        $piece = Piece::with(['media', 'collections', 'children', 'supportMedium'])
            ->findOrFail($id);
        return Inertia::render('Pieces/form', [
            'imgUrl' => config('filesystems.disks.' . config('filesystems.default') . '.url'),
            'mode' => 'show',
            'piece' => $piece
        ]);
    }

    public function edit($id): Response
    {
        $piece = Piece::with(['media', 'collections', 'children', 'supportMedium'])
            ->findOrFail($id);

        $types = array_flip(config('enums.media_types'));

        $media = 
        $support_media = [];

        $all_media = Medium::all();
        foreach($all_media as $medium) {
            $m = [
                'label' => $medium->title,
                'value' => $medium->id
            ];
            if ($medium->type === $types['Piece'])
                $media[] = $m;
            if ($medium->type === $types['Support'])
                $support_media[] = $m;
        }

        $collections = [];
        $all_collections = Collection::all();
        foreach($all_collections as $collection)
            $collections[] = [
                'label' => $collection->title,
                'value' => $collection->id
            ];

        return Inertia::render('Pieces/form', [
            'mode' => 'edit',
            'imgUrl' => config('filesystems.disks.' . config('filesystems.default') . '.url'),
            'piece' => $piece,
            'media' => $media,
            'supportMedia' => $support_media,
            'collections' => $collections
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->input();
        $all = $request->all();

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');

            $data['hash'] = Piece::getNewHash();
        }

        $piece = Piece::create($data);

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $piece->addImage($uncompressed, $data['hash'], 'jpg');
            $piece->addImage($compressed, $data['hash'], 'webp');
        }

        $piece->handleChildren($request, $data['children'] ?? []);
        $piece->updateMedia($data['media']);
        $piece->updateCollections($data['collections']);

        return redirect("/pieces/$piece->id/edit");
    }

    public function update($id, Request $request): RedirectResponse
    {
        $data = $request->input();
        
        $piece = Piece::with(['media', 'collections', 'children'])->findOrFail($id);

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');

            $data['hash'] = $piece->getNewHash();
            $piece->addImage($uncompressed, $data['hash'], 'jpg');
            $piece->addImage($compressed, $data['hash'], 'webp');
        }

        $piece->handleChildren($request, $data['children'] ?? []);

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
