<?php

namespace App\Controllers\Admin;

use App\Models\SiswaModel;
use App\Models\KelasModel;

use App\Controllers\BaseController;
use App\Models\JurusanModel;
use App\Models\UploadModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class DataSiswa extends BaseController
{
   protected SiswaModel $siswaModel;
   protected KelasModel $kelasModel;
   protected JurusanModel $jurusanModel;

   protected $siswaValidationRules = [
      'nis' => [
         'rules' => 'required|max_length[20]|min_length[4]',
         'errors' => [
            'required' => 'NIS harus diisi.',
            'is_unique' => 'NIS ini telah terdaftar.',
            'min_length[4]' => 'Panjang NIS minimal 4 karakter'
         ]
      ],
      'nama' => [
         'rules' => 'required|min_length[3]',
         'errors' => [
            'required' => 'Nama harus diisi'
         ]
      ],
      'id_kelas' => [
         'rules' => 'required',
         'errors' => [
            'required' => 'Kelas harus diisi'
         ]
      ],
      'jk' => ['rules' => 'required', 'errors' => ['required' => 'Jenis kelamin wajib diisi']],
      'no_hp' => 'required|numeric|max_length[20]|min_length[5]'
   ];

   public function __construct()
   {
      $this->siswaModel = new SiswaModel();
      $this->kelasModel = new KelasModel();
      $this->jurusanModel = new JurusanModel();
   }

   public function index()
   {
      $data = [
         'title' => 'Data Siswa',
         'ctx' => 'siswa',
         'kelas' => $this->kelasModel->getDataKelas(),
         'jurusan' => $this->jurusanModel->getDataJurusan()
      ];

      return view('admin/data/data-siswa', $data);
   }

   public function ambilDataSiswa()
   {
      $kelas = $this->request->getVar('kelas') ?? null;
      $jurusan = $this->request->getVar('jurusan') ?? null;

      $result = $this->siswaModel->getAllSiswaWithKelas($kelas, $jurusan);

      $data = [
         'data' => $result,
         'empty' => empty($result)
      ];

      return view('admin/data/list-data-siswa', $data);
   }

   public function formTambahSiswa()
   {
      $kelas = $this->kelasModel->getDataKelas();

      $data = [
         'ctx' => 'siswa',
         'kelas' => $kelas,
         'title' => 'Tambah Data Siswa'
      ];

      return view('admin/data/create/create-data-siswa', $data);
   }

   public function saveSiswa()
   {
      $validationRules = [
         'nis' => 'required|is_unique[tb_siswa.nis]',
         'nama' => 'required',
         'id_kelas' => 'required|integer',
         'jk' => 'required',
         'no_hp' => 'required',
         'foto' => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
      ];

      if (!$this->validate($validationRules)) {
         return view('/admin/data/create/create-data-siswa', [
            'ctx' => 'siswa',
            'kelas' => $this->kelasModel->getDataKelas(),
            'title' => 'Tambah Data Siswa',
            'validation' => $this->validator,
            'oldInput' => $this->request->getVar()
         ]);
      }

      $fileFoto = $this->request->getFile('foto');
      if ($fileFoto && !$fileFoto->hasMoved()) {
         $namaFoto = $fileFoto->getRandomName();
         $fileFoto->move('uploads/siswa', $namaFoto);
      } else {
         $namaFoto = null;
      }

      $result = $this->siswaModel->createSiswa(
         $this->request->getVar('nis'),
         $this->request->getVar('nama'),
         intval($this->request->getVar('id_kelas')),
         $this->request->getVar('jk'),
         $this->request->getVar('no_hp'),
         $namaFoto
      );

      if ($result) {
         session()->setFlashdata('msg', 'Tambah data berhasil');
         return redirect()->to('/admin/siswa');
      }

      session()->setFlashdata('msg', 'Gagal menambah data');
      return redirect()->to('/admin/siswa/create');
   }

   public function formEditSiswa($id)
   {
      $siswa = $this->siswaModel->getSiswaById($id);
      $kelas = $this->kelasModel->getDataKelas();

      if (empty($siswa) || empty($kelas)) {
         throw new PageNotFoundException('Data siswa dengan id ' . $id . ' tidak ditemukan');
      }

      $data = [
         'data' => $siswa,
         'kelas' => $kelas,
         'ctx' => 'siswa',
         'title' => 'Edit Siswa',
      ];

      return view('admin/data/edit/edit-data-siswa', $data);
   }

   public function updateSiswa()
   {
      $idSiswa = $this->request->getVar('id');

      $siswaLama = $this->siswaModel->getSiswaById($idSiswa);

      if ($siswaLama['nis'] != $this->request->getVar('nis')) {
         $this->siswaValidationRules['nis']['rules'] = 'required|max_length[20]|min_length[4]|is_unique[tb_siswa.nis]';
      }

      $this->siswaValidationRules['foto'] = [
         'rules' => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
         'errors' => [
            'max_size' => 'Ukuran foto maksimal 2MB.',
            'is_image' => 'File yang diunggah harus berupa gambar.',
            'mime_in' => 'Format gambar harus jpg, jpeg, atau png.'
         ]
      ];

      if (!$this->validate($this->siswaValidationRules)) {
         $siswa = $this->siswaModel->getSiswaById($idSiswa);
         $kelas = $this->kelasModel->getDataKelas();

         return view('/admin/data/edit/edit-data-siswa', [
            'data' => $siswa,
            'kelas' => $kelas,
            'ctx' => 'siswa',
            'title' => 'Edit Siswa',
            'validation' => $this->validator,
            'oldInput' => $this->request->getVar()
         ]);
      }

      $fileFoto = $this->request->getFile('foto');
      if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
         $namaFoto = $fileFoto->getRandomName();

         $fileFoto->move('uploads/siswa', $namaFoto);

         if ($siswaLama['foto'] && file_exists('uploads/siswa/' . $siswaLama['foto'])) {
            unlink('uploads/siswa/' . $siswaLama['foto']);
         }
      } else {
         $namaFoto = $siswaLama['foto'];
      }

      $result = $this->siswaModel->updateSiswa(
         id: $idSiswa,
         nis: $this->request->getVar('nis'),
         nama: $this->request->getVar('nama'),
         idKelas: intval($this->request->getVar('id_kelas')),
         jenisKelamin: $this->request->getVar('jk'),
         noHp: $this->request->getVar('no_hp'),
         foto: $namaFoto 
      );

      if ($result) {
         session()->setFlashdata('msg', 'Edit data berhasil');
         return redirect()->to('/admin/siswa');
      }

      session()->setFlashdata('msg', 'Gagal mengubah data');
      return redirect()->to('/admin/siswa/edit/' . $idSiswa);
   }

   public function delete($id)
   {
      $result = $this->siswaModel->delete($id);

      if ($result) {
         session()->setFlashdata([
            'msg' => 'Data berhasil dihapus',
            'error' => false
         ]);
         return redirect()->to('/admin/siswa');
      }

      session()->setFlashdata([
         'msg' => 'Gagal menghapus data',
         'error' => true
      ]);
      return redirect()->to('/admin/siswa');
   }

   /**
    * Delete Selected Posts
    */
   public function deleteSelectedSiswa()
   {
      $siswaIds = inputPost('siswa_ids');
      $this->siswaModel->deleteMultiSelected($siswaIds);
   }

   /*
    *-------------------------------------------------------------------------------------------------
    * IMPORT SISWA
    *-------------------------------------------------------------------------------------------------
    */

   /**
    * Bulk Post Upload
    */
   public function bulkPostSiswa()
   {
      $data['title'] = 'Import Data Siswa';
      $data['ctx'] = 'siswa';
      $data['kelas'] = $this->kelasModel->getDataKelas();

      return view('/admin/data/import-siswa', $data);
   }

   /**
    * Generate CSV Object Post
    */
   public function generateCSVObjectPost()
   {
      $uploadModel = new UploadModel();
      //delete old txt files
      $files = glob(FCPATH . 'uploads/tmp/*.txt');
      if (!empty($files)) {
         foreach ($files as $item) {
            @unlink($item);
         }
      }
      $file = $uploadModel->uploadCSVFile('file');
      if (!empty($file) && !empty($file['path'])) {
         $obj = $this->siswaModel->generateCSVObject($file['path']);
         if (!empty($obj)) {
            $data = [
               'result' => 1,
               'numberOfItems' => $obj->numberOfItems,
               'txtFileName' => $obj->txtFileName,
            ];
            echo json_encode($data);
            exit();
         }
      }
      echo json_encode(['result' => 0]);
   }

   /**
    * Import CSV Item Post
    */
   public function importCSVItemPost()
   {
      $txtFileName = inputPost('txtFileName');
      $index = inputPost('index');
      $siswa = $this->siswaModel->importCSVItem($txtFileName, $index);
      if (!empty($siswa)) {
         $data = [
            'result' => 1,
            'siswa' => $siswa,
            'index' => $index
         ];
         echo json_encode($data);
      } else {
         $data = [
            'result' => 0,
            'index' => $index
         ];
         echo json_encode($data);
      }
   }

   /**
    * Download CSV File Post
    */
   public function downloadCSVFilePost()
   {
      $submit = inputPost('submit');
      $response = \Config\Services::response();
      if ($submit == 'csv_siswa_template') {
         return $response->download(FCPATH . 'assets/file/csv_siswa_template.csv', null);
      } elseif ($submit == 'csv_guru_template') {
         return $response->download(FCPATH . 'assets/file/csv_guru_template.csv', null);
      }
   }
   /**
    * Import ZIP File
    */
   public function importZip()
   {
      $data = [
         'title' => 'Import Foto Siswa',
         'ctx' => 'siswa'
      ];

      return view('admin/data/import-zip', $data);
   }

   public function processImportZip()
   {
      $file = $this->request->getFile('zip_file');

      if ($file && $file->isValid() && !$file->hasMoved()) {
         try {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);

            $zip = new \ZipArchive();
            if ($zip->open(WRITEPATH . 'uploads/' . $newName) === TRUE) {
               $extractPath = WRITEPATH . 'uploads/photos/';
               $zip->extractTo($extractPath);
               $zip->close();

               $targetPath = FCPATH . 'uploads/siswa/';
               if (!is_dir($targetPath)) {
                  mkdir($targetPath, 0755, true);
                  log_message('error', 'Created directory: ' . $targetPath);
               }

               $files = scandir($extractPath);
               $successfulUploads = 0;
               $failedUploads = 0;

               foreach ($files as $photo) {
                  if (in_array(pathinfo($photo, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
                     $nis = pathinfo($photo, PATHINFO_FILENAME);
                     $siswa = $this->siswaModel->getSiswaByNIS($nis);
                     log_message('error', 'Siswa found: ' . print_r($siswa, true));
                     if (isset($siswa['id_siswa'])) {
                        $extension = pathinfo($photo, PATHINFO_EXTENSION);
                        $newFileName = $nis . '.' . $extension;

                        $sourcePath = $extractPath . $photo;
                        $destinationPath = FCPATH . 'uploads/siswa/' . $newFileName;

                        log_message('error', 'Source Path: ' . $sourcePath);
                        log_message('error', 'Destination Path: ' . $destinationPath);

                        if (file_exists($sourcePath)) {
                           log_message('error', 'File exists at source: ' . $sourcePath);
                        } else {
                           log_message('error', 'Source file does not exist: ' . $sourcePath);
                        }

                        if (rename($sourcePath, $destinationPath)) {
                           $this->siswaModel->updateFotoSiswa($siswa['id_siswa'], $newFileName);
                           $successfulUploads++;
                        } else {
                           log_message('error', 'Failed to move file: ' . $sourcePath);
                           $failedUploads++;
                        }
                     } else {
                        log_message('error', 'Student ID not found for NIS: ' . $nis);
                        $failedUploads++;
                     }
                  }
               }

               if ($successfulUploads > 0) {
                  session()->setFlashdata('msg', 'Import foto berhasil! ' . $successfulUploads . ' foto berhasil diupload.');
               }

               if ($failedUploads > 0) {
                  session()->setFlashdata('msg', $failedUploads . ' foto gagal diupload karena siswa tidak ditemukan atau ada kesalahan.');
               }
            } else {
               session()->setFlashdata('msg', 'Gagal membuka file ZIP.');
               log_message('error', 'Gagal membuka file ZIP: ' . $newName);
            }
         } catch (\Exception $e) {
            session()->setFlashdata('msg', 'Terjadi kesalahan saat memproses file ZIP.');
            log_message('error', 'Error during ZIP file processing: ' . $e->getMessage());
         }
      } else {
         session()->setFlashdata('msg', 'File tidak valid.');
         log_message('error', 'File ZIP tidak valid atau gagal diupload.');
      }

      return redirect()->to('/admin/siswa/import-zip');
   }
}
