@push('scripts')
    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <!-- Datatables -->

    <script src="{{ asset('assets/js/plugin/datatables/datatables.rtl.min.js') }}"></script>
    <!-- Sweet Alert -->
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.rtl.min.js') }}"></script>
    <!-- Kaiadmin JS -->
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>

    <!-- jQuery Sparkline -->
    <script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>

    <!-- Chart Circle -->
    <script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- jQuery Vector Maps -->
    <script src="{{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jsvectormap/world.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    {{-- Select2 --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        // Laravel Reverb Configuration
        const REVERB_APP_KEY = '{{ config('broadcasting.connections.reverb.key') }}';
        const REVERB_HOST = '{{ config('broadcasting.connections.reverb.options.host') }}';
        const REVERB_PORT = {{ config('broadcasting.connections.reverb.options.port') }};
        const REVERB_SCHEME = '{{ config('broadcasting.connections.reverb.options.scheme') }}';
        const REVERB_CLUSTER = '{{ config('broadcasting.connections.reverb.options.cluster') }}';
        const REVERB_USE_TLS = REVERB_SCHEME === 'https';
        const REVERB_WS_PORT = REVERB_USE_TLS ? 443 : REVERB_PORT;
        const REVERB_WS_HOST = `${REVERB_SCHEME}://${REVERB_HOST}`;
        const REVERB_WS_PATH = '/app/' + REVERB_APP_KEY;
        const REVERB_WS_URL = `${REVERB_WS_HOST}:${REVERB_WS_PORT}${REVERB_WS_PATH}`;

        // Initialize Pusher (Laravel Reverb uses Pusher protocol)
        const pusher = new Pusher(REVERB_APP_KEY, {
            wsHost: REVERB_HOST,
            wsPort: REVERB_PORT,
            wssPort: REVERB_WS_PORT,
            forceTLS: REVERB_USE_TLS,
            enabledTransports: ['ws', 'wss'],
            cluster: REVERB_CLUSTER,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            }
        });

        // Get current user ID
        const currentUserId = {{ Auth::id() ?? 'null' }};

        // Notification sound
        let notificationSound;
        document.addEventListener('click', () => {
            if (!notificationSound) {
                notificationSound = new Audio("{{ asset('assets/sounds/notification.mp3') }}");
                notificationSound.load();
            }
        }, {
            once: true
        });

        // Subscribe to private channel for user-specific notifications
        if (currentUserId) {
            const privateChannel = pusher.subscribe('private-user.' + currentUserId);

            privateChannel.bind('notification.sent', function(data) {
                console.log('Notification received:', data);
                showNotification(data);
                updateNotificationCount();

                if (notificationSound) {
                    notificationSound.play().catch(error => console.error("Error playing sound:", error));
                }
            });
        }

        // Subscribe to public notifications channel
        const notificationsChannel = pusher.subscribe('notifications');

        notificationsChannel.bind('notification.sent', function(data) {
            // Only show if it's for current user
            if (data.user_id == currentUserId) {
                console.log('Notification received:', data);
                showNotification(data);
                updateNotificationCount();

                if (notificationSound) {
                    notificationSound.play().catch(error => console.error("Error playing sound:", error));
                }
            }
        });

        // Function to show notification
        function showNotification(notification) {
            const notificationView = `
            <a href="{{ url('admin/notifications') }}/${notification.id}/edit">
                <div class="notif-icon notif-primary">
                    <i class="fa fa-bell" style="padding: 15px;"></i>
                </div>
                <div class="notif-content">
                    <span class="block">${notification.title}</span>
                    <span class="block">${notification.body}</span>
                    <span class="time">${notification.created_at}</span>
                </div>
            </a>
        `;

            // Add to notifications list if exists
            if ($('#notifications').length) {
                $('#notifications').prepend(notificationView);
            }

            // Update notification count
            const currentCount = parseInt($('.notification').text()) || 0;
            $('.notification').text(currentCount + 1);

            // Show toast notification
            if (typeof notifyMessage !== 'undefined') {
                notifyMessage("success", notification.body, notification.title);
            } else {
                // Fallback notification
                alert(notification.title + ': ' + notification.body);
            }

            // Mark as seen after a delay
            setTimeout(() => {
                markNotificationAsSeen(notification.id);
            }, 2000);
        }

        // Function to update notification count
        function updateNotificationCount() {
            fetch('{{ route('admin.notifications.unread-count') }}')
                .then(response => response.json())
                .then(data => {
                    if ($('.notification').length) {
                        $('.notification').text(data.count || 0);
                    }
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }

        // Function to mark notification as seen
        function markNotificationAsSeen(notificationId) {
            fetch(`{{ url('admin/notifications') }}/${notificationId}/mark-seen`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationCount();
                    }
                })
                .catch(error => console.error('Error marking notification as seen:', error));
        }

        // Load initial notification count
        $(document).ready(function() {
            updateNotificationCount();

            // Refresh notification count every 30 seconds
            setInterval(updateNotificationCount, 30000);
        });

        // Handle connection events
        pusher.connection.bind('connected', () => {
            console.log('✅ Connected to Laravel Reverb!');
        });

        pusher.connection.bind('error', (err) => {
            console.error('❌ Connection error:', err);
        });

        pusher.connection.bind('disconnected', () => {
            console.log('⚠️ Disconnected from Laravel Reverb');
        });
    </script>

    <script>
        var placementFrom = "top";
        var placementAlign = "right";
        var state = "success";
        var style = "withicon";
        var content = {};

        function notifyMessage(status, message, title) {
            content.message = message;
            content.title = title;
            if (status == "success")
                content.icon = "fa fa-check";
            else
                content.icon = "fa fa-false";
            notification = $.notify(content, {
                type: state,
                placement: {
                    from: placementFrom,
                    align: placementAlign,
                },
                time: 2,
                delay: 0,
            });

            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    </script>

    <script>
        $(document).ready(function() {
            // Add Row
            $(".table-datatable").DataTable({
                pageLength: 10,
            });

            $('div')[$('div').length - 2].remove();
        });

        $(document).load(function() {
            $('div')[$('div').length - 2].remove();
        });
    </script>


    <script>
        function destroy(id) {
            swal("حذف البيانات!", "هل أنت متأكد من حذف البيانات ؟", {
                icon: "warning",
                buttons: {
                    confirm: {
                        className: "btn btn-warning deleteRow",
                    },
                },
            });

            $('.deleteRow').attr('onclick',
                `event.preventDefault();document.getElementById('delete-form-${id}').submit();`);
        }
    </script>

    @if (session('success'))
        <script>
            notifyMessage("success", "{{ session('success') }}", "إشعار جديد");
        </script>
    @endif

    @if (session('error'))
        <script>
            notifyMessage("error", "{{ session('error') }}", "إشعار جديد");
        </script>
    @endif

    <script>
        $(document).ready(function() {
            $('.selectAll').click(function() {
                for (let i = 0; i < $('.form-check-input , .checkbox').length; i++) {
                    $('.form-check-input , .checkbox')[i].checked = true;
                }
            });
            $('.disAll').click(function() {
                for (let i = 0; i < $('.form-check-input , .checkbox').length; i++) {
                    $('.form-check-input , .checkbox')[i].checked = false;
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#search").on("keyup", function() {
                var input, filter, ul, li, a, i, txtValue;
                input = document.getElementById("search");
                filter = input.value.toLowerCase();
                ul = document.getElementById("listSearch");
                li = ul.getElementsByTagName("li");
                for (i = 0; i < li.length; i++) {
                    a = li[i].getElementsByTagName("div")[0].lastElementChild;
                    txtValue = a.textContent || a.innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        li[i].style.display = "";
                    } else {
                        li[i].style.display = "none";
                    }
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ths = document.querySelectorAll('.table-datatable th');

            ths.forEach(th => {
                th.style.setProperty('padding-left', '30px', 'important');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('loader');
            const loaderNew = document.getElementById('loader-new');

            // روابط التنقل
            document.querySelectorAll('a').forEach(link => {
                const href = link.getAttribute('href');
                if (href && !href.startsWith('#') && !link.hasAttribute('target')) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        loader.classList.add('show');
                        loaderNew.classList.add('show');

                        setTimeout(() => {

                            window.location.href = href;

                            setTimeout(() => {
                                loader.classList.remove('show');
                                loader.classList.add('hide');

                                loaderNew.classList.remove('show');
                                loaderNew.classList.add('hide');
                            }, 100);
                        }, 1500);
                    });
                }
            });

            //     // إرسال الفورم
            //     document.querySelectorAll('form').forEach(form => {
            //         form.addEventListener('submit', function(e) {
            //             e.preventDefault();
            //             loader.classList.add('show');
            //             loaderNew.classList.add('show');

            //             setTimeout(() => {
            //                 form.submit();


            //                 setTimeout(() => {
            //                     loader.classList.remove('show');
            //                     loader.classList.add('hide');
            //                     loaderNew.classList.remove('show');
            //                     loaderNew.classList.add('hide');
            //                 }, 100);
            //             }, 1500);
            //         });
            //     });
        });
    </script>

    <script>
        function validateNumber(input) {
            input.value = input.value.replace(/[^0-9.]/g, '');
        }
    </script>

    @stack('scripts')
@endpush
