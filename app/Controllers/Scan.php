<?php

namespace App\Controllers;

use App\Helpers\WhatsAppHelper;
use CodeIgniter\I18n\Time;
use App\Models\GuruModel;
use App\Models\SiswaModel;
use App\Models\PresensiGuruModel;
use App\Models\PresensiSiswaModel;
use App\Libraries\enums\TipeUser;

class Scan extends BaseController
{
    protected SiswaModel $siswaModel;
    protected GuruModel $guruModel;
    protected WhatsAppHelper $whatsAppHelper;
    protected PresensiSiswaModel $presensiSiswaModel;
    protected PresensiGuruModel $presensiGuruModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->guruModel = new GuruModel();
        $this->presensiSiswaModel = new PresensiSiswaModel();
        $this->presensiGuruModel = new PresensiGuruModel();
        $this->whatsAppHelper = new WhatsAppHelper();
    }

    public function index($t = 'Masuk')
    {
        $data = ['waktu' => $t, 'title' => 'Absensi Siswa SMA Negeri 1 Jakenan'];
        return view('scan/scan', $data);
    }

    public function cekKode()
    {
        // Ambil variabel POST
        $uniqueCode = $this->request->getVar('unique_code');
        $waktuAbsen = $this->request->getVar('waktu');

        $status = false;
        $type = TipeUser::Siswa;

        // Cek apakah kode milik siswa
        $result = $this->siswaModel->cekSiswa($uniqueCode);

        if (!$result) {
            // Jika bukan siswa, cek apakah kode milik guru
            $result = $this->guruModel->cekGuru($uniqueCode);

            if ($result) {
                $status = true;
                $type = TipeUser::Guru;
            }
        } else {
            $status = true;
        }

        if (!$status) {
            return $this->showErrorView('Data tidak ditemukan');
        }

        // Menentukan jenis absensi (Masuk / Pulang)
        switch (strtolower($waktuAbsen)) {
            case 'masuk':
                return $this->absenMasuk($type, $result);
            case 'pulang':
                return $this->absenPulang($type, $result);
            default:
                return $this->showErrorView('Data tidak valid');
        }
    }

    private function absenMasuk($type, $result)
    {
        $data = ['data' => $result, 'waktu' => 'masuk'];
        $date = Time::today()->toDateString();
        $time = Time::now()->toTimeString();

        if ($type == TipeUser::Guru) {
            $idGuru = $result['id_guru'];
            $data['type'] = TipeUser::Guru;

            if ($this->presensiGuruModel->cekAbsen($idGuru, $date)) {
                return $this->showErrorView('Anda sudah absen masuk hari ini', $data);
            }

            $this->presensiGuruModel->absenMasuk($idGuru, $date, $time);
            $data['presensi'] = $this->presensiGuruModel->getPresensiByIdGuruTanggal($idGuru, $date);
        } else {
            $idSiswa = $result['id_siswa'];
            $idKelas = $result['id_kelas'];
            $data['type'] = TipeUser::Siswa;

            if ($this->presensiSiswaModel->cekAbsen($idSiswa, $date)) {
                return $this->showErrorView('Anda sudah absen masuk hari ini', $data);
            }

            $this->presensiSiswaModel->absenMasuk($idSiswa, $date, $time, $idKelas);
            $data['presensi'] = $this->presensiSiswaModel->getPresensiByIdSiswaTanggal($idSiswa, $date);

            // Kirim pesan WhatsApp ke orang tua
            $message = "Siswa {$result['nama_siswa']} telah absen masuk.";
            $this->whatsAppHelper->sendMessage($result['no_hp'], $message);
        }

        return view('scan/scan-result', $data);
    }

    private function absenPulang($type, $result)
    {
        $data = ['data' => $result, 'waktu' => 'pulang'];
        $date = Time::today()->toDateString();
        $time = Time::now()->toTimeString();

        if ($type == TipeUser::Guru) {
            $idGuru = $result['id_guru'];
            $data['type'] = TipeUser::Guru;

            $sudahAbsen = $this->presensiGuruModel->cekAbsen($idGuru, $date);
            if (!$sudahAbsen) {
                return $this->showErrorView('Anda belum absen masuk hari ini', $data);
            }

            $this->presensiGuruModel->absenKeluar($sudahAbsen, $time);
            $data['presensi'] = $this->presensiGuruModel->getPresensiById($sudahAbsen);
        } else {
            $idSiswa = $result['id_siswa'];
            $data['type'] = TipeUser::Siswa;

            $sudahAbsen = $this->presensiSiswaModel->cekAbsen($idSiswa, $date);
            if (!$sudahAbsen) {
                return $this->showErrorView('Anda belum absen masuk hari ini', $data);
            }

            $this->presensiSiswaModel->absenKeluar($sudahAbsen, $time);
            $data['presensi'] = $this->presensiSiswaModel->getPresensiById($sudahAbsen);

            // Kirim pesan WhatsApp ke orang tua
            $message = "Siswa {$result['nama_siswa']} telah absen pulang.";
            $this->whatsAppHelper->sendMessage($result['no_hp'], $message);
        }

        return view('scan/scan-result', $data);
    }

    private function showErrorView($msg = 'Terjadi kesalahan', $data = [])
    {
        $data['msg'] = $msg;
        return view('scan/error-scan-result', $data);
    }
}
