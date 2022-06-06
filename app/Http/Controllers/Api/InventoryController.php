<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Models\Inventory;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function getData()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.appliance.io/test.json",// your preferred link
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }

        return json_decode($response);
    }

    private function validasi($model)
    {
        $dataJson = $this->getData();

        $result = explode(' ', $model);
        if(in_array($result[0],['00','MG','32'])){
            $model = end($result);
        }elseif(in_array(end($result),['00'])){
            $model = $result[0];
        }else{
            $arraySplit = str_split($model,1);
            if(end($arraySplit) == 'I' && $arraySplit[count($arraySplit)-2] == "A"){
                $arraySlice = array_slice($arraySplit,0,count($arraySplit)-2);
                $model = join('',$arraySlice);
            }elseif($arraySplit[0] == 'M' && $arraySplit[1] == 'G'){
                $arraySlice = array_slice($arraySplit,2,count($arraySplit));
                $model = join('',$arraySlice);
            }
        }

        $arraySplit = str_split($model,1);
        if($arraySplit[0] == '3' && $arraySplit[1] == '2'){
            $arraySlice = array_slice($arraySplit,2,count($arraySplit));
            $model = join('',$arraySlice);
        }

        foreach ($dataJson as $ds) {
            if($model == $ds->model_number){
                $data = [
                    'status'    => 'success',
                    'data'      => $ds
                ];
            }else{
                $data = [
                    'status'    => 'failed',
                    'data'      => 'Model Number Not Found'
                ];
            }
        }

        return $data;
    }

    public function ordererQty(Request $request)
    {
        $data = $this->validasi($request->model_number);
        if($data['status'] == 'failed'){
            return response()->json([
                'status'    => 'failed',
                'data'      => 'Model Number Not Found'
            ]);
        }else{
            $transaksi = Transaksi::create([
                'model_number'   => $request->model_number,
                'quantity'       => $request->quantity,
                'invoice'        => $request->invoice,
                'price'          => $request->price,
            ]);

            $inventory = Inventory::where('model_number',$data['data']->model_number)->first();
            if($inventory->current_quantity == 0){
                $inventory->update([
                    'orderer_produk' => $inventory->orderer_produk + $request->quantity,
                ]);
            }else{
                $sisa = $inventory->current_quantity - $request->quantity;
                if($sisa < 0){
                    $inventory->update([
                        'current_quantity'   => 0,
                        'orderer_produk' => $inventory->current_quantity + abs($sisa),
                    ]);
                }else{
                    $inventory->update([
                        'current_quantity'   => $sisa,
                        'orderer_produk' => $inventory->orderer_produk + $sisa,
                    ]);
                }
            }

            return response()->json([
                'status'    => 'success',
                'data'      => $transaksi
            ]);
        }
    }

    public function currentQty(Request $request)
    {
        $data = $this->validasi($request->model_number);
        if($data['status'] == 'failed'){
            return response()->json([
                'status'    => 'failed',
                'data'      => 'Model Number Not Found'
            ]);
        }else{
            $transaksi = Transaksi::create([
                'model_number'   => $request->model_number,
                'quantity'       => $request->quantity,
                'price'          => $request->price,
            ]);

            $inventory = Inventory::where('model_number',$data['data']->model_number)->first();
            if($inventory->orderer_produk == 0){
                $inventory->update([
                    'current_quantity' => $inventory->current_quantity + $request->quantity,
                ]);
            }else{
                $sisa = $inventory->orderer_produk - $request->quantity;
                if($sisa < 0){
                    $inventory->update([
                        'orderer_produk'   => 0,
                        'current_quantity' => $inventory->current_quantity + abs($sisa),
                    ]);
                }else{
                    $inventory->update([
                        'orderer_produk'   => $sisa,
                        'current_quantity' => $inventory->current_quantity + $sisa,
                    ]);
                }
            }

            return response()->json([
                'status'    => 'success',
                'data'      => $transaksi
            ]);
        }
    }

    public function update(Request $request,Inventory $inventory)
    {
        $inventory->update([
            'model_number'  => $request->model_number,
            'category_produk' => $request->category_produk,
            'orderer_produk' => $request->orderer_produk,
            'current_quantity' => $request->current_quantity,
            'price' => $request->price,
        ]);

        return response()->json([
            'status'    => 'success',
            'data'      => $inventory
        ]);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json([
            'status'    => 'success',
            'data'      => $inventory
        ]);
    }
}
