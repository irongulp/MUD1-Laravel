<?php

namespace App\Http\Controllers;

use App\Http\Views\DebugRoom;
use App\Models\Attribute;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use function compact;

class DebugRoomController extends Controller
{
    private const START_ROOM_ATTRIBUTE = 'startrm';

    public function index(): RedirectResponse
    {
        $roomId = Attribute
            ::where('name', self::START_ROOM_ATTRIBUTE)
            ->firstOrFail()
            ->rooms()
            ->firstOrFail()
            ->id;

        return Redirect(route('debug-room', $roomId));
    }
    public function get(
        Room $room,
        DebugRoom $debugRoom
    ) {
        return $debugRoom->build($room)->getContent();
    }
}
