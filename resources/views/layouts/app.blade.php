<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/app_icon.png') }}">
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />

    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    @yield('additional-styles')

    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <link id="pagestyle" href="{{ asset('assets/css/material-dashboard.css?v=3.0.4') }}" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    @vite(['resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}" ></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}" ></script>
    <script src="{{ asset('assets/js/general.js') }}" ></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css">

    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.jsdelivr.net/npm/bootstrap-confirmation2/dist/bootstrap-confirmation.min.js"></script>
</head>
<body class="g-sidenav-show  bg-gray-100">
    <div id="app">
        @yield('aside-bar')

        <main class="main-content border-radius-lg ">
            @yield('header')
            @yield('content')
        </main>
    </div>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
          var options = {
            damping: '0.5'
          }
          Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

        let getCsrfToken = function() {
          return '{{ csrf_token() }}';
        }

        let ezBSAlert = function(options) {
            var deferredObject = $.Deferred();
            var defaults = {
                type: "alert", //alert, prompt,confirm
                modalSize: 'modal-sm', //modal-sm, modal-lg
                okButtonText: 'Ok',
                cancelButtonText: 'Cancel',
                yesButtonText: 'Yes',
                noButtonText: 'No',
                headerText: 'Attention',
                messageText: 'Message',
                alertType: 'default', //default, primary, success, info, warning, danger
                inputFieldType: 'text', //could ask for number,email,etc
            }
            $.extend(defaults, options);

            var _show = function(){
                var headClass = "navbar-default";
                switch (defaults.alertType) {
                    case "primary":
                        headClass = "alert-primary";
                        break;
                    case "success":
                        headClass = "alert-success";
                        break;
                    case "info":
                        headClass = "alert-info";
                        break;
                    case "warning":
                        headClass = "alert-warning";
                        break;
                    case "danger":
                        headClass = "alert-danger";
                        break;
                }
                $('BODY').append(
                    '<div id="ezAlerts" class="modal fade">' +
                    '<div class="modal-dialog" class="' + defaults.modalSize + '">' +
                    '<div class="modal-content">' +
                    '<div id="ezAlerts-header" class="modal-header ' + headClass + '">' +
                    '<h4 id="ezAlerts-title" class="modal-title">Modal title</h4>' +
                    '</div>' +
                    '<div id="ezAlerts-body" class="modal-body">' +
                    '<div id="ezAlerts-message" ></div>' +
                    '</div>' +
                    '<div id="ezAlerts-footer" class="modal-footer">' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );

                $('.modal-header').css({
                    'padding': '15px 15px',
                    '-webkit-border-top-left-radius': '5px',
                    '-webkit-border-top-right-radius': '5px',
                    '-moz-border-radius-topleft': '5px',
                    '-moz-border-radius-topright': '5px',
                    'border-top-left-radius': '5px',
                    'border-top-right-radius': '5px'
                });

                $('#ezAlerts-title').text(defaults.headerText);
                $('#ezAlerts-message').html(defaults.messageText);

                var keyb = "false", backd = "static";
                var calbackParam = "";
                switch (defaults.type) {
                    case 'alert':
                        keyb = "true";
                        backd = "true";
                        $('#ezAlerts-footer').html('<button class="btn btn-' + defaults.alertType + '">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
                            calbackParam = true;
                            $('#ezAlerts').modal('hide');
                        });
                        break;
                    case 'confirm':
                        var btnhtml = '<button id="ezok-btn" class="btn btn-primary">' + defaults.yesButtonText + '</button>';
                        if (defaults.noButtonText && defaults.noButtonText.length > 0) {
                            btnhtml += '<button id="ezclose-btn" class="btn btn-default">' + defaults.noButtonText + '</button>';
                        }
                        $('#ezAlerts-footer').html(btnhtml).on('click', 'button', function (e) {
                            if (e.target.id === 'ezok-btn') {
                                calbackParam = true;
                                $('#ezAlerts').modal('hide');
                            } else if (e.target.id === 'ezclose-btn') {
                                calbackParam = false;
                                $('#ezAlerts').modal('hide');
                            }
                        });
                        break;
                    case 'prompt':
                        $('#ezAlerts-message').html(defaults.messageText + '<br /><br /><div class="form-group"><input type="' + defaults.inputFieldType + '" class="form-control" id="prompt" /></div>');
                        $('#ezAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
                            calbackParam = $('#prompt').val();
                            $('#ezAlerts').modal('hide');
                        });
                        break;
                }

                $('#ezAlerts').modal({
                    show: false,
                    backdrop: backd,
                    keyboard: false
                }).on('hidden.bs.modal', function (e) {
                    $('#ezAlerts').remove();
                    deferredObject.resolve(calbackParam);
                }).on('shown.bs.modal', function (e) {
                    if ($('#prompt').length > 0) {
                        $('#prompt').focus();
                    }
                }).modal('show');
            }

            _show();
            return deferredObject.promise();
        }

        GeneralFunctions.init();
      </script>

      <!-- Github buttons -->
      <script async defer src="https://buttons.github.io/buttons.js"></script>
      @yield('additional-scripts')
</body>
</html>
