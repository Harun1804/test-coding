<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PertamaController extends Controller
{
    public function getData()
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

    public function store(Request $request)
    {
        $dataJson = $this->getData();

        $result = explode(' ', $request->model);
        if(in_array($result[0],['00','MG','32'])){
            $model = end($result);
        }elseif(in_array(end($result),['00'])){
            $model = $result[0];
        }else{
            $arraySplit = str_split($request->model,1);
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

        return response()->json($data);
    }
}
