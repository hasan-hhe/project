<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Reservation Backend API",
    description: "API documentation for the Apartment Reservation System",
    contact: new OA\Contact(
        email: "support@reservation.com"
    )
)]
#[OA\Server(
    url: "/api",
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Tag(
    name: "Authentication",
    description: "Authentication endpoints"
)]
#[OA\Tag(
    name: "Profile",
    description: "User profile management"
)]
#[OA\Tag(
    name: "Apartments",
    description: "Apartment listing and details"
)]
#[OA\Tag(
    name: "Reservations",
    description: "Booking and reservation management"
)]
#[OA\Tag(
    name: "Conversations",
    description: "Messaging and chat functionality"
)]
#[OA\Tag(
    name: "Notifications",
    description: "User notifications"
)]
#[OA\Tag(
    name: "Locations",
    description: "Governorates and cities"
)]
#[OA\Tag(
    name: "Owner",
    description: "Apartment owner management endpoints"
)]
class Controller
{
    //
}
