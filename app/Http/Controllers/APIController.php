<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use App\Order;
use App\Outlet;
use App\Slot;
use App\User;
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
use DB;
use TeamTNT\TNTSearch\TNTSearch;


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
        $file = $this->saveFilex($request->attachment);
        Order::create([
                'user_id'=> $request->user()->id,
                'order_number' => $request->order_number,
                'outlet_id' => $request->outlet_id,
                'delivery_date' => $request->delivery_date,
                'delivery_slot_id' => $request->delivery_slot_id,
                'delivery_status'=> $request->delivery_status,
                'membership_number' => $request->membership_number,
                'ca_remarks' => $request->ca_remarks,
                'attachment' => $file,
                'payment_method' => $request->payment_method,

        ]);
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
        if ($request->delivery_status === '4') {
            $order->delivery_status = $request->delivery_status;
            $order->delivered_date = Carbon::today()->toDateString();

            $order->delivered_date_only = Carbon::now();

            $order->delivery_time = Carbon::now();
            if ($request->has('de_remarks')) {
              $order->de_remarks = $request->de_remarks;
            }
            if ($request->has('longitude')) {
                  $order->longitude = $request->longitude;
            }
            if ($request->has('longitude')) {
                  $order->latitude = $request->latitude;
            }

        }else {
          $order->delivery_status = $request->delivery_status;
          $order->user_id = $request->user()->id;
          //$order->delivery_date = Carbon::today()->toDateString();
        }

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


     //if ($utype != '4') {
      //  $order->last_update_user_id = $request->user()->id;
      //}

      if($utype == '4') {
        $order->delivery_executive_id = $request->user()->id;
      }



      if ($request->has('edit_blocked')) {
        $order->edit_blocked = $request->edit_blocked;
      }
      if ($request->has('additional_remarks')) {
          $order->additional_remarks = $request->additional_remarks;
      }
      if ($request->has('follow_up_pending')) {
            $order->follow_up_pending = $request->follow_up_pending;
      }

      if ($request->has('attachment')) {
        $file = $this->saveFilex($request->attachment);
        $order->attachment = $file;
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
      $yesterday = Carbon::yesterday()->toDateString();
      if ($request->user()->user_type === 3) {
        $order = DB::table('orders')
        ->join('outlets','outlets.id','=','orders.outlet_id')
        ->join('slots','slots.id','=','orders.delivery_slot_id')
        ->join('users','users.id','orders.user_id')
        ->select('orders.*','slots.*','outlets.*','users.name')
      //  ->whereDateBetween('delivery_date',[$yesterday,$today])
        ->where('orders.outlet_id',$request->user()->outlet_id)
        ->get();
      }else {
        if ($request->has('date')) {
          $date = $request->date;
          $order = DB::table('orders')
          ->join('outlets','outlets.id','=','orders.outlet_id')
          ->join('slots','slots.id','=','orders.delivery_slot_id')
          ->join('users','users.id','orders.user_id')
          ->select('orders.*','slots.*','outlets.*','users.name')
          ->whereDate('delivery_date',$date)
          ->get();
          }
          elseif ($request->has('slot_id')) {

              $order = DB::table('orders')
            ->join('outlets','outlets.id','=','orders.outlet_id')
            ->join('slots','slots.id','=','orders.delivery_slot_id')
            ->join('users','users.id','orders.user_id')
            ->select('orders.*','slots.*','outlets.*','users.name')
            ->whereDate('delivery_date',$today)
            ->where('orders.delivery_slot_id', $request->slot_id)
            ->get();
            }
            elseif ($request->has('outlet_id')) {
              $order = DB::table('orders')
              ->join('outlets','outlets.id','=','orders.outlet_id')
              ->join('slots','slots.id','=','orders.delivery_slot_id')
              ->join('users','users.id','orders.user_id')
              ->select('orders.*','slots.*','outlets.*','users.name')
              ->whereDate('delivery_date',$today)
              ->where('orders.outlet_id', $request->outlet_id)
              ->get();
            }
            elseif ($request->has('user_id')) {
              $order = DB::table('orders')
              ->join('outlets','outlets.id','=','orders.outlet_id')
              ->join('slots','slots.id','=','orders.delivery_slot_id')
              ->join('users','users.id','orders.user_id')
              ->select('orders.*','slots.*','outlets.*','users.name')
              ->whereDate('delivery_date',$today)
              ->where('orders.user_id', $request->user_id)
              ->get();
            }
            elseif ($request->has('delivery_status')) {
              $order = DB::table('orders')
              ->join('outlets','outlets.id','=','orders.outlet_id')
              ->join('slots','slots.id','=','orders.delivery_slot_id')
              ->join('users','users.id','orders.user_id')
              ->select('orders.*','slots.*','outlets.*','users.name')
              ->whereDate('delivery_date',$today)
              ->where('orders.delivery_status', $request->delivery_status)
              ->get();
            }
        else {
          $order = DB::table('orders')
          ->join('outlets','outlets.id','=','orders.outlet_id')
          ->join('slots','slots.id','=','orders.delivery_slot_id')
          ->join('users','users.id','orders.user_id')
          ->select('orders.*','slots.slot_time','slots.id as slotId','outlets.id as outetId','outlets.outlet_name','users.name')
          ->whereDate('orders.delivery_date',$today)
          ->orderBy('orders.id','DESC')
          ->get();
        }
      }


     //$order = Order::all();

      return $order->toJson();
    }
    public function getOrderbyStatus(Request $request)
    {
      $ds = $request->delivery_status;
      $id = $request->user()->id;
      $outletId = $request->user()->outlet_id;
      if($ds === '1') {
        $order = Order::where('delivery_status','=', '1')
        ->join('outlets','outlets.id','=','orders.outlet_id')
        ->join('slots','slots.id','=','orders.delivery_slot_id')
        ->select('orders.*','slots.slot_time','outlets.outlet_name')
        ->where('outlets.id',$outletId)
        ->get();
      }
      elseif($ds === '3') {
        $order = Order::where('delivery_status','=', '3')
        ->join('outlets','outlets.id','=','orders.outlet_id')
        ->join('slots','slots.id','=','orders.delivery_slot_id')
        ->select('orders.*','slots.slot_time','outlets.outlet_name')
        ->where('delivery_executive_id',$id)
        ->get();
      }
      else {
        $order = Order::where('delivery_status','=', '4')
        ->join('outlets','outlets.id','=','orders.outlet_id')
        ->join('slots','slots.id','=','orders.delivery_slot_id')
        ->select('orders.*','slots.slot_time','outlets.outlet_name')
        ->where('delivery_executive_id',$id)
        ->get();
      }
      return $order->toJson();
    }
    public function saveFilex($file)
    {

        $filename = str_replace(' ', '_', $file->getClientOriginalName());
        Storage::put($filename,  File::get($file));

        return $filename;
    }
    public function saveFile(Request $request)
    {
        $file = $request->file;
        $filename = str_replace(' ', '_', $file->getClientOriginalName());
        Storage::put($filename,  File::get($file));

        return $filename;
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


    $path = storage_path('storage/'.$name);
  //  $headers = ['Content-Type' => 'application/pdf'];
    return response()->download($path);

    //return response()->download($path, $name, $headers);
    }


    public function getSlot()
    {
      $time = Carbon::now();
      $slot = Slot::all();
      return response()->json(['Message' => $slot, 'Time' => $time]);
    }
    public function getOutlet()
    {
      $outlet = Outlet::orderBy('outlet_name','asc')->get();
      return response()->json(['Message' => $outlet]);
    }
    public function getCA()
    {
      $CA = User::where('user_type',2)->orderBy('name','asc')->get();
      return response()->json(['Message' => $CA]);
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


    /*
    @@ Summary functions
    */

    Public function StatusByOutlet()
    {
      $today = Carbon::today()->toDateString();
      $reserves = DB::table('orders')
      ->join('outlets','outlets.id','=','orders.outlet_id')
      //->join('slots','slots.id','=','orders.delivery_slot_id')
      ->selectRaw('orders.outlet_id,orders.delivery_status, outlets.outlet_name ,count(*) as total')
      ->whereDate('orders.delivery_date',$today)
      ->groupBy('outlet_name','delivery_status')
      ->get();

      return $reserves->toJson();

      //return $order->toJson();
    }

    Public function StatusBySlot(Request $request)
    {
      $today = Carbon::today()->toDateString();
      if ($request->has('outlet_id')) {
        $reserves = DB::table('orders')
        //->join('outlets','outlets.id','=','orders.outlet_id')
        ->join('slots','slots.id','=','orders.delivery_slot_id')
        ->selectRaw('orders.delivery_slot_id,orders.delivery_status,slots.slot_time,count(*),orders.outlet_id')
        ->groupBy('delivery_slot_id','delivery_status')
        ->whereDate('orders.delivery_date',$today)
        ->where('outlet_id',$request->outlet_id)
        ->get();
      }
      else {

        $reserves = DB::table('orders')
        //->join('outlets','outlets.id','=','orders.outlet_id')
        ->join('slots','slots.id','=','orders.delivery_slot_id')
        ->selectRaw('orders.delivery_slot_id,orders.delivery_status,slots.slot_time,count(*)')
        ->whereDate('orders.delivery_date',$today)
        ->groupBy('delivery_slot_id','delivery_status')
        ->get();
      }



      return $reserves->toJson();

      //return $order->toJson();
    }
    Public function PendingBytime()
    {

      $today = Carbon::today()->toDateString();
      $reserves = DB::table('orders')
      ->join('outlets','outlets.id','=','orders.outlet_id')
      ->join('slots','slots.id','=','orders.delivery_slot_id')
      ->selectRaw('orders.outlet_id,orders.delivery_slot_id,orders.delivery_status,slots.slot_time,outlets.outlet_name,count(*)')
      ->whereDate('orders.delivery_date',$today)
      ->whereIn('delivery_status',[0,1,2,4,5,6])
      ->groupBy('delivery_slot_id','outlet_id')
      ->get();

      return $reserves->toJson();

      //return $order->toJson();
    }

    public function OrderSearch()
    {
      $tnt = new TNTSearch;
      $tnt->loadConfig([
          'driver'    => 'mysql',
          'host'      => 'localhost',
          'database'  => 'dbname',
          'username'  => 'user',
          'password'  => 'pass',
          'storage'   => '/var/www/tntsearch/examples/'
      ]);
      $tnt->selectIndex("places.index");



    }

}
