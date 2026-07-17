<?php

namespace App\Controllers;

use App\Models\BarangModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $svc = service('dashboard');

        $menipis = $svc->getBarangMenipis();
        $terbaru = $svc->getAktivitasTerbaru();

        return view('dashboard/index', [
            'title'   => 'Dashboard',
            'menipis' => $menipis,
            'terbaru' => $terbaru,
        ]);
    }

    public function chartKpi()
    {
        return $this->response->setJSON(service('dashboard')->getStatistikKpi());
    }

    public function chartStatusStok()
    {
        return $this->response->setJSON(service('dashboard')->getStatusStok());
    }

    public function chartStokKategori()
    {
        return $this->response->setJSON(service('dashboard')->getStokKategori());
    }

    public function chartTrenTransaksi()
    {
        return $this->response->setJSON(service('dashboard')->getTrenTransaksi());
    }
}
