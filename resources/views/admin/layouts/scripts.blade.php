@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // Laravel Reverb Configuration
    const REVERB_APP_KEY = '{{ config('broadcasting.connections.reverb.key') }}';
    const REVERB_HOST = '{{ config('broadcasting.connections.reverb.host') }}';
    const REVERB_PORT = {{ config('broadcasting.connections.reverb.port') }};
    const REVERB_SCHEME = '{{ config('broadcasting.connections.reverb.scheme') }}';
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
    }, { once: true });

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
@endpush
