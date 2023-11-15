<?php

namespace App\Http\Controllers;

use App\Models\sensorM;
use App\Models\perangkatM;
use App\Models\logsM;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class apiC extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function ambil(Request $request)
     {
         try {
             $token_sensor = $request->header('TokenSensor');
             
             $cek = perangkatM::where('token', $token_sensor)->count();
             
             if($cek === 0 ){
                 return abort(500, 'Kunci tidak valid');
             }
             
             $sensor = sensorM::count();
             if($sensor < 1) {
                sensorM::create([
                    "relay1" => "0",
                    "relay2" => "0",
                    "waktu" => date("Y-m-d H:i:s"),
                ]);
             }

             $pengaturan = perangkatM::first();
             $sensor = sensorM::first();
             $data = [
                "relay1" => $sensor->relay1,
                "relay2" => $sensor->relay2,
                "hari" => $pengaturan->hari,
                "jam" => $pengaturan->jam,
                "umur" => $pengaturan->umur,
             ];

             return $data;
 
         } catch (\Throwable $th) {
            return abort(500, 'Kunci tidak valid');
         }
         
     }

     public function normalkan(Request $request)
     {
         try {
             $token_sensor = $request->header('TokenSensor');
             
             $cek = perangkatM::where('token', $token_sensor)->count();
             
             if($cek === 0 ){
                 return abort(500, 'Kunci tidak valid');
             }
             
             $sensor = sensorM::first();
             
             $relay1 = $sensor->relay1;
             $relay2 = $sensor->relay2;

            if($relay1 == 1) {
                $sensor->update([
                    "relay1" => 0,
                ]);
             }
             
            if($relay2 == 1) {
                $sensor->update([
                    "relay2" => 0,
                ]);
             }
             

             return "telah dinormalkan";
 
         } catch (\Throwable $th) {
            return abort(500, 'Kunci tidak valid');
         }
         
    }

    public function kirim(Request $request)
    {
        try {
            
            $token_sensor = $request->header('TokenSensor');
             
             $cek = perangkatM::where('token', $token_sensor)->count();
             
             if($cek === 0 ){
                 return abort(500, 'Kunci tidak valid');
             }
            
            $jsonData = $request->getContent();
            $json = json_decode($jsonData, true);

            $jarakD5 = (int)$json[0]["jarakD5"];
            $jarakD7 = (int)$json[0]["jarakD7"];
            $sensorAnalog = (int)$json[0]["sensorAnalog"];
            $sensorDigital = (int)$json[0]["sensorDigital"];
            $waktu = date("Y-m-d H:i:s", $json[0]["waktu"]);

            $perangkat = perangkatM::first();
            
            $siram_j = $perangkat->jam; 
            $pupuk_h = $perangkat->hari; 
            $menit = $perangkat->menit; 

            $sensor = sensorM::first();

            if($sensor->relay1 == 0 && $sensor->relay2 == 0) {
                $relay1 = 0; 
                $relay2 = 0; 
                
                $logs = logsM::count();
                
                if($logs > 0) {
                    $logs = logsM::orderBy("created_at", "desc")->first();
    
                    $tanggalsekarang = strtotime(date("Y-m-d H:i:s"));
                    $tanggalsensor = strtotime(date("Y-m-d H:i:s", strtotime($logs->waktu))); 
                    $siramBerikutnya = strtotime("+".$siram_j." hours", $tanggalsensor);
                    $pupukBerikutnya = strtotime("+".$siram_j." hours", $tanggalsensor);
                    $berikutnya = strtotime("+".$menit." minutes", $tanggalsensor);
    
                    if($tanggalsekarang > $berikutnya) {
                        if($tanggalsekarang > $siramBerikutnya) {
                            $relay1 = 1;
                        }
                        if($tanggalsekarang > $pupukBerikutnya) {
                            $relay2 = 1;
                        }
    
                        
                        if($relay1 == 1 && $relay2 == 1 ){
                            $ket = "Penyiraman dan pemupukan";
                        }else if ($relay1 == 1) {
                            $ket = "Melakukan penyiraman";
                        }else if($relay2 == 1) {
                            $ket = "Melakukan Pemupukan";
                        }else {
                            $ket = "Telah melakukan Penyiraman dan Pemupukan";
                        }
        
                        logsM::create([
                            "jarakD5" => $jarakD5,
                            "jarakD7" => $jarakD7,
                            "sensorAnalog" => $sensorAnalog,
                            "sensorDigital" => $sensorDigital,
                            "waktu" => $waktu,
                            "ket" => $ket,
                        ]);
        
                        sensorM::update([
                            "relay1" => $relay1,
                            "relay2" => $relay2,
                            "waktu" => $waktu,
                        ]);

                    }
    
                }else {
                    $relay1 = 1; 
                    $relay2 = 1; 
                    if($relay1 == $relay2 ){
                        $ket = "Penyiraman dan pemupukan";
                    }
                     
    
                    logsM::create([
                        "jarakD5" => $jarakD5,
                        "jarakD7" => $jarakD7,
                        "sensorAnalog" => $sensorAnalog,
                        "sensorDigital" => $sensorDigital,
                        "waktu" => $waktu,
                        "ket" => $ket,
                    ]);
    
                    sensorM::update([
                        "relay1" => $relay1,
                        "relay2" => $relay2,
                        "waktu" => $waktu,
                    ]);
                }

            }
            


            return "finish";

        } catch (\Throwable $th) {
            return abort(500, 'Kunci tidak valid');
        }
        
    }

    public function login(Request $request)
    { 
        try {
            $username = $request->username;
            $password = $request->password;

            $jumlahpassword = strlen($password);
            if($jumlahpassword<8){
                $pesan = [
                    "pesan" => "Minimal password 8 karakter",
                    "login" => 0,
                ];
                return $pesan;
            }

            $proses = User::where("username", $username);

            $pesan = [
                "pesan" => "Username dan Passord tidak benar",
                "login" => 0,
            ];

            if($proses->count() === 1) {
                if(Hash::check($password, $proses->first()->password)){
                    $data = $proses->first();
                    $alat = perangkatM::first();
                    $token_sensor = $alat->token;

                    $pesan = [
                        "pesan" => "Selamat datang",
                        "login" => 1,
                        "id" => $data->id,
			            "posisi" => $data->posisi,
                        "name" => $data->name,
                        "token_sensor" => $token_sensor,
                        "email" => $data->email,
                    ];
                }
            }

            return $pesan;
            
        } catch (\Throwable $th) {
            $pesan = [
                "pesan" => "Error form Catch",
                "login" => 0,
            ];
            return $pesan;
        }
    }


    public function data(Request $request, $token_sensor)
    {
             
        $cek = perangkatM::where('token', $token_sensor)->count();
        
        if($cek === 0 ){
            return abort(500, 'Kunci tidak valid');
        }

        // try {
            $logs = logsM::orderBy("created_at", "desc")->first();
            $sensor = sensorM::first();

            $data = [
                "relay1" => $sensor->relay1,
                "relay2" => $sensor->relay2,
                "kelembaban" => empty($logs->sensorAnalog)?0:$logs->sensorAnalog,
                "air" => empty($logs->jarakD5)?0:$logs->jarakD5,
                "pupuk" => empty($logs->jarakD7)?0:$logs->jarakD7,
                "ket" => empty($logs->ket)?"tanpa keterangan":$logs->ket,
            ];

            return $data;
            
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


    }

    public function siramair(Request $request, $token_sensor)
    {
             
        $cek = perangkatM::where('token', $token_sensor)->count();
        
        if($cek === 0 ){
            return abort(500, 'Kunci tidak valid');
        }

        // try {
        $waktu = date("Y-m-d H:i:s");
        
        $sensor = sensorM::first();
        $logs = logsM::orderBy("created_at", "desc")->first();

        $sensor->update([
            "relay1" => 1,
        ]);

        logsM::create([
            "sensorDigital" => $logs->sensorDigital,
            "sensorAnalog" => $logs->sensorAnalog,
            "jarakD5" => $logs->jarakD5,
            "jarakD7" => $logs->jarakD7,
            "waktu" => $waktu,
            "ket" => "Melakukan penyiraman manual",
        ]);

        
        
        $ket = [
            "pesan" => "Melakukan penyiraman manual",
        ];

        return $ket;
            
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


    }

    public function sirampupuk(Request $request, $token_sensor)
    {
             
        $cek = perangkatM::where('token', $token_sensor)->count();
        
        if($cek === 0 ){
            return abort(500, 'Kunci tidak valid');
        }

        // try {
        $waktu = date("Y-m-d H:i:s");
        
        $sensor = sensorM::first();
        $logs = logsM::orderBy("created_at", "desc")->first();

        $sensor->update([
            "relay2" => 1,
        ]);

        logsM::create([
            "sensorDigital" => $logs->sensorDigital,
            "sensorAnalog" => $logs->sensorAnalog,
            "jarakD5" => $logs->jarakD5,
            "jarakD7" => $logs->jarakD7,
            "waktu" => $waktu,
            "ket" => "Pemupukan manual",
        ]);

        
        
        $ket = [
            "pesan" => "Pemupukan manual",
        ];

        return $ket;
            
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


    }
    

    public function pengaturan(Request $request, $token_sensor)
    {
        $cek = perangkatM::where('token', $token_sensor)->count();
        
        if($cek === 0 ){
            return abort(500, 'Kunci tidak valid');
        }

        $pengaturan = perangkatM::first();
        $data = [
            "jam" => empty($pengaturan->jam)?0:$pengaturan->jam,
            "menit" => empty($pengaturan->menit)?0:$pengaturan->menit,
        ];

        return $data;

    }



    public function updatepengaturan(Request $request, $token_sensor)
    {
        $cek = perangkatM::where('token', $token_sensor)->count();
        
        if($cek === 0 ){
            return abort(500, 'Kunci tidak valid');
        }

        $pengaturan = perangkatM::first();

        $req = $request->all();

        $pengaturan->update($req);

        $data = [
            "pesan" => "Success",
        ];

        return $data;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function histori(Request $request, $token_sensor)
    {
        $cek = perangkatM::where('token', $token_sensor)->count();
        
        if($cek === 0 ){
            return abort(500, 'Kunci tidak valid');
        }

        $logs = logsM::orderBy("waktu", "desc")->limit(20)->get();

        $data = [];
        foreach ($logs as $log) {
            if($log->sensorAnalog > 700) {
                $kelembaban = "Tanah Normal";
            }else {
                $kelembaban = "Tanah Lembab";
            }


            $data[] = [
                "kelembaban" => $kelembaban,
                "air" => $log->jarakD5." Cm",
                "pupuk" => $log->jarakD7." Cm",
                "ket" => $log->ket,
                "tanggal" => \Carbon\Carbon::parse($log->waktu)->isoFormat("DD MMMM Y")." ".date("H:i:s", strtotime($log->waktu)),
            ];
        }

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\sensorM  $sensorM
     * @return \Illuminate\Http\Response
     */
    public function show(sensorM $sensorM)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\sensorM  $sensorM
     * @return \Illuminate\Http\Response
     */
    public function edit(sensorM $sensorM)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\sensorM  $sensorM
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, sensorM $sensorM)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\sensorM  $sensorM
     * @return \Illuminate\Http\Response
     */
    public function destroy(sensorM $sensorM)
    {
        //
    }
}
