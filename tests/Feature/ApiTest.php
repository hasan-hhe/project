<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class ApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $owner;
    protected $apartment;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create([
            'account_type' => 'RENTER',
            'phone_number' => '1234567890',
            'password' => Hash::make('password123'),
        ]);

        $this->owner = User::factory()->create([
            'account_type' => 'OWNER',
            'phone_number' => '0987654321',
            'password' => Hash::make('password123'),
        ]);

        // Create test apartment
        $this->apartment = Apartment::factory()->create([
            'owner_id' => $this->owner->id,
        ]);

        // Get auth token
        $response = $this->postJson('/api/auth/login', [
            'phone_number' => '1234567890',
            'password' => 'password123',
        ]);

        $this->token = $response->json('data.token');
    }

    // ==================== AUTH APIs ====================

    public function test_register_api()
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone_number' => '9999999999',
            'password' => 'password123',
            'account_type' => 'RENTER',
            'identity_document_image' => $this->createFakeImage(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_login_api()
    {
        $response = $this->postJson('/api/auth/login', [
            'phone_number' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_logout_api()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/logout');

        $response->assertStatus(200);
    }

    // ==================== PROFILE APIs ====================

    public function test_get_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/my-profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone_number',
                    'account_type',
                ],
            ]);
    }

    public function test_update_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/update-profile-info', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone_number' => '1234567890',
            'password' => 'newpassword123',
            'identity_document_image' => $this->createFakeImage(),
        ]);

        $response->assertStatus(200);
    }

    // ==================== APARTMENT APIs ====================

    public function test_get_apartments()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/apartments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'body',
            ])
            ->assertJsonPath('message', 'success');
    }

    public function test_get_apartment_by_id()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/apartments/{$this->apartment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'price',
                ],
            ]);
    }

    public function test_toggle_favorite()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/apartments/{$this->apartment->id}/toggle-favorite");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'is_favorite',
                ],
            ]);
    }

    public function test_get_favorite_apartments()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/apartments/favorites');

        $response->assertStatus(200);
    }

    public function test_get_apartment_reviews()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/apartments/{$this->apartment->id}/reviews");

        $response->assertStatus(200);
    }

    public function test_add_review()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/apartments/{$this->apartment->id}/reviews", [
            'rating' => 5,
            'comment' => 'Great apartment!',
        ]);

        $response->assertStatus(200);
    }

    // ==================== BOOKING APIs ====================

    public function test_create_booking()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/reservations', [
            'apartment_id' => $this->apartment->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'start_date',
                    'end_date',
                    'total_price',
                ],
            ]);
    }

    public function test_get_my_reservations()
    {
        // Create a booking first
        Booking::factory()->create([
            'renter_id' => $this->user->id,
            'apartment_id' => $this->apartment->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/reservations/my-reservations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'start_date',
                            'end_date',
                        ],
                    ],
                ],
            ]);
    }

    public function test_get_reservation_by_id()
    {
        $booking = Booking::factory()->create([
            'renter_id' => $this->user->id,
            'apartment_id' => $this->apartment->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/reservations/{$booking->id}");

        $response->assertStatus(200);
    }

    public function test_update_reservation()
    {
        $booking = Booking::factory()->create([
            'renter_id' => $this->user->id,
            'apartment_id' => $this->apartment->id,
            'status' => 'PENDING',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/reservations/{$booking->id}/update", [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
    }

    public function test_cancel_reservation()
    {
        $booking = Booking::factory()->create([
            'renter_id' => $this->user->id,
            'apartment_id' => $this->apartment->id,
            'status' => 'PENDING',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/reservations/{$booking->id}/cancel", [
            'cancel_reason' => 'Changed my mind',
        ]);

        $response->assertStatus(200);
    }

    public function test_delete_reservation()
    {
        $booking = Booking::factory()->create([
            'renter_id' => $this->user->id,
            'apartment_id' => $this->apartment->id,
            'status' => 'PENDING',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/reservations/{$booking->id}/delete");

        $response->assertStatus(200);
    }

    // ==================== CONVERSATION APIs ====================

    public function test_get_conversations()
    {
        Conversation::factory()->create([
            'owner_id' => $this->owner->id,
            'renter_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/conversations');

        $response->assertStatus(200);
    }

    public function test_create_conversation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/conversations', [
            'owner_id' => $this->owner->id,
            'apartment_id' => $this->apartment->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_get_conversation_by_id()
    {
        $conversation = Conversation::factory()->create([
            'owner_id' => $this->owner->id,
            'renter_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/conversations/{$conversation->id}");

        $response->assertStatus(200);
    }

    public function test_get_messages()
    {
        $conversation = Conversation::factory()->create([
            'owner_id' => $this->owner->id,
            'renter_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/conversations/{$conversation->id}/messages");

        $response->assertStatus(200);
    }

    public function test_send_message()
    {
        $conversation = Conversation::factory()->create([
            'owner_id' => $this->owner->id,
            'renter_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/conversations/{$conversation->id}/messages", [
            'content' => 'Hello, is this apartment available?',
        ]);

        $response->assertStatus(200);
    }

    public function test_mark_messages_as_read()
    {
        $conversation = Conversation::factory()->create([
            'owner_id' => $this->owner->id,
            'renter_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/conversations/{$conversation->id}/mark-read");

        $response->assertStatus(200);
    }

    // ==================== NOTIFICATION APIs ====================

    public function test_get_notifications()
    {
        $notification = Notification::factory()->create();
        UserNotification::factory()->create([
            'user_id' => $this->user->id,
            'notification_id' => $notification->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/notifications');

        $response->assertStatus(200);
    }

    public function test_get_user_notifications()
    {
        $notification = Notification::factory()->create();
        UserNotification::factory()->create([
            'user_id' => $this->user->id,
            'notification_id' => $notification->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/notifications/user/list');

        $response->assertStatus(200);
    }

    public function test_get_unread_count()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/notifications/unread/count');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'unread_count',
                ],
            ]);
    }

    public function test_mark_notification_as_seen()
    {
        $notification = Notification::factory()->create();
        $userNotification = UserNotification::factory()->create([
            'user_id' => $this->user->id,
            'notification_id' => $notification->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/notifications/{$userNotification->id}/mark-seen");

        $response->assertStatus(200);
    }

    // ==================== LOCATION APIs ====================

    public function test_get_governorates()
    {
        $response = $this->getJson('/api/governorates');

        $response->assertStatus(200);
    }

    public function test_get_cities()
    {
        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
    }

    // Helper method to create fake image for testing
    protected function createFakeImage()
    {
        return \Illuminate\Http\UploadedFile::fake()->image('test.jpg');
    }
}

