<?php

class Courier extends Controller
{
    use AnterAja, Jne, SiCepat;

    //     public function index() {
    //         $data = $this->getRajaOnkirCost('1000', '6983');

    //         foreach($data['rajaongkir']['results'] as $key => $value) {
    //             if($value['code']=='sicepat') { continue; }
    // 			foreach($value['costs'] as $key_service => $value_service) {
    // 			    $result['content'][] = [
    //                     'layanan' => $value['code'],
    //                     'kode' => $value_service['service'],
    //                     'produk' => $value_service['description'],
    //                     'estimasi' => $value_service['cost'][0]['etd'],
    //                     'harga' => $value_service['cost'][0]['value']
    //                 ];
    // 			}
    //         }

    //         var_dump($result);
    //     }

    public function ajax_get_subdistrict()
    {
        $result = $this->getSubDistrict_AJ($_POST['subdistrict']);
        echo json_encode($result);
    }


    public function ajax_get_courier_cost()
    {
        $anteraja = $this->getServiceRates_AJ($_POST['postalcode_penjual'], $_POST['postalcode_pembeli'], $_POST['berat']);
        $jne = $this->getServiceRates_JNE($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        if (isset($_SESSION['login']['id_user']) and in_array($_SESSION['login']['id_user'], ['US202202120000000007', 'US202202180000000002'])) {
            $sicepat = $this->getServiceRates_SC($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        }

        $result = [];


        if ($anteraja['status'] == 200) {
            $result['stts'] = 200;
            foreach ($anteraja['content']['services'] as $val) {
                $result['content'][] = [
                    'layanan' => 'ANTER AJA',
                    'kode' => $val['product_code'],
                    'produk' => $val['product_name'],
                    'estimasi' => str_replace(' Day', '', $val['etd']),
                    'harga' => $val['rates']
                ];
            }
        } else {
            if (!is_null($anteraja)) {
                $result['stts'] = 400;
                if ($anteraja['status'] == 400 and $anteraja['info'] == 'Origin is Mandatory') {
                    $result['content'] = "Alamat Warehouse Tidak Ditemukan!";
                } elseif ($anteraja['status'] == 400 and $anteraja['info'] == 'Destination is Mandatory') {
                    $result['content'] = "Alamat Tidak Ditemukan! Mohon Isi Kode Pos dengan Benar.";
                } else {
                    $result['content'] = "Terjadi Kesalahan";
                }
            }
        }

        if (!isset($jne['error'])) {
            $result['stts'] = 200;
            foreach ($jne['price'] as $val) {
                if ($val['goods_type'] == 'Document/Paket') {
                    $result['content'][] = [
                        'layanan' => 'JNE',
                        'kode' => $val['service_code'],
                        'produk' => $val['service_display'],
                        'estimasi' => $val['etd_from'] == $val['etd_thru'] ? (is_null($val['etd_from']) ? '-' : $val['etd_from']) : $val['etd_from'] . '-' . $val['etd_thru'],
                        'harga' => $val['price']
                    ];
                }
            }
        }

        if ($sicepat['sicepat']['status']['code'] == 200) {
            $result['stts'] = 200;
            foreach ($sicepat['sicepat']['results'] as $val) {
                $result['content'][] = [
                    'layanan' => 'SiCepat',
                    'kode' => $val['service'],
                    'produk' => $val['description'],
                    'estimasi' => str_replace(' hari', '', $val['etd']),
                    'harga' => $val['tariff']
                ];
            }
        }

        //     $rajaongkir = $this->getRajaOnkirCost($_POST['berat'], $_POST['subdistrict_pembeli']);

        //     if (isset($rajaongkir['rajaongkir']['results'][0])) {
        //         $result['stts'] = 200;
        //         foreach($rajaongkir['rajaongkir']['results'] as $key => $value) {
        //             if($value['code']=='sicepat' or $value['code']=='jne') { continue; }
        // 			foreach($value['costs'] as $key_service => $value_service) {
        // 			    $result['content'][] = [
        //                     'layanan' => $value['code'],
        //                     'kode' => $value_service['service'],
        //                     'produk' => $value_service['description'],
        //                     'estimasi' => str_replace(' HARI', '', $value_service['cost'][0]['etd']),
        //                     'harga' => $value_service['cost'][0]['value']
        //                 ];
        // 			}
        //         }   

        //     }

        echo json_encode($result);
    }

    public function ajax_get_tracking_history()
    {
        $courier = $_POST['courier'];
        $awb = $_POST['awb'];

        if ($courier == 'anter aja') {
            $get = $this->getTrackingHistory_AJ($awb);
            if (!is_null($get['content']['history'])) {
                $result['stts'] = 200;
                $result['awb'] = $get['content']['waybill_no'];
                foreach ($get['content']['history'] as $val) {
                    $result['history'][] = [
                        'text' => is_null($val['hub_name']) ? $val['message']['id'] :  $val['hub_name'] . ' - ' . $val['message']['id'],
                        'date' => date('M j Y g:i A', strtotime($val['timestamp']))
                    ];
                    krsort($result['history']);
                    $result['history'] = array_values($result['history']);
                }
            } else {
                $result['stts'] = 400;
            }
        } elseif ($courier == 'jne') {
            $get = $this->getTrackingHistory_JNE($awb);
            if (isset($get['history'])) {
                $result['stts'] = 200;
                $result['awb'] = $get['detail'][0]['cnote_no'];
                foreach ($get['history'] as $val) {
                    $result['history'][] = [
                        'text' => $val['desc'],
                        'date' => date('M j Y g:i A', strtotime($val['date']))
                    ];
                }
            } else {
                $result['stts'] = 400;
            }
        } elseif ($courier == 'sicepat') {
            $get = $this->getTrackingHistory_SC($awb);
            if ($get['sicepat']['status']['code'] == 200) {
                $get = $get['sicepat']['result'];
                $result['stts'] = 200;
                $result['awb'] = $get['waybill_number'];
                foreach ($get['track_history'] as $val) {
                    $result['history'][] = [
                        'text' => $val['status'] . ' | ' . $val['city'],
                        'date' => date('M j Y g:i A', strtotime($val['date_time']))
                    ];
                }
            } else {
                $result['stts'] = 400;
            }
        }

        echo json_encode($result);
    }




    private function getRajaOnkirCost($berat, $subdistrict_pembeli)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . RAJAONGKIR_ORIGIN . "&originType=" . RAJAONGKIR_ORIGIN_TYPE . "&destination=" . $subdistrict_pembeli . "&destinationType=subdistrict&weight=" . $berat . "&courier=" . RAJAONGKIR_KURIR,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . RAJAONGKIR_KEY . ""
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
