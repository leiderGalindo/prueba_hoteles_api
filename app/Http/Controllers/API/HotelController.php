<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Hotel;

class HotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotels = Hotel::all();

        return response()->json($hotels, 200);
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:hotels',
            'number_of_rooms' => 'required|integer|min:1',
        ]);

        // Validamos si los datos fueron validados correctamente
        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        try {
            $slug = $this->StringToSlug($request->name);
            $newHotel = new Hotel();
            $newHotel->name = $request->name;
            $newHotel->slug = $slug;
            $newHotel->address = $request->address;
            $newHotel->city = $request->city;
            $newHotel->nit = $request->nit;
            $newHotel->number_of_rooms = $request->number_of_rooms;
            $newHotel->save();
            

            return response()->json([
                'message' => 'Hotel successfully created',
                'hotel' => $newHotel
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
    public function show(string $slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => 'required|string',
        ]);
        // Validamos si los datos fueron validados correctamente
        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        try {
            $hotel = Hotel::where('slug', $slug)->first();
            if(!$hotel) throw new \Exception('The hotel does not exist');

            return response()->json($hotel, 200);
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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        // Validamos si los datos fueron validados correctamente
        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        try {
            $hotel = Hotel::where('id', $id)->first();
            if(!$hotel) throw new \Exception('The hotel does not exist');

            return response()->json($hotel, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'nit' => 'required|string|max:255',
            'number_of_rooms' => 'required|integer|min:1',
        ]);

        // Validamos si los datos fueron validados correctamente
        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 200);
        }

        try {
            $hotel = Hotel::where('id', $id)->first();
            if(!$hotel) throw new \Exception('The hotel does not exist');

            $hotel->name = $request->name;
            $hotel->address = $request->address;
            $hotel->city = $request->city;
            $hotel->nit = $request->nit;
            $hotel->number_of_rooms = $request->number_of_rooms;
            $hotel->save();

            return response()->json([
                'message' => 'Hotel actualizado correctamente',
                'hotel' => $hotel
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
        try{
            $hotel = Hotel::where('id', $id)->first();
            if(!$hotel) throw new \Exception('The hotel does not exist');

            $hotel->delete();

            return response()->json([
                'message' => 'Hotel eliminado correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    public function StringToSlug($string = "")
    {
        if ($string != "") {
            $string = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $string));
            $separator = "-";
            $re = "/(\\s|\\" . $separator . ")+/mu";
            $str = @trim($string);
            return preg_replace($re, $separator, $str);
        }
        return "";
    }
}
