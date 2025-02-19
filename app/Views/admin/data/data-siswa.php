<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <?php if (session()->getFlashdata('msg')) : ?>
               <div class="pb-2 px-3">
                  <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success'  ?> ">
                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <i class="material-icons">close</i>
                     </button>
                     <?= session()->getFlashdata('msg') ?>
                  </div>
               </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
               <div>
                  <a class="btn btn-primary mr-2 py-3" href="<?= base_url('admin/siswa/create'); ?>">
                     <i class="material-icons mr-2">add</i> Tambah data siswa
                  </a>
                  <a class="btn btn-primary mr-2 py-3" href="<?= base_url('admin/siswa/bulk'); ?>">
                     <i class="material-icons mr-2">upload_file</i> Import CSV
                  </a>
                  <a class="btn btn-primary mr-2 py-3" href="<?= base_url('admin/siswa/import-zip'); ?>">
                     <i class="material-icons mr-2">folder_zip</i> Import ZIP
                  </a>
                  <button class="btn btn-danger py-3 btn-table-delete" onclick="deleteSelectedSiswa('Data yang sudah dihapus tidak bisa dikembalikan');">
                     <i class="material-icons mr-2">delete_forever</i> Delete All
                  </button>
               </div>
               <div class="input-group" style="width: 300px;">
                  <input type="text" class="form-control" id="searchSiswa" placeholder="Cari siswa..." onkeyup="searchSiswa()">
                  <div class="input-group-append">
                     <span class="input-group-text">
                        <i class="material-icons">search</i>
                     </span>
                  </div>
               </div>
            </div>

            <div class="card">
               <div class="card-header card-header-tabs card-header-primary">
                  <div class="nav-tabs-navigation">
                     <div class="row">
                        <div class="col-md-2">
                           <h4 class="card-title"><b>Daftar Siswa</b></h4>
                           <p class="card-category">Angkatan <?= $generalSettings->school_year; ?></p>
                        </div>
                        <div class="col-md-4">
                           <div class="nav-tabs-wrapper">
                              <span class="nav-tabs-title">Kelas:</span>
                              <ul class="nav nav-tabs" data-tabs="tabs">
                                 <li class="nav-item">
                                    <a class="nav-link active" onclick="kelas = null; trig()" href="#" data-toggle="tab">
                                       <i class="material-icons">check</i> Semua
                                       <div class="ripple-container"></div>
                                    </a>
                                 </li>
                                 <?php
                                 $tempKelas = [];
                                 foreach ($kelas as $value) : ?>
                                    <?php if (!in_array($value['kelas'], $tempKelas)) : ?>
                                       <li class="nav-item">
                                          <a class="nav-link" onclick="kelas = '<?= $value['kelas']; ?>'; trig()" href="#" data-toggle="tab">
                                             <i class="material-icons">school</i> <?= $value['kelas']; ?>
                                             <div class="ripple-container"></div>
                                          </a>
                                       </li>
                                       <?php array_push($tempKelas, $value['kelas']) ?>
                                    <?php endif; ?>
                                 <?php endforeach; ?>
                              </ul>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="nav-tabs-wrapper">
                              <span class="nav-tabs-title">Jurusan:</span>
                              <ul class="nav nav-tabs" data-tabs="tabs">
                                 <li class="nav-item">
                                    <a class="nav-link active" onclick="jurusan = null; trig()" href="#" data-toggle="tab">
                                       <i class="material-icons">check</i> Semua
                                       <div class="ripple-container"></div>
                                    </a>
                                 </li>
                                 <?php foreach ($jurusan as $value) : ?>
                                    <li class="nav-item">
                                       <a class="nav-link" onclick="jurusan = '<?= $value['jurusan']; ?>'; trig();" href="#" data-toggle="tab">
                                          <i class="material-icons">work</i> <?= $value['jurusan']; ?>
                                          <div class="ripple-container"></div>
                                       </a>
                                    </li>
                                 <?php endforeach; ?>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div id="dataSiswa">
                  <p class="text-center mt-3">Daftar siswa muncul disini</p>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
   var kelas = null;
   var jurusan = null;

   getDataSiswa(kelas, jurusan);

   function trig() {
      getDataSiswa(kelas, jurusan);
   }

   function getDataSiswa(_kelas = null, _jurusan = null, search = '') {
      jQuery.ajax({
         url: "<?= base_url('/admin/siswa'); ?>",
         type: 'post',
         data: {
            'kelas': _kelas,
            'jurusan': _jurusan,
            'search': search
         },
         success: function(response) {
            $('#dataSiswa').html(response);

            $('html, body').animate({
               scrollTop: $("#dataSiswa").offset().top
            }, 500);
         },
         error: function(xhr, status, thrown) {
            console.log(thrown);
            $('#dataSiswa').html(thrown);
         }
      });
   }

   function searchSiswa() {
      var searchValue = document.getElementById('searchSiswa').value;
      getDataSiswa(kelas, jurusan, searchValue);
   }

   document.addEventListener('DOMContentLoaded', function() {
      $("#checkAll").click(function() {
         $('input:checkbox').not(this).prop('checked', this.checked);
      });
   });
</script>

<?= $this->endSection() ?>
