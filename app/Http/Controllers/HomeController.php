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

class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        return Redirect(route('home'));
    }
}
