<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use function compact;

class DebugRoomController extends Controller
{
    private const START_ROOM_ATTRIBUTE = 9;

    public function index()
    {
        $room = Attribute::findOrFail(self::START_ROOM_ATTRIBUTE)->rooms()->first();

        return view('debug-room', compact('room'));
    }
    public function get(Room $room)
    {
        return view('debug-room', compact('room'));
    }
}
