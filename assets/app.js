/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import $ from 'jquery';

// start the Stimulus application
import 'bootstrap';


import 'select2'; // globally assign select2 fn to $ element
import 'select2/dist/css/select2.css';

/* import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid'; */

$('document').ready(function() {
    $('.select-language').select2();
    $("input[type=file]").on('change', function(e) {
        previewFile(this);
    });

    showHideDoctorInfo();

    // document.addEventListener('DOMContentLoaded', function() {
    /* var calendarEl = document.getElementById('calendar');

    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin]
    });

    calendar.render(); */
    //});

    //Close modal
    $('#btn-close-modal-request-rdv').click(function() {
        console.log("hereeee");
        $('#btn-close-modal-close-rdv').click();
    });

});

function previewFile(input) {
    var file = $("input[type=file]").get(0).files[0];

    if (file) {
        var reader = new FileReader();

        reader.onload = function() {
            $("#previewImg").attr("src", reader.result);
        }

        reader.readAsDataURL(file);
    }
    //let input = e.currentTarget;
    $(input).parent().find('.custom-file-label').html(input.files[0].name);
}

function showHideDoctorInfo() {
    let checkBox = $("#is_doctor")
    $("#is_doctor").on('click', function() {
        if ($(this).prop("checked") == true) {
            $('.doctor_info').removeClass('d-none');
        } else if ($(this).prop("checked") == false) {
            $('.doctor_info').addClass('d-none');
        }
    });
}