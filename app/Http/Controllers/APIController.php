<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use App\Order;
use App\Outlet;
use App\Slot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
//use Illuminate\Support\Facades\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Illuminate\Support\Facades\File;

class APIController extends Controller
{
    /**
     * Get root url.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeOrder(Request $request)
    {
      try {
          $this->validate($request, [
              'order_number' => 'required|unique:orders,order_number',

          ]);
      } catch (ValidationException $e) {
          return $e->getResponse();
      }
        Order::create($request->all()+['user_id'=> $request->user()->id]);
        return new JsonResponse(['message' => 'Order Created']);
    }


    public function updateOrder(Request $request,$id)
    {

      $utype =$request->user()->user_type;

      $order = Order::findorFail($id);
      if ($request->has('order_number')) {
        $order->order_number = $request->order_number;
      }
      if ($request->has('outlet_id')) {
        $order->outlet_id = $request->outlet_id;
      }
      if ($request->has('delivery_date')) {
        $order->delivery_date = $request->delivery_date;
      }

      if ($request->has('delivery_status')) {
        $order->delivery_status = $request->delivery_status;
      }
      if ($request->has('delivery_slot_id')) {
        $order->delivery_slot_id = $request->delivery_slot_id;
      }
      if ($request->has('delivery_trip_type')) {
        $order->delivery_trip_type = $request->delivery_trip_type;
      }
      if ($request->has('membership_number')) {
        $order->membership_number = $request->membership_number;
      }
      if ($request->has('pos_bill')) {
        $order->pos_bill = $request->pos_bill;
      }
      if ($request->has('payment_method')) {
        $order->payment_method = $request->payment_method;
      }
      if ($request->has('ca_remarks')) {
        $order->ca_remarks = $request->ca_remarks;
      }
      if ($request->has('availablity_status')) {
        $order->availablity_status = $request->availablity_status;
      }

      if ($request->has('delivered_date')) {
        $order->delivery_date = $request->delivery_date;
      }
      if ($request->has('delivered_time')) {
        $order->delivered_time = $request->delivered_time;
      }

      if ($request->has('delivery_time')) {
        $order->delivery_time = $request->delivery_time;
      }
      if ($request->has('de_remarks')) {
        $order->de_remarks = $request->de_remarks;
      }

      $order->last_update_user_id = $request->user()->id;

      if ($request->has('edit_blocked')) {
        $order->edit_blocked = $request->edit_blocked;
      }
      if ($request->has('additional_remarks')) {
          $order->additional_remarks = $request->additional_remarks;
        }
        if ($request->has('follow_up_pending')) {
            $order->follow_up_pending = $request->follow_up_pending;
          }
        if ($request->has('longitude')) {
              $order->longitude = $request->longitude;
        }
      if ($request->has('attachment')) {
        $file = $this->saveFile($request->attachment);
        $order->attachment = $file;
      }

      if($utype === '4') {
        $order->delivery_executive_id = $request->user()->id;
      }
      $order->save();


      if ($utype === '1') {
        return response()->json(['Message' => 'Success']);
      }
      elseif($utype === '2')
      {
        return response()->json(['Message' => 'Success']);
      }
      else{
        return response()->json(['Message' => 'Status Updated']);
      }

    }

    public function getOrder(Request $request)
    {

      $today = Carbon::today()->toDateString();
      if ($request->has('date')) {
        $date = $request->date;
        $order = Order::whereDate('delivery_date',$date)->get();
      }else {

        $order = Order::all();
      }

    //  $order = Order::where('created_at',$today)->get();

      return $order->toJson();
    }
    public function getOrderbyStatus(Request $request)
    {
      $ds = $request->delivery_status;
      if ($ds === '0') {
        $order = Order::where('delivery_status','=', '0')->get();
      }
      elseif ($ds === '1') {
        $order = Order::where('delivery_status','=', '1')->get();
      }
      else {
        $order = Order::where('delivery_status','=', '2')->get();
      }
      return $order->toJson();
    }

    public function saveFile(Request $request)
    {
        $file = $request->file;
        Storage::put($file->getClientOriginalName(),  File::get($file));

        return $file->getClientOriginalName();
    }

    public function deleteFile($name)
    {
        Storage::delete($name);
        return response()->json('success');
    }

    public function getFileList(){

        $files = Storage::files('/');
        return response()->json($files);

    }

    public function viewFile($name){

      /*  return response()->make(Storage::get($name), 200, [
            'Content-Type' => Storage::mimeType($name),
            'Content-Disposition' => 'inline; '.$name,
        ]);
*/
    $path = storage_path('storage/'.$name);
  //  $headers = ['Content-Type' => 'application/pdf'];
    return response()->download($path);

    //return response()->download($path, $name, $headers);
    }


    public function getSlot()
    {
      $slot = Slot::all();
      return response()->json(['Message' => $slot]);
    }
    public function getOutlet()
    {
      $outlet = Outlet::all();
      return response()->json(['Message' => $outlet]);
    }

    public function GetOrderByOutletID($id)
    {
      $today = Carbon::today()->toDateString();
      $order = Order::where('outlet_id',$id)->whereDate('delivered_date',$today)->get();

      return $order->toJson();
    }

    public function GetOrderByUserID(Request $request)
    {
      $today = Carbon::today()->toDateString();
      $order = Order::where('user_id',$request->user()->id)->whereDate('delivered_date',$today)->get();

      return $order->toJson();
    }

}
