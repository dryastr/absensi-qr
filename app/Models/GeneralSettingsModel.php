<?php

namespace App\Models;

use CodeIgniter\Model;

class GeneralSettingsModel extends Model
{
   protected $table = 'general_settings';
   protected $primaryKey = 'id';
   protected $allowedFields = ['school_name', 'school_year', 'token_api', 'copyright', 'logo'];
   protected $returnType = 'object'; // Bisa diubah ke 'array' jika ingin akses pakai ['token_api']
   
   public function getSettings()
   {
       return $this->first();
   }
   public function inputValues()
   {
      $request = service('request');
      return [
         'school_name' => $request->getPost('school_name'),
         'school_year' => $request->getPost('school_year'),
         'token_api' => $request->getPost('token_api'),
         'copyright' => $request->getPost('copyright'),
      ];
   }

   public function updateSettings()
   {
      $data = $this->inputValues();

      // Periksa apakah model UploadModel ada
      if (!class_exists('\App\Models\UploadModel')) {
         throw new \Exception("Model UploadModel tidak ditemukan.");
      }

      $uploadModel = new \App\Models\UploadModel();
      $logoPath = $uploadModel->uploadLogo('logo');

      if (!empty($logoPath) && !empty($logoPath['path'])) {
         $oldLogo = $this->where('id', 1)->first()->logo;
         $data['logo'] = $logoPath['path'];
         if (!empty($oldLogo) && file_exists($oldLogo)) {
            @unlink($oldLogo);
         }
      }

      return $this->update(1, $data);
   }
}
