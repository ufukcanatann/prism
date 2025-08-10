<?php

/**
 * CSRF Protection Example
 * 
 * This file shows how to implement CSRF protection in your application.
 */

// Example route with CSRF protection
$router->group(['middleware' => 'csrf'], function() use ($router) {
    
    // Form routes that need CSRF protection
    $router->post('/contact', 'ContactController@store');
    $router->post('/users', 'UserController@store');
    $router->put('/users/{id}', 'UserController@update');
    $router->delete('/users/{id}', 'UserController@destroy');
    
});

// Example form in Blade template:
/*
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    {{ csrf_meta() }}
</head>
<body>
    <form method="POST" action="/contact">
        {{ csrf_field() }}
        
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="message">Message:</label>
        <textarea id="message" name="message" required></textarea>
        
        <button type="submit">Send Message</button>
    </form>
</body>
</html>
*/

// Example AJAX with CSRF token:
/*
<script>
// Get CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Include in AJAX requests
fetch('/api/data', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify(data)
});
</script>
*/
