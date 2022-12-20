<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataAnalyticController extends Controller
{
    public function perfomanceOverviewByBranchOffice(Request $request)
    {
        $activeUser = DB::select('SELECT COUNT(*) total_active_user, mst_user.fullname, trim(mst_user.kota) as kota
        FROM trx_booking, mst_user, trx_payment 
        WHERE trx_booking.user_id = mst_user.id 
        AND trx_payment.booking_id = trx_booking.id 
        AND trx_payment.paid = true 
        GROUP BY trx_booking.user_id, mst_user.fullname, kota 
        ORDER BY total_active_user DESC');

        /* group 'kota' to BO directory based on ajc tlc */
        $performanceData = array();
        foreach ($activeUser as $user) {
            
        }
        
        return response()->json(['data' => $activeUser]);
    }


    public function perfomanceDetailByBranchOffice(Request $request)
    {
        $i = 0;
        $regionArray = array("MES", "JKT", "SUB", "DPS", "UPG");
        $Response = array();
       

         
        foreach($regionArray as $item) 
        {
            $region = strtoupper($regionArray[$i]);
            if($region == 'MES'){
                $region = "where table3.BranchOffice = 'MES' or table3.BranchOffice = 'PLM' or table3.BranchOffice = 'PDG' or table3.BranchOffice = 'PKU' or table3.BranchOffice = 'BTJ' or table3.BranchOffice = 'DJB' or table3.BranchOffice = 'TNJ' or table3.BranchOffice = 'BKS' or table3.BranchOffice = 'FLZ' or table3.BranchOffice = 'PGK' or table3.BranchOffice = 'TJG' or table3.BranchOffice = 'BTH' or table3.BranchOffice = 'GNS' ";
            }
            else if($region == 'JKT'){
                $region = "where table3.BranchOffice = 'JKT' or table3.BranchOffice = 'BDO' or table3.BranchOffice = 'TKG'";
            }
            else if($region == 'SUB'){
                $region = "where table3.BranchOffice = 'SRG' or table3.BranchOffice = 'SOC' or table3.BranchOffice = 'MLG' or table3.BranchOffice = 'SUB' or table3.BranchOffice = 'BWX' or table3.BranchOffice = 'JOG'";
            } 
            else if($region == 'DPS'){
                $region = "where table3.BranchOffice = 'DPS' or table3.BranchOffice = 'KOE' or table3.BranchOffice = 'LBJ' or table3.BranchOffice = 'AMI'";
            }
            else if($region == 'UPG'){
                $region = "where table3.BranchOffice = 'GTO' or table3.BranchOffice = 'PNK' or table3.BranchOffice = 'BDJ' or table3.BranchOffice = 'PKY' or table3.BranchOffice = 'BPN' or table3.BranchOffice = 'SRI' or table3.BranchOffice = 'BEJ' or table3.BranchOffice = 'AMQ' or table3.BranchOffice = 'TTE' or table3.BranchOffice = 'DJJ' or table3.BranchOffice = 'MKQ' or table3.BranchOffice = 'TIM' or table3.BranchOffice = 'BIK' or table3.BranchOffice = 'NBX' or table3.BranchOffice = 'SOQ' or table3.BranchOffice = 'NJU' or table3.BranchOffice = 'UPG' or table3.BranchOffice = 'PLW' or table3.BranchOffice = 'KDI' or table3.BranchOffice = 'MDC'";
            }
            else{
                $region="";
            }

            $activebranchOffice = \DB::select(\DB::raw("Select table3.BranchOffice as BranchOffice, table3.Transaksi as Transaksi, table3.Sohib as Sohib, table3.AktifUser as AktifUser  from 
            ( 
                SELECT table2.BranchOffice as BranchOffice, coalesce(table1.transactions,0) as Transaksi, coalesce(table2.Sohib,0) as Sohib, coalesce(table2.ActiveUser,0) as AktifUser FROM
                (
                    
                    select view5.b as BranchOffice, count(view5.b) as transactions 
                    from 
                    ( 
                     
                        select view4.sita as sita, coalesce(nullif(view4.b,''),view4.sita) as b, view4.t as t
                        from 
                        ( 
                            select view3.mid as mid, view3.sita as sita, REPLACE(view3.Branch , 'ALL', 'JKT') as b, view3.Tipe as t
                                from 
                                ( 
                                    select view2.mid as mid, view2.sita as sita, view2.citycode as citycode, coalesce(view2.branchOfc,view2.citycode) as Branch, view2.tipeuser as Tipe
                                    from 
                                     ( 
                                        select  distinct view1.tid as tid, view1.userid as mid, view1.fn as fn, view1.kota as cityname, view1.sitacode as sita, view1.citycode as citycode,  bo.branch_office_area as branchofc, view1.tipe as tipeuser
                                        from 
                                        ( 
                                            select t.id as tid, t.user_id as userid,m.fullname as fn, m.kota as kota,substring(m.department, 1, 3) as sitacode, m.city_code as citycode, m.user_type as tipe
                                            from
                                            trx_booking t, trx_payment p, mst_user m
                                            where  t.id = p.booking_id and p.paid = true and m.id = t.user_id
                                        )view1 	
                                        left join mst_city_branch_office bo
                                        ON TRIM(view1.kota) = TRIM(bo.city_name)
                                        order by view1.userid asc
                                     ) view2
                                )view3
                        )view4
                    )view5
                        
                    group by view5.b
                    order by view5.b
                
                )Table1
                
                RIGHT JOIN 
                
                (
                        SELECT view22.bo2 as BranchOffice, view22.jumlah_sohib as Sohib, view11.jumlah_subconsole as ActiveUser
                        FROM 
                                (
                            
                                select view5.b as bo1, view5.t as tipe, count(view5.t) as jumlah_subconsole
                                from 
                                    ( 
                                     
                                        select view4.sita as sita, coalesce(nullif(view4.b,''),view4.sita) as b, view4.t as t
                                        from 
                                        ( 
                                            select view3.sita as sita, REPLACE(view3.Branch , 'ALL', 'JKT') as b, view3.Tipe as t
                                                from 
                                                ( 
                                                    select view2.sita as sita, view2.citycode, coalesce(view2.branchOfc,view2.citycode) as Branch, view2.tipeuser as Tipe
                                                    from 
                                                     ( 
                                                        select distinct view1.mid, view1.fn, view1.cityname, view1.sitacode as sita, view1.citycode as citycode, view1.branch as branchOfc, view1.tipe as tipeuser
                                                        from 
                                                        (
                                                            select distinct m.id as mid, m.fullname as fn, m.kota, substring(m.department, 1, 3) as sitacode, m.city_code as citycode, bo.city_name as cityname, bo.branch_office_area as branch, bo.region_area as region, m.user_type as tipe
                                                            from mst_city_branch_office bo
                                                            RIGHT JOIN mst_user m
                                                            ON TRIM(m.kota) = TRIM(bo.city_name)
                                                            order by m.id asc
                                                        ) view1 where view1.tipe = 'subconsole'
                                                     ) view2
                                                )view3
                                        )view4
                                    )view5
                                        
                                    group by view5.b, view5.t
                                    order by view5.b
                                ) view11
                
                                RIGHT JOIN 
                                (
                                
                                    select view5.b as bo2, view5.t as tipe, count(view5.t) as jumlah_sohib
                                    from 
                                    ( 
                                     
                                        select view4.sita as sita, coalesce(view4.b,view4.sita) as b, view4.t as t
                                        from 
                                        ( 
                                            select view3.sita as sita, REPLACE(view3.Branch , 'ALL', 'JKT') as b, view3.Tipe as t
                                                from 
                                                ( 
                                                    select view2.sita as sita, view2.citycode, coalesce(view2.branchOfc,view2.citycode) as Branch, view2.tipeuser as Tipe
                                                    from 
                                                     ( 
                                                        select distinct view1.mid, view1.fn, view1.cityname, view1.sitacode as sita, view1.citycode as citycode, view1.branch as branchOfc, view1.tipe as tipeuser
                                                        from 
                                                        (
                                                            select distinct m.id as mid, m.fullname as fn, m.kota, substring(m.department, 1, 3) as sitacode, m.city_code as citycode, bo.city_name as cityname, bo.branch_office_area as branch, bo.region_area as region, m.user_type as tipe
                                                            from mst_city_branch_office bo
                                                            RIGHT JOIN mst_user m
                                                            ON TRIM(m.kota) = TRIM(bo.city_name)
                                                            order by m.id asc
                                                        ) view1 where view1.tipe = 'user'
                                                     ) view2
                                                )view3
                                        )view4
                                    )view5
                                        
                                    group by view5.b, view5.t
                                    order by view5.b
                
                                ) view22 ON view11.bo1 = view22.bo2
                        
                )table2 
                ON table1.BranchOffice = table2.BranchOffice
            ) table3		
            $region
                
            "));
            
            $Response[$regionArray[$i++]] = $activebranchOffice;
           
        }


            // dd($activebranchOffice);
        return response()->json(['data' => $Response]);
    }

    public function perfomanceTransactionUser(Request $request)
    {
          
        $totaluserTransaction = \DB::select(\DB::raw("select 'internal' as tipeUser ,count (view1.tid) as total
        from 
         ( 
            select t.id as tid, t.user_id as userid,m.fullname as fn, m.kota as kota, m.email as email, substring(m.department, 1, 3) as sitacode, m.city_code as citycode, m.user_type as tipe
            from
            trx_booking t, trx_payment p, mst_user m
            where  t.id = p.booking_id and p.paid = true and m.id = t.user_id and m.department is not null
                UNION	
            select t.id as tid, t.user_id as userid,m.fullname as fn, m.kota as kota, m.email as email, substring(m.department, 1, 3) as sitacode, m.city_code as citycode, m.user_type as tipe
            from
            trx_booking t, trx_payment p, mst_user m
            where  t.id = p.booking_id and p.paid = true and m.id = t.user_id and m.department is null and  
            (m.email  like '%@garuda-indonesia.com%' or m.email  like '%@citilink%' or  m.email like '%@aerowisata%' or  m.email like '%@gmf%' or m.email like '%@gapura%')
        ) view1
        union
        select 'external' as tipeUser ,count (view2.tid) as total
        from 
         ( 
            select t.id as tid, t.user_id as userid,m.fullname as fn, m.kota as kota, m.email as email, substring(m.department, 1, 3) as sitacode, m.city_code as citycode, m.user_type as tipe
            from
            trx_booking t, trx_payment p, mst_user m
            where  t.id = p.booking_id and p.paid = true and m.id = t.user_id and m.department is null and 
            (m.email not like '%@garuda-indonesia.com%' and m.email not like '%@citilink%' and  m.email not like '%@aerowisata%' and  m.email not like '%@gmf%' and m.email not like '%@gapura%')
            order by t.id
        ) view2"));
         
        return response()->json(['data' => $totaluserTransaction]);
    }

}
