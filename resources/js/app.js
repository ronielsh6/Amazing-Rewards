import './bootstrap';
import 'jquery'
import 'jquery-toast-plugin'
import swal from 'sweetalert';
import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateSize from 'filepond-plugin-image-preview';
import FilePondPluginImageEdit from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

const inputElement = document.querySelector('input[type="file"].filepond');

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

FilePond.registerPlugin(FilePondPluginImagePreview,
    FilePondPluginImageExifOrientation,
    FilePondPluginFileValidateSize,
    FilePondPluginImageEdit);

FilePond.create(inputElement).setOptions({
        server: {
            process: {
                url: './uploads/process',
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                onload: (response) => {
                    $('.filepond').val(response);
                    console.log(response);
                    document.getElementById("image_links_container").hidden = false;
                },
            },
            revert: {
                url: './uploads/remove',
                method: "DELETE",
                uniqueFieldId: $('.filepond').val(),
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                onload: () => {
                    $('.filepond').val(null);
                    document.getElementById("image_links_container").hidden = true;
                },
            },
        },
        allowMultiple: false,
        credits: false,

    }
);
