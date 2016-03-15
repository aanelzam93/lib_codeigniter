<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function ambiljalurdengantujuanakhir($jalur, $akhir){
    pre($jalur);
    #pre('AKHIR : '.$akhir);
    foreach ($jalur as $k=>$v){
        #pre($v);
        $terakhir[$k] = 'ilang';
        foreach ($v as $kv=>$vv){
            #pre($vv);   
            #pre($kv.' = '. (substr( $vv, -1, strlen($akhir))) );
            $dites = substr( $vv, -1, strlen($akhir));
            if ($dites == $akhir){
                $terakhir[$k] = $kv;
                break;
            }
        }
        
    }
    
    foreach ($jalur as $k=>$v){
        if ($terakhir[$k] != 'ilang'){
            #pre($v);
            foreach ($v as $kv=>$vv){
                if ($kv <= $terakhir[$k]){
                    $jalurterakhir[$k][$kv] = $vv;
                }
            }
        }else{
           $jalurterakhirilang[$k] = $v; 
        }
        #$jalurfinal[$k] =    
    }
    
    #pre($terakhir);
    #pre($jalurterakhir);
    $data['ilang'] = $jalurterakhirilang;
    $data['sampai'] = $jalurterakhir;
    $semuajalur = $jalurterakhirilang + $jalurterakhir;
	ksort($semuajalur);
    $data['jalur'] = $semuajalur;
    return $data;
}



function probabilitas($arr, $tij = 0.01, $alpha = 0.1, $beta = 0.2) {
	$hasil = 1;

	if (count($arr) > 1) {

		foreach($arr as $j) {
			$x[] = pow($tij, $alpha) * pow(1/$j, $beta);
		}

		$hasil = $x[0] / array_sum($x);
	}
	#pre($hasil);
	return $hasil;
}

function delta_antarkota($arr_jarak, $q = 1) {
	$arr_x = array();
	foreach ($arr_jarak as $r) {
		$arr_x[] = $q / $r;
	}
	
	return array_sum($arr_x);
}

function intensitas($arr_tij, $arr_delta, $p = 0.1) {
	$tij_baru = array();
	foreach ($arr_delta as $k => $v) {
		$tij_baru[$k] = ($p * $arr_tij[$k]) + $v;
	}
	
	return $tij_baru;
}

function siklus_semut($data, $jarak, $raw, $arr_tij = 0.01, $q = 1, $p = 0.1, $alpha = 0.1, $beta = 0.2) {
	
/* start nilai awal */
	$tij_awal = array();
	$tij_hitung = array();
	$jalur_baru = array();
	$hasil = array();
/* end nilai awal */
	
/* start jalursemut */ 
	#pre($data['ilang']);
	#pre($data['sampai']);
	#pre($jarak);
	$semua = $data['jalur'];
	$jalursemut = array();
	foreach($semua as $ka => $va){
		$jj = array();
		$i = 0;
		foreach ($va as $kb => $vb) {
			$semuatitik[$vb] = $vb;
			$nn = explode('-',$vb);
			$jj[] = $nn[0];
			$i++;
			if(count($va) == $i) {
				$jj[] = $nn[1];
			}
		}
		$jalursemut[$ka] = implode('-',$jj);
	}
	#pre($semuatitik);
	#pre($jalursemut);
	//pre($semua);
/* end jalursemut */

/* start probabilitas */
	$probabilitas_semut = array();
	foreach($jalursemut as $km => $vm) {
		
		// untuk perhitungan detail
		$arr_jj = explode('-', $vm);
		$tmp_jj = '';
		$tmp_ar = array();
		$tmp_bg = array();
		$tmp_tabu = '';
		foreach($arr_jj as $kjj => $vjj) {
			$strip = ($tmp_tabu == '') ? '' : '-';
			$tmp_tabu .= $strip . $vjj;
			if($tmp_jj == '') {
				$tmp_jj = $vjj;
				$tmp_ar[] = $vjj;
				$tmp_bg[]['tabulist'] = $vjj;
				continue;
			} else {
				$hit = array();
				foreach($raw[$tmp_jj] as $kr => $vr) {
					/* jika titik ada di tmp_ar artinya jalur balik arah */
					if(! in_array($vr, $tmp_ar)) { 
						$xyz = $tmp_jj . '-' . $vr;
						#pre($xyz);
						
						/* hanya jalur yg ada di semuatitik yang disimpan */
						if (in_array($xyz, $semuatitik)) {
							$hit[$xyz] = $jarak[$xyz];
						}
					}
				}
				
				$tmp_hit = $hit;
				$prop = array();
				foreach($hit as $kh => $vh) {
					unset($tmp_hit[array_search($vh, $tmp_hit)]);
					array_unshift($tmp_hit, $vh);
					
					$prop[$kh] = probabilitas($tmp_hit, $tij_awal[$kh], $alpha, $beta);
				}
				#pre($prop);
				$prop['tabulist'] = $tmp_tabu;
				$tmp_bg[] = $prop;
				#echo "----";
				
				$tmp_jj = $vjj;
				$tmp_ar[] = $vjj;
			}
		}
		$probabilitas_semut[] = $tmp_bg;
	}
	pre($probabilitas_semut);	
/* end probabilitas */

/* start jalur baru */
	#pre($data['sampai']);
	$jalur_baru = $data['sampai'];
	$key = array_search(min($hitungjarak), $hitungjarak);
	
	$jml_tambahan = count($data['ilang']);
	for ($i=0; $i<$jml_tambahan; $i++) {
		$jalur_baru[] = $jalur_baru[$key];
	}
	#pre($jalur_baru);
/* end jalur baru */

/* start olah data */
	$tabeljalur = array();
	foreach($jalursemut as $km => $vm) {
		// tabel jalursemut => jarak;
		$tabeljalur[$km .':'. $vm] = @$hitungjarak[$km] ? : '0';
	}
	$key = array_search(min($hitungjarak), $hitungjarak);	
/* end olah data */

	$hasil['probabilitas'] = $probabilitas_semut;
	$hasil['tabeljalur'] = $tabeljalur;
	$hasil['terpendek'] = $jalursemut[$key];
	$hasil['deltaantarkota'] = $deltaantarkota;
	
	/* untuk perhitungan siklus selanjutnya */
	$hasil['tij_hitung'] = $tij_hitung;
	$hasil['data']['ilang'] = array();
	$hasil['data']['sampai'] = $jalur_baru;
	$hasil['data']['jalur'] = $jalur_baru;
	return $hasil;
}
