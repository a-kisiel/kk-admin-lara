<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Book;
use App\Models\Medium;
use App\Models\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function index(Request $request): Response
    {
        $params = $request->all();
        
        if (empty($params['page']))
            $params['page'] = 1;
        if (empty($params['sort']))
            $params['sort'] = 'alphabetical';

        $query = Book::with(['children'])->whereNull('parent_id');

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
        
        $books = $query->paginate(20);

        $total_pieces = $books->total();

        $books = $books->append(['stub']);

        return Inertia::render('Books/index', [
            'books' => $books,
            'params' => $params
        ]);
    }

    public function add(): Response
    {
        $types = array_flip(config('enums.media_types'));

        $media = [];

        foreach(Medium::where('type', $types['Book'])->get() as $medium)
            $media[] = [
                'label' => $medium->title,
                'value' => $medium->id
            ];

        return Inertia::render('Books/form', [
            'mode' => 'add',
            'media' => $media
        ]);
    }

    public function show($id): Response
    {
        $book = Book::with(['media', 'children'])
            ->findOrFail($id);
        return Inertia::render('Books/form', [
            'imgUrl' => config('filesystems.disks.' . config('filesystems.default') . '.url'),
            'mode' => 'show',
            'book' => $book
        ]);
    }

    public function edit($id): Response
    {
        $book = Book::with(['media', 'children'])
            ->findOrFail($id);

        $types = array_flip(config('enums.media_types'));

        $media = [];
        foreach(Medium::where('type', $types['Book'])->get() as $medium)
            $media[] = [
                'label' => $medium->title,
                'value' => $medium->id
            ];

        return Inertia::render('Books/form', [
            'imgUrl' => config('filesystems.disks.' . config('filesystems.default') . '.url'),
            'book' => $book,
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

            $data['hash'] = Book::getNewHash();
        }

        $book = Book::create($data);

        if (isset($has_files)) {
            $book->addImage($uncompressed, $data['hash'], 'jpg');
            $book->addImage($compressed, $data['hash'], 'webp');
        }

        $book->handleChildren($request, $data['children'] ?? []);
        $book->updateMedia($data['media']);

        return redirect("/books/$book->id/edit");
    }

    public function update($id, Request $request): RedirectResponse
    {
        $data = $request->input();
        
        $book = Book::with(['media', 'children'])->findOrFail($id);

        if ($request->hasFile('main_uncompressed') && $request->hasFile('main_compressed')) {
            $uncompressed = $request->file('main_uncompressed');
            $compressed = $request->file('main_compressed');

            $data['hash'] = $book->getNewHash();
            $book->addImage($uncompressed, $data['hash'], 'jpg');
            $book->addImage($compressed, $data['hash'], 'webp');
        }

        $book->handleChildren($request, $data['children'] ?? []);

        $book->update($data);

        $book->updateMedia($data['media']);

        return redirect("/books/$id/edit");
    }

    public function delete($id): RedirectResponse
    {
        $book = Book::with(['media', 'children'])
            ->findOrFail($id);

        $book->media()->detach($book->media->pluck('id'));

        Book::where('id', $id)
            ->orWhere('parent_id', $id)
            ->delete();

        return redirect("/books");
    }
}
