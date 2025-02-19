<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6 col-xl-5">
                <?= view('admin/_messages'); ?>
                <div class="card p-3 mb-3">
                    <div class="card-header card-header-tabs card-header-primary">
                        <h4 class="card-title"><b>Upload ZIP File</b></h4>
                        <p class="card-category">Upload file ZIP yang berisi foto siswa dengan nama file berupa NIS</p>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('admin/siswa/import-zip') ?>" method="post" enctype="multipart/form-data" id="zipUploadForm">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <div class="dm-uploader-container">
                                    <div id="drag-and-drop-zone" class="dm-uploader p-2" style="height: 200px; border: 2px dashed #ccc; text-align: center;">
                                        <p class="dm-upload-icon">
                                            <i class="material-icons" style="font-size: 48px; color: #888;">cloud_upload</i>
                                        </p>
                                        <h5 class="text-muted">Drag & drop files here</h5>
                                        <div class="btn btn-primary mt-2">
                                            <span>Open the file Browser</span>
                                            <input type="file" name="zip_file" title="Click to add Files" accept=".zip" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="csv-upload-spinner text-center mt-3" style="display: none;">
                                    <strong class="text-csv-importing">Importing ZIP...</strong>
                                    <div class="spinner-border text-primary mt-2" role="status"></div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3" id="submitBtn" disabled>Upload</button>
                        </form>
                    </div>
                </div>
                <?php if (session()->getFlashdata('msg')): ?>
                    <div class="alert alert-info mt-2"><?= session()->getFlashdata('msg') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('input[name="zip_file"]').addEventListener('change', function() {
        document.getElementById('submitBtn').disabled = !this.files.length;
    });

    document.getElementById('zipUploadForm').addEventListener('submit', function() {
        document.querySelector('.csv-upload-spinner').style.display = 'block';
    });

    const dropZone = document.getElementById('drag-and-drop-zone');
    const fileInput = document.querySelector('input[name="zip_file"]');

    dropZone.addEventListener('dragover', function(event) {
        event.preventDefault();
        dropZone.style.border = "2px solid #007bff";
    });

    dropZone.addEventListener('dragleave', function() {
        dropZone.style.border = "2px dashed #ccc";
    });

    dropZone.addEventListener('drop', function(event) {
        event.preventDefault();
        dropZone.style.border = "2px dashed #ccc";
        if (event.dataTransfer.files.length > 0) {
            fileInput.files = event.dataTransfer.files;
            document.getElementById('submitBtn').disabled = false;
        }
    });
</script>

<?= $this->endSection() ?>