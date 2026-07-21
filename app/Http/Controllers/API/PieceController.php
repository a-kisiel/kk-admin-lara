<?php

namespace App\Http\Controllers\API;

use App\Models\Piece;
use App\Models\Medium;
use App\Models\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Log;

class PieceController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->input();

        $all = $request->all();
        $data['active'] = !empty($data['active']);
        
        return Piece::create($data)->id;
    }

    public function addChild($id, Request $request)
    {
        $data = $request->input();
        $data['active'] = !!$data['active'];
        $data['parent_id'] = $id;

        $has_files = $request->hasFile('uncompressed') && $request->hasFile('compressed');
        
        if ($has_files)
            $data['hash'] = Piece::getNewHash();

        $piece = Piece::create($data);

        if ($has_files) {
            $uncompressed = $request->file('uncompressed');
            $compressed = $request->file('compressed');

            $piece->addImage($uncompressed, $data['hash'], 'jpg');
            $piece->addImage($compressed, $data['hash'], 'webp');
        }
        return $piece;
    }

    public function toggleActive($id)
    {
        $piece = Piece::findOrFail($id);
        $piece->update(['active' => !$piece->active]);
    }

    public function update($id, Request $request)
    {
        $piece = Piece::findOrFail($id);

        $data = $request->input();

        if ($request->hasFile('uncompressed') && $request->hasFile('compressed')) {
            $uncompressed = $request->file('uncompressed');
            $compressed = $request->file('compressed');
            
            $data['hash'] = $piece->getNewHash();

            $piece->addImage($uncompressed, $data['hash'], 'jpg');
            $piece->addImage($compressed, $data['hash'], 'webp');
        }
        
        $piece->update($data);

        return $piece;
    }

    public function delete($id)
    {
        Piece::findOrFail($id)->delete();
    }
}
