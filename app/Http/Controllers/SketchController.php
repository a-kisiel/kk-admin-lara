<?php

namespace App\Http\Controllers;

use App\Models\Sketch;
use App\Models\Medium;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SketchController extends Controller
{
    public function index(Request $request): Response
    {
        $params = $request->all();
        
        if (empty($params['page']))
            $params['page'] = 1;
        if (empty($params['sort']))
            $params['sort'] = 'alphabetical';

        $query = Sketch::with(['media']);

        if (!empty($params['medium_id']))
            $query->whereHas('media', function ($q) use ($params) {
                $q->where('media.id', $params['medium_id']);
            });

        if (!empty($params['is_active'])) {
            $a = $params['is_active'] === 'yes';
            $query->where('active', $a);
        }

        if ($params['sort'] === 'first')
            $query->orderBy('created_at', 'asc');
        elseif ($params['sort'] === 'latest')
            $query->orderBy('created_at', 'desc');
        elseif ($params['sort'] === 'alphabetical')
            $query->orderByRaw('lower(title) asc');
        
        $sketches = $query->paginate(20);

        $total_pieces = $sketches->total();

        $sketches = $sketches->append(['stub']);

        $types = array_flip(config('enums.media_types'));

        return Inertia::render('Sketches/index', [
            'sketches' => $sketches,
            'media' => Medium::where('type', $types['Piece'])->get(),
            'params' => $params
        ]);
    }

    public function add(): Response
    {
        $types = array_flip(config('enums.media_types'));

        $media = [];
        $all_media = Medium::all();
        
        foreach($all_media as $medium) {
            $m = [
                'label' => $medium->title,
                'value' => $medium->id
            ];
            if ($medium->type === $types['Piece'])
                $media[] = $m;
        }

        return Inertia::render('Sketches/form', [
            'mode' => 'add',
            'media' => $media
        ]);
    }

    public function show(): Response
    {
        $sketch = Sketch::with(['media', 'children'])
            ->findOrFail($id);
        return Inertia::render('Sketches/form', [
            'imgUrl' => config('filesystems.disks.' . config('filesystems.default') . '.url'),
            'mode' => 'show',
            'sketch' => $sketch
        ]);
    }

    public function edit($id): Response
    {
        $sketch = Sketch::with(['media', 'children'])
            ->findOrFail($id);

        $types = array_flip(config('enums.media_types'));

        $all_media = Medium::all();
        foreach($all_media as $medium) {
            $m = [
                'label' => $medium->title,
                'value' => $medium->id
            ];
            if ($medium->type === $types['Piece'])
                $media[] = $m;
        }

        return Inertia::render('Sketches/form', [
            'imgUrl' => config('filesystems.disks.' . config('filesystems.default') . '.url'),
            'sketch' => $sketch,
            'media' => $media
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->input();
        $all = $request->all();

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');
            $has_files = true;

            $data['hash'] = Sketch::getNewHash();
        }

        $sketch = Sketch::create($data);

        if (isset($has_files)) {
            $sketch->addImage($uncompressed, $data['hash'], 'jpg');
            $sketch->addImage($compressed, $data['hash'], 'webp');
        }

        $sketch->handleChildren($request, $data['children'] ?? []);
        $sketch->updateMedia($data['media']);

        return redirect("/sketches/$sketch->id/edit");
    }

    public function update($id, Request $request): RedirectResponse
    {
        $data = $request->input();
        
        $sketch = Sketch::with(['media', 'children'])->findOrFail($id);

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');

            $data['hash'] = $sketch->getNewHash();
            $sketch->addImage($uncompressed, $data['hash'], 'jpg');
            $sketch->addImage($compressed, $data['hash'], 'webp');
        }

        $sketch->handleChildren($request, $data['children'] ?? []);

        $sketch->update($data);

        $sketch->updateMedia($data['media']);

        return redirect("/sketches/$id/edit");
    }

    public function delete($id): RedirectResponse
    {
        $sketch = Sketch::with(['media', 'children'])
            ->findOrFail($id);

        $sketch->media()->detach($sketch->media->pluck('id'));

        Sketch::where('id', $id)
            ->orWhere('parent_id', $id)
            ->delete();

        return redirect("/sketches");
    }
}
