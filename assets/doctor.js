import './styles/app.scss';

import $ from 'jquery';

// start the Stimulus application
import 'bootstrap';

require('bootstrap-icons/font/bootstrap-icons.css');

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';


$('document').ready(function() {

    const appointments = returnFunc;
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        //initialView: 'timeGridWeek',
        initialView: 'dayGridMonth',
        locale: 'fr',
        timeZone: 'Europe/Paris',
        headerToolbar: {
            start: 'prev,next today',
            center: 'title',
            end: 'dayGridMonth,timeGridWeek',
        },
        buttonText: {
            'today': 'Aujourd\'hui',
            'week': 'Semaine',
            'month': 'Mois'
        },
        events: appointments,
        editable: true, // don't allow event dragging
        eventResizableFromStart: true
    });

    calendar.on('eventChange', (e) => {
        let doctorId = $('#doctor-id').val()
        const appointment = e.event;
        let url = `/doctor/${doctorId}`;
        let datasApp = {
            'id': appointment.id,
            'title': appointment.title,
            'description': appointment.extendedProps.description,
            'start': appointment.start,
            'end': appointment.end,
            'allDay': appointment.allDay,
            'backgroundColor': appointment.backgroundColor,
            'borderColor': appointment.borderColor,
            'textColor': appointment.borderColor
        }
        $.ajax({
            type: "PUT",
            url: url,
            data: datasApp,
            //dataType: "json",
            success: function(data) {
                console.log("success");
            },
            error: function(params) {
                console.error('failed')
            }
        });

    });

    //calendar.render();

    //Notification
    $('#doctor-notif').on('click', function() {
        $('#doctor-list-notif').slideToggle('slow');
        $('#doctor-notif li').on('click', function(e) {
            e.stopPropagation();
        });
        $('#count-doctor-notification').css('display', 'none');
        $('.appointment_info').each(function() {
            $.ajax({
                type: "GET",
                url: "/doctor/read-appointment",
                data: { 'idAppointment': $(this).find('input').val() },
                success: function(data) {
                    $('#count-doctor-notification').text('');
                }
            });
        });
    });

    //Load notification
    setInterval(loadNotification, 20000);
    //Slipe up list menu when click outside of them
    hideNotification();
});

var returnFunc = function() {
    let doctorId = $('#doctor-id')
    let userId = $('#user-id');
    var responseData;
    $.ajax({
        async: false,
        type: "GET",
        url: "/appointment/load/" + $(doctorId).val(),
        data: {
            'patientId': $(userId).val(),
            'doctorId': $(doctorId).val()
        },
        success: function(data) {
            responseData = JSON.parse(data);
        }
    });

    return responseData;
}();


function loadNotification() {

    $.ajax({
        type: "GET",
        url: "/doctor/load-notification",
        //data: "data",
        //dataType: "dataType",
        success: function(data) {
            let response = JSON.parse(JSON.stringify(data));

            if (response.notificationsDoctor != null) {
                if ($('#count-doctor-notification').length > 0) {
                    $('#count-doctor-notification').css('display', 'block');
                    $('#count-doctor-notification').text(response.notificationsDoctor);
                } else {
                    $('#bi-bell-doctor').append('<span id="count-doctor-notification" class="badge badge-danger rounded position-absolute notification">' +
                        response.notificationsDoctor + '</span>');
                }

                $('#doctor-list-notif').append('<li className="appointment_info"><div><span className="font-weight-bold">' +
                    response.newNotification.patientname + '</span> <span>vous a demandé un rendez-vous le ' +
                    response.notificationsDoctor.start + '</span></div></li>');
            }
        }
    });
}

function hideNotification() {
    // Hide dropdown menu on click outside
    $(document).on("click", function(event) {
        if ( !$(event.target).closest("#doctor-notif").length ) {
            $("#doctor-list-notif").slideUp("slow");
        }
    });
}