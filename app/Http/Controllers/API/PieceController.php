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
    public function toggleActive($id)
    {
        $piece = Piece::findOrFail($id);
        $piece->update(['active' => !$piece->active]);
    }

    public function generateThumbnails($id)
    {
        $piece = Piece::findOrFail($id);
        $piece->generateThumbnails();
    }
}
