<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\Members;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'allRoom' => Rooms::join('users', 'rooms.room_owner', '=', 'users.id')
                ->select('rooms.*', 'users.name as room_owner_name')
                ->get(),
            'status' => 200,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $room = Rooms::create([
            'room_owner' => $request->owner_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'slot' => $request->slot,
        ]);

        Rooms::find($room->id)->members()->attach($request->owner_id, ['is_confirm' => true]);

        return response()->json([
            'msg' => "Tạo room thành công",
            'status' => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return response()->json([
            'room' => Rooms::find($id),
            'members' => Rooms::find($id)->members()->where('is_confirm', true)->get(),
            'status' => 200,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rooms $rooms)
    {
        if(Rooms::find($request->id) == null){
            return response()->json(['msg' => "Room không tồn tại", 'status' => 404], 404);
        }
        else{
            if($request->room_owner != Rooms::find($request->id)->room_owner){
                return response()->json(['msg' => "Đây không phải là room của bạn", 'status' => 403], 403);
            }
            else{
                Rooms::find($request->id)->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'image' => $request->image,
                    'slot' => $request->slot,
                ]);
                return response()->json(['msg' => "Update room thành công", 'status' => 200], 200);
            }
        } 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Rooms $rooms)
    {
        if(Rooms::find($request->id) == null){
            return response()->json(['msg' => "Room không tồn tại", 'status' => 404], 404);
        }
        else{
            if($request->room_owner != Rooms::find($request->id)->room_owner){
                return response()->json(['msg' => "Đây không phải là room của bạn", 'status' => 403], 403);
            }
            else{
                Rooms::find($request->id)->delete();
                return response()->json(['msg' => "Delete room thành công", 'status' => 200], 200);
            }
        }
    }

    public function join(Request $request)
    {   
        if(Rooms::find($request->room_id)->members()->where('user_id', $request->user_id)->exists()){
            if(Rooms::find($request->room_id)
                ->members()
                ->where('user_id', $request->user_id)
                ->where('is_confirm', true)->exists()
            ){
                return response()->json([
                    'msg' => "Bạn đã ở trong room này rồi",
                    'status' => 409,
                ]);
            }
            else{
                return response()->json([
                    'msg' => "Bạn đã đăng ký join room này rồi",
                    'status' => 409,
                ]);
            }
        }
        else{
            Rooms::find($request->room_id)->members()->attach($request->user_id, ['is_confirm' => false]);
            return response()->json([
                'msg' => "Bạn đã đăng ký join room thành công",
                'status' => 200,
            ]);
        }
    }

    public function getAllUserNeedConfirm(Request $request)
    {
        return response()->json([
            'user' => Rooms::find($request->room_id)->members()->where('is_confirm', false)->get(),
            'status' => 200,
        ]);
    }

    public function acceptUser(Request $request){
        $id = collect($request->all())
            ->filter(function($value, $key){
                return strpos($key, 'user_') === 0;
            })
            ->all();

        if(empty($id)){
            return response()->json([
                'msg' => "Hãy chọn 1 người",
                'status' => 204,
            ]);
        }

        if(count($id) === count(User::whereIn('id', $id)->get()->toArray())){
            Rooms::find($request->room_id)->members()->detach($id);
            Rooms::find($request->room_id)->members()->attach($id, ['is_confirm' => true]);
            return response()->json([
                'msg' => "Thành công",
                'status' => 200,
            ]);
        }
        else{
            return response()->json([
                'msg' => "Người được chọn không hợp lệ",
                'status' => 304,
            ]);
        }
    }

    public function refuseUser(Request $request){
        $id = collect($request->all())
            ->filter(function($value, $key){
                return strpos($key, 'user_') === 0;
            })
            ->all();

        if(empty($id)){
            return response()->json([
                'msg' => "Hãy chọn 1 người",
                'status' => 204,
            ]);
        }

        if(count($id) === count(User::whereIn('id', $id)->get()->toArray())){
            Rooms::find($request->room_id)->members()->detach($id);
            return response()->json([
                'msg' => "Thành công",
                'status' => 200,
            ]);
        }
        else{
            return response()->json([
                'msg' => "Người được chọn không hợp lệ",
                'status' => 304,
            ]);
        }
    }

    public function roomsOfUser(Request $request)
    {
        if(empty(User::find($request->user_id))){
            return response()->json([
                'status' => 404,
                'msg' => 'Người dùng không tồn tại',
            ]);
        }

        return response()->json(Rooms::where('room_owner', $request->user_id)->get());
    }

    public function roomUserJoin(Request $request)
    {
        if(empty(User::find($request->user_id))){
            return response()->json([
                'status' => 404,
                'msg' => 'Người dùng không tồn tại',
            ]);
        }
        
        $rooms = Members::join('rooms', 'members.room_id', '=', 'rooms.id')
            ->join('users', 'rooms.room_owner', '=', 'users.id')
            ->where('user_id', $request->user_id)
            ->where('is_confirm', true)
            ->select('rooms.*', 'users.name as host_name')
            ->orderBy('rooms.id', 'asc')
            ->get();
        foreach($rooms as $room){
            $members = Rooms::find($room->id)->members()->where('is_confirm', true)->count();
            $room->members = $members; 
        }


        return response()->json($rooms);
    }
}
