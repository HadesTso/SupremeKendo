<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Libray\Response;
use App\Models\Code;
use Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function giftInfoExcel(Request $request, Code $code)
    {
        $json_data = $request->input('data');

        $data = json_decode($json_data, true);

        $batch_id = $data['batch_name'];
        $box_id = $data['box_name'];


        $orm = $code->select('code');

        if (count($batch_id)){
            $orm->whereIn('code_batch_id', $batch_id);
        }

        if (count($box_id)){
            $orm->whereIn('code_box_id', $box_id);
        }

        $list = $orm->get();

        $cellData = [
            ['礼包码'],
        ];

        foreach ($list as $key=>$value) {
            $cellData[] = array($value['code']);
        }

        Excel::create('礼包码',function($excel) use ($cellData){
            $excel->sheet('gift', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');

    }
}