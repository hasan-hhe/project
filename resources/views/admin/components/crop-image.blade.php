<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'modal' }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">قص الصورة</h5>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <div class="row" dir="ltr">
                        <div class="col-md-8">
                            <img src="" id="{{ isset($viewImageModal) ? $viewImageModal : 'sample_image' }}" />
                        </div>
                        <div class="col-md-4">
                            <div class="preview {{ isset($prev) ? $prev : 'prev' }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="{{ isset($crop) ? $crop : 'crop' }}" class="btn btn-primary">قص</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/dropzone/dist/dropzone.css" />
<link href="https://unpkg.com/cropperjs/dist/cropper.css" rel="stylesheet" />

<style>
  .image_area {
      position: relative;
  }

  img {
      display: block;
      max-width: 100%;
  }

  .preview {
      overflow: hidden;
      width: 160px;
      height: 160px;
      margin: 10px;
      border: 1px solid red;
  }

  .modal {
      z-index: 9999999;
  }

  .modal-lg {
      max-width: 1000px !important;
  }

  .overlay {
      position: absolute;
      bottom: 10px;
      left: 0;
      right: 0;
      background-color: rgba(255, 255, 255, 0.5);
      overflow: hidden;
      height: 0;
      transition: .5s ease;
      width: 100%;
  }

  .image_area:hover .overlay {
      height: 50%;
      cursor: pointer;
  }

  .text {
      color: #333;
      font-size: 20px;
      position: absolute;
      top: 50%;
      left: 50%;
      -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
      text-align: center;
  }
</style>
@endpush

@push('scripts')
<script>
  $(document).ready(function() {

      var modal = $("#{{ isset($modalId) ? $modalId : 'modal' }}");

      var image = document.getElementById("{{ isset($viewImageModal) ? $viewImageModal : 'sample_image' }}");

      var cropper;

      $("#{{ isset($uploadImage) ? $uploadImage : 'upload_image' }}").change(function(event) {
          var files = event.target.files;

          function done(url) {
              image.src = url;
              modal.modal('show');
          };

          if (files && files.length > 0) {
              reader = new FileReader();
              reader.onload = function(event) {
                  done(reader.result);
              };
              reader.readAsDataURL(files[0]);
          }
      });

      modal.on('shown.bs.modal', function() {
          cropper = new Cropper(image, {
              aspectRatio: {{ isset($aspectRatio) ? $aspectRatio : 1 }} {{ isset($aspectRatio1) ? '/' : '' }} {{ isset($aspectRatio1) ? $aspectRatio1 : '' }},
              viewMode: {{ isset($viewMode) ? $viewMode : 3 }},
              preview: ".{{ isset($prev) ? $prev : 'prev' }}"
          });
      }).on('hidden.bs.modal', function() {
          cropper.destroy();
          cropper = null;
      });

      $("#{{ isset($crop) ? $crop : 'crop' }}").click(function() {
          canvas = cropper.getCroppedCanvas({
              width: parseFloat("{{ isset($width) ? $width : '500' }}"),
              height: parseFloat("{{ isset($height) ? $height : '500' }}")
          });

          canvas.toBlob(function(blob) {
              url = URL.createObjectURL(blob);
              var reader = new FileReader();
              reader.readAsDataURL(blob);
              reader.onloadend = function() {
                  var base64data = reader.result;
                  $('#{{$viewImage}}').attr('src', base64data);
                  $('#{{$form}}').attr('value', base64data);
                  modal.modal('hide');
              };
          });
      });

  });
</script>
<script src="https://unpkg.com/dropzone"></script>
<script src="https://unpkg.com/cropperjs"></script>
@endpush