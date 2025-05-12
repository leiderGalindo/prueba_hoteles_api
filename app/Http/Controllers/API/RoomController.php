<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Room;
use App\Models\Hotel;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'quantity' => 'required|integer',
            'room_type' => 'required|in:ESTANDAR,JUNIOR,SUITE',
            'accommodation' => 'required|in:SENCILLA,DOBLE,TRIPLE,CUADRUPLE',
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        try {
            // Validar acomodacion a procesar
            $response = $this->ValidateRoom($request);
            if(isset($response['error'])){
                throw new \Exception($response['error']);
            }
            // echo json_encode($request->all());die;

            $room = new Room();
            $room->hotel_id = $request->input('hotel_id');
            $room->quantity = $request->input('quantity');
            $room->room_type = $request->input('room_type');
            $room->accommodation = $request->input('accommodation');
            $room->save();

            return response()->json([
                'message' => 'Room created successfully',
                'room' => $room
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Validamos si el hotel existe
            $hotel = Hotel::where('id', $id)->first();
            if(!$hotel) throw new \Exception('The hotel does not exist');
            
            // Obtenemos el numero de habitaciones actuales del hotel
            $rooms = Room::where('hotel_id', $id)->get();

            return response()->json($rooms, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'quantity' => 'required|integer',
            'room_type' => 'required|in:ESTANDAR,JUNIOR,SUITE',
            'accommodation' => 'required|in:SENCILLA,DOBLE,TRIPLE,CUADRUPLE',
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        try {
            // Validar acomodacion a procesar
            $response = $this->ValidateRoom($request, $id);
            if(isset($response['error'])){
                throw new \Exception($response['error']);
            }

            $room = Room::where('id', $id)->first();
            if(!$room) throw new \Exception('The room does not exist');

            $room->hotel_id = $request->input('hotel_id');
            $room->quantity = $request->input('quantity');
            $room->room_type = $request->input('room_type');
            $room->accommodation = $request->input('accommodation');
            $room->save();

            return response()->json([
                'message' => 'Room updated successfully',
                'room' => $room
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $room = Room::where('id', $id)->first();
            if(!$room) throw new \Exception('The room does not exist');

            $room->delete();

            return response()->json([
                'message' => 'Room deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Valida los datos de la habitación
     * 
     * @param $request
     * 
     * @return array|bool
    **/
    public function ValidateRoom($request = null, $id = false){ 
        if(!$request) return false;

        try {
            $hotelId = $request->input('hotel_id');

            // Validamos si el hotel existe
            $hotel = Hotel::where('id', $hotelId)->first();
            if(!$hotel) throw new \Exception('The hotel does not exist');
            
            // Obtenemos el numero de habitaciones actuales del hotel
            if(!$id){
                $currentNumberOfRooms = Room::where('hotel_id', $hotelId)->sum('quantity');
            }else{
                $currentNumberOfRooms = Room::where('hotel_id', $hotelId)->where('id', '!=', $id)->sum('quantity');
            }
            $roomsAvailable = ($hotel->number_of_rooms - $currentNumberOfRooms);

            // Validamos que aun existan habitaciones disponibles
            if($roomsAvailable === 0){
                throw new \Exception('Limit reached: no more rooms can be added to this hotel.');
            }

            // Validamos que la cantidad de habitaciones ingresada no supere las habitaciones disponibles
            if($request->input('quantity') > $roomsAvailable){
                throw new \Exception("Limit exceeded: you can only register {$roomsAvailable} more rooms in this hotel.");
            }

            // Validaciones de tipo de habitacion
            $roomAccommodations = [
                'ESTANDAR'  => ['SENCILLA', 'DOBLE'],
                'JUNIOR'    => ['TRIPLE', 'CUADRUPLE'],
                'SUITE'     => ['SENCILLA', 'DOBLE', 'TRIPLE'],
            ];

            if(!in_array($request->input('accommodation'), $roomAccommodations[$request->input('room_type')])){
                $validAccommodations = implode(', ', $roomAccommodations[$request->input('room_type')]);
                throw new \Exception("The accommodation is not valid for this room type. Please select one of these options: ({$validAccommodations}).");
            }

            // Validamos que no exista un habitacion con las mismas caracteristicas
            $query = Room::where('hotel_id', $hotelId)
                ->where('room_type', $request->input('room_type'))
                ->where('accommodation', $request->input('accommodation'));
                
            if($id){
                $query->where('id', '!=', $id);
            }
            
            $room = $query->first();
            if($room) throw new \Exception('The room already exists');
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        return false;
    }
}
