<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SLA Penanganan Pengaduan
    |--------------------------------------------------------------------------
    |
    | Jumlah hari kerja (Senin-Jumat) sejak pengaduan dibuat atau didisposisi
    | sebelum pengaduan dianggap melewati batas waktu dan dieskalasi.
    |
    */

    'sla_working_days' => (int) env('WADULIN_SLA_WORKING_DAYS', 3),

];
